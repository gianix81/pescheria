<?php

namespace App\Livewire\Tecnico\Anagrafiche;

use App\Models\Product;
use App\Services\AuditService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
#[Title('Prodotti')]
class Prodotti extends Component
{
    use WithPagination;

    public ?int $modificaId = null;

    public string $ricerca = '';

    public array $form = [
        'article_code' => '', 'portal_code' => '', 'plu' => '', 'description' => '', 'long_description' => '',
        'category' => '', 'origin' => '', 'fao_zone' => '', 'production_method' => '',
        'caliber' => '', 'unit_of_measure' => 'KG', 'vat_rate' => 10,
        'default_kg_per_package' => null, 'is_active' => true,
    ];

    public function mount(): void
    {
        $this->authorize('gestire-anagrafiche');
    }

    public function modifica(int $id): void
    {
        $this->modificaId = $id;
        $this->form = Product::findOrFail($id)->only(array_keys($this->form));
    }

    public function nuovo(): void
    {
        $this->modificaId = null;
        $this->form = [
            'article_code' => '', 'portal_code' => '', 'plu' => '', 'description' => '', 'long_description' => '',
            'category' => '', 'origin' => '', 'fao_zone' => '', 'production_method' => '',
            'caliber' => '', 'unit_of_measure' => 'KG', 'vat_rate' => 10,
            'default_kg_per_package' => null, 'is_active' => true,
        ];
    }

    public function salva(): void
    {
        $this->authorize('gestire-anagrafiche');

        $dati = $this->validate([
            'form.article_code' => ['required', 'string', 'max:40', 'unique:products,article_code'.($this->modificaId ? ','.$this->modificaId : '')],
            'form.portal_code' => ['nullable', 'string', 'max:20'],
            'form.plu' => ['nullable', 'string', 'max:20'],
            'form.description' => ['required', 'string', 'max:190'],
            'form.long_description' => ['nullable', 'string', 'max:2000'],
            'form.category' => ['nullable', 'string', 'max:80'],
            'form.origin' => ['nullable', 'string', 'max:120'],
            'form.fao_zone' => ['nullable', 'string', 'max:40'],
            'form.production_method' => ['nullable', 'string', 'max:80'],
            'form.caliber' => ['nullable', 'string', 'max:60'],
            'form.unit_of_measure' => ['required', 'string', 'max:10'],
            'form.vat_rate' => ['required', 'numeric', 'min:0', 'max:99'],
            'form.default_kg_per_package' => ['nullable', 'numeric', 'min:0.001'],
            'form.is_active' => ['boolean'],
        ])['form'];

        $prodotto = $this->modificaId ? Product::findOrFail($this->modificaId) : new Product;
        $prodotto->fill($dati)->save();

        app(AuditService::class)->log(
            $this->modificaId ? 'product.updated' : 'product.created', $prodotto, ['codice' => $prodotto->article_code],
        );

        $this->nuovo();
        $this->dispatch('toast', messaggio: 'Prodotto salvato.');
    }

    public function render()
    {
        return view('livewire.tecnico.anagrafiche.prodotti', [
            'elenco' => Product::search($this->ricerca)->orderBy('article_code')->paginate(15),
        ]);
    }
}
