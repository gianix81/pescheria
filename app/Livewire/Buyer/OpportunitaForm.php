<?php

namespace App\Livewire\Buyer;

use App\Enums\AvailabilityType;
use App\Exceptions\DomainException;
use App\Models\Opportunity;
use App\Models\Product;
use App\Models\Store;
use App\Services\MediaService;
use App\Services\OpportunityWorkflowService;
use App\Support\PricingCalculator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Creazione e modifica opportunità.
 *
 * Due modalità che salvano esattamente gli stessi dati:
 *  - `rapida`   → una sola schermata, mobile-first, sostituisce il sondaggio WhatsApp;
 *  - `guidata`  → wizard in 4 passaggi con salvataggio automatico della bozza.
 */
#[Layout('components.layouts.app')]
class OpportunitaForm extends Component
{
    use WithFileUploads;

    public ?Opportunity $opportunity = null;

    public string $modalita = 'rapida';

    public int $passo = 1;

    // --- ricerca articolo ---------------------------------------------------
    public string $ricercaArticolo = '';

    public ?int $product_id = null;

    // --- dati opportunità ---------------------------------------------------
    public string $article_code = '';

    public string $plu = '';

    public string $description = '';

    public string $title = '';

    public string $commercial_description = '';

    public string $technical_notes = '';

    public string $logistics_notes = '';

    public string $category = '';

    public string $origin = '';

    public string $fao_zone = '';

    public string $production_method = '';

    public string $caliber = '';

    public $kg_per_package = null;

    public $purchase_price = null;

    public $sale_price_gross = null;

    public $vat_rate = 10;

    public int $min_lot = 1;

    public int $order_multiple = 1;

    public string $availability_type = 'APERTA';

    public $total_packages = null;

    public string $opens_at = '';

    public string $closes_at = '';

    public string $delivery_date = '';

    public bool $requires_refusal_reason = false;

    /** @var int[] */
    public array $store_ids = [];

    /** @var UploadedFile[] */
    public array $nuoviMedia = [];

    public bool $anteprimaAperta = false;

    public function mount(?Opportunity $opportunity = null): void
    {
        $this->authorize('create', Opportunity::class);

        $fuso = config('app.display_timezone');

        if ($opportunity) {
            $this->authorize('update', $opportunity);
            $this->opportunity = $opportunity->load(['media', 'stores']);
            $this->modalita = 'guidata';

            $campiTesto = [
                'article_code', 'plu', 'description', 'title', 'commercial_description',
                'technical_notes', 'logistics_notes', 'category', 'origin',
                'fao_zone', 'production_method', 'caliber',
            ];

            foreach ($campiTesto as $campo) {
                $this->{$campo} = (string) $opportunity->{$campo};
            }

            $this->fill($opportunity->only([
                'product_id', 'kg_per_package', 'purchase_price', 'sale_price_gross',
                'vat_rate', 'min_lot', 'order_multiple', 'total_packages', 'requires_refusal_reason',
            ]));

            $this->availability_type = $opportunity->availability_type->value;
            $this->opens_at = $opportunity->opens_at->setTimezone($fuso)->format('Y-m-d\TH:i');
            $this->closes_at = $opportunity->closes_at->setTimezone($fuso)->format('Y-m-d\TH:i');
            $this->delivery_date = $opportunity->delivery_date->format('Y-m-d');
            $this->store_ids = $opportunity->stores->pluck('id')->all();

            return;
        }

        // Valori iniziali pensati per la pubblicazione mattutina.
        $this->opens_at = now($fuso)->format('Y-m-d\TH:i');
        $this->closes_at = now($fuso)->addHours(6)->format('Y-m-d\TH:i');
        $this->delivery_date = now($fuso)->addDays(2)->format('Y-m-d');
        $this->store_ids = Store::active()->pluck('id')->all();
    }

    // ------------------------------------------------------------ anagrafica

    public function selezionaProdotto(int $productId): void
    {
        $prodotto = Product::findOrFail($productId);

        $this->product_id = $prodotto->id;
        $this->article_code = $prodotto->article_code;
        $this->plu = (string) $prodotto->plu;
        $this->description = $prodotto->description;
        $this->title = $this->title ?: $prodotto->description;
        $this->commercial_description = $this->commercial_description ?: (string) $prodotto->long_description;
        $this->category = (string) $prodotto->category;
        $this->origin = (string) $prodotto->origin;
        $this->fao_zone = (string) $prodotto->fao_zone;
        $this->production_method = (string) $prodotto->production_method;
        $this->caliber = (string) $prodotto->caliber;
        $this->vat_rate = (float) $prodotto->vat_rate;
        $this->kg_per_package = $this->kg_per_package ?: $prodotto->default_kg_per_package;
        $this->ricercaArticolo = '';
    }

    // ------------------------------------------------------------ calcoli live

    public function getPrezziProperty(): array
    {
        return PricingCalculator::all(
            (float) $this->purchase_price,
            (float) $this->sale_price_gross,
            (float) $this->vat_rate,
        );
    }

    // ------------------------------------------------------------ media

    public function caricaMedia(): void
    {
        $tipiAmmessi = implode(',', array_merge(
            config('pescheria.media.image_mimes'),
            config('pescheria.media.video_mimes'),
        ));

        // Il limite qui è quello massimo ammesso; MediaService applica poi
        // il limite specifico per tipo (immagini 10 MB, video 100 MB).
        $this->validate([
            'nuoviMedia' => ['array', 'max:'.config('pescheria.media.max_files', 10)],
            'nuoviMedia.*' => [
                'file',
                'mimetypes:'.$tipiAmmessi,
                'max:'.(config('pescheria.media.max_video_mb') * 1024),
            ],
        ]);

        $opportunita = $this->opportunity ?? $this->salvaBozza(silenzioso: true);

        if (! $opportunita) {
            return;
        }

        $service = app(MediaService::class);

        foreach ($this->nuoviMedia as $file) {
            try {
                $service->store($opportunita, $file, auth()->user());
            } catch (DomainException $e) {
                $this->addError('nuoviMedia', $e->getMessage());
            }
        }

        $this->nuoviMedia = [];
        $this->opportunity = $opportunita->fresh(['media', 'stores']);
    }

    public function eliminaMedia(int $mediaId): void
    {
        $media = $this->opportunity?->media()->findOrFail($mediaId);

        if ($media) {
            app(MediaService::class)->delete($media);
            $this->opportunity = $this->opportunity->fresh(['media', 'stores']);
        }
    }

    // ------------------------------------------------------------ salvataggio

    protected function regole(): array
    {
        return [
            'article_code' => ['required', 'string', 'max:40'],
            'plu' => ['nullable', 'string', 'max:20'],
            'description' => ['required', 'string', 'max:190'],
            'title' => ['required', 'string', 'max:160'],
            'commercial_description' => ['nullable', 'string', 'max:2000'],
            'technical_notes' => ['nullable', 'string', 'max:2000'],
            'logistics_notes' => ['nullable', 'string', 'max:2000'],
            'category' => ['nullable', 'string', 'max:80'],
            'origin' => ['nullable', 'string', 'max:120'],
            'fao_zone' => ['nullable', 'string', 'max:40'],
            'production_method' => ['nullable', 'string', 'max:80'],
            'caliber' => ['nullable', 'string', 'max:60'],
            'kg_per_package' => ['required', 'numeric', 'min:0.001', 'max:9999'],
            'purchase_price' => ['required', 'numeric', 'min:0', 'max:99999'],
            'sale_price_gross' => ['required', 'numeric', 'min:0', 'max:99999'],
            'vat_rate' => ['required', 'numeric', 'min:0', 'max:99'],
            'min_lot' => ['required', 'integer', 'min:1', 'max:9999'],
            'order_multiple' => ['required', 'integer', 'min:1', 'max:9999'],
            'availability_type' => ['required', 'in:APERTA,LIMITATA'],
            'total_packages' => ['nullable', 'integer', 'min:1', 'max:999999', 'required_if:availability_type,LIMITATA'],
            'opens_at' => ['required', 'date'],
            'closes_at' => ['required', 'date', 'after:opens_at'],
            'delivery_date' => ['required', 'date'],
            'store_ids' => ['required', 'array', 'min:1'],
            'store_ids.*' => ['integer', 'exists:stores,id'],
        ];
    }

    protected function messaggi(): array
    {
        return [
            'closes_at.after' => 'La scadenza deve essere successiva all\'apertura.',
            'total_packages.required_if' => 'Con disponibilità limitata devi indicare il totale dei colli.',
            'store_ids.required' => 'Seleziona almeno un punto vendita destinatario.',
            'kg_per_package.min' => 'I kg per collo devono essere maggiori di zero.',
        ];
    }

    protected function attributiValidazione(): array
    {
        return [
            'article_code' => 'codice articolo', 'description' => 'descrizione', 'title' => 'titolo',
            'kg_per_package' => 'kg per collo', 'purchase_price' => 'prezzo di acquisto',
            'sale_price_gross' => 'prezzo di vendita', 'vat_rate' => 'aliquota IVA',
            'opens_at' => 'apertura', 'closes_at' => 'scadenza', 'delivery_date' => 'data di consegna',
            'store_ids' => 'punti vendita',
        ];
    }

    private function dati(): array
    {
        $fuso = config('app.display_timezone');

        return [
            'product_id' => $this->product_id,
            'article_code' => $this->article_code,
            'plu' => $this->plu ?: null,
            'description' => $this->description,
            'title' => $this->title,
            'commercial_description' => $this->commercial_description ?: null,
            'technical_notes' => $this->technical_notes ?: null,
            'logistics_notes' => $this->logistics_notes ?: null,
            'category' => $this->category ?: null,
            'origin' => $this->origin ?: null,
            'fao_zone' => $this->fao_zone ?: null,
            'production_method' => $this->production_method ?: null,
            'caliber' => $this->caliber ?: null,
            'kg_per_package' => $this->kg_per_package,
            'purchase_price' => $this->purchase_price,
            'sale_price_gross' => $this->sale_price_gross,
            'vat_rate' => $this->vat_rate,
            'min_lot' => $this->min_lot,
            'order_multiple' => $this->order_multiple,
            'availability_type' => AvailabilityType::from($this->availability_type),
            'total_packages' => $this->availability_type === 'LIMITATA' ? $this->total_packages : null,
            'requires_refusal_reason' => $this->requires_refusal_reason,
            // Gli orari arrivano in ora italiana e vengono salvati in UTC.
            'opens_at' => Carbon::parse($this->opens_at, $fuso)->utc(),
            'closes_at' => Carbon::parse($this->closes_at, $fuso)->utc(),
            'delivery_date' => $this->delivery_date,
            'store_ids' => $this->store_ids,
        ];
    }

    /** L'opportunità è già stata vista dai punti vendita? */
    public function getPubblicataProperty(): bool
    {
        return $this->opportunity?->status->isPubblicata() ?? false;
    }

    /**
     * Salva le modifiche scegliendo il percorso giusto: bozza oppure
     * opportunità già pubblicata, che comporta notifica ai destinatari e
     * controlli di compatibilità con gli ordini già raccolti.
     */
    public function salvaBozza(bool $silenzioso = false): ?Opportunity
    {
        $this->validate($this->regole(), $this->messaggi(), $this->attributiValidazione());

        $service = app(OpportunityWorkflowService::class);

        try {
            if (! $this->opportunity) {
                $this->opportunity = $service->createDraft($this->dati(), auth()->user());
                $messaggio = 'Bozza creata ('.$this->opportunity->reference.').';
            } elseif ($this->pubblicata) {
                $this->authorize('updatePublished', $this->opportunity);

                $this->opportunity = $service->updatePublished($this->opportunity, $this->dati(), auth()->user());
                $messaggio = 'Modifiche salvate: i punti vendita destinatari sono stati avvisati.';
            } else {
                $this->opportunity = $service->updateDraft($this->opportunity, $this->dati(), auth()->user());
                $messaggio = 'Bozza salvata ('.$this->opportunity->reference.').';
            }
        } catch (DomainException $e) {
            $this->addError('salvataggio', $e->getMessage());

            return null;
        }

        if (! $silenzioso) {
            $this->dispatch('toast', messaggio: $messaggio);
        }

        return $this->opportunity;
    }

    public function inviaInVerifica(): void
    {
        $opportunita = $this->salvaBozza(silenzioso: true);

        if (! $opportunita) {
            return;
        }

        try {
            app(OpportunityWorkflowService::class)->submitForReview($opportunita, auth()->user());
        } catch (DomainException $e) {
            $this->addError('invio', $e->getMessage());

            return;
        }

        session()->flash('status', 'Opportunità '.$opportunita->reference.' inviata al Tecnico per la verifica.');

        $this->redirectRoute('opportunita.show', $opportunita, navigate: true);
    }

    public function vaiAlPasso(int $passo): void
    {
        $this->passo = max(1, min(4, $passo));
    }

    public function render()
    {
        $risultati = $this->ricercaArticolo !== ''
            ? Product::active()->search($this->ricercaArticolo)->limit(8)->get()
            : collect();

        return view('livewire.buyer.opportunita-form', [
            'risultatiRicerca' => $risultati,
            'puntiVendita' => Store::active()->orderBy('code')->get(),
            'prezzi' => $this->prezzi,
            'problemiPubblicazione' => $this->opportunity
                ? app(OpportunityWorkflowService::class)->publishIssues($this->opportunity)
                : [],
        ])->title($this->opportunity ? 'Modifica '.$this->opportunity->reference : 'Nuova opportunità');
    }
}
