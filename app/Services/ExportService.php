<?php

namespace App\Services;

use App\Enums\OpportunityStatus;
use App\Enums\ResponseStatus;
use App\Models\Opportunity;
use App\Models\User;
use App\Services\Export\AssegnazionePortale;
use App\Support\Format;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * Export dei risultati (capitolato §9).
 *
 * I filtri accettati sono: opportunity_id, status, delivery_date,
 * delivery_from/delivery_to, closes_from/closes_to, store_id.
 * L'export rispetta esattamente i filtri ricevuti e viene registrato nell'audit log.
 */
class ExportService
{
    public function __construct(private readonly AuditService $audit) {}

    private const CSV_HEADER = [
        'codice_articolo', 'plu', 'descrizione', 'codice_punto_vendita', 'punto_vendita',
        'colli', 'kg_per_collo', 'kg_totali', 'data_consegna', 'stato_risposta',
    ];

    /** @return Collection<int, Opportunity> */
    public function opportunities(array $filters): Collection
    {
        $query = Opportunity::query()
            ->with(['stores', 'responses.store', 'responses.lastActor'])
            ->orderBy('delivery_date')
            ->orderBy('reference');

        if (! empty($filters['opportunity_id'])) {
            $query->whereKey($filters['opportunity_id']);
        }

        if (! empty($filters['status'])) {
            $statuses = array_map(
                fn ($s) => $s instanceof OpportunityStatus ? $s->value : $s,
                (array) $filters['status'],
            );
            $query->whereIn('status', $statuses);
        }

        if (! empty($filters['delivery_date'])) {
            $query->whereDate('delivery_date', $filters['delivery_date']);
        }

        if (! empty($filters['delivery_from'])) {
            $query->whereDate('delivery_date', '>=', $filters['delivery_from']);
        }

        if (! empty($filters['delivery_to'])) {
            $query->whereDate('delivery_date', '<=', $filters['delivery_to']);
        }

        if (! empty($filters['closes_from'])) {
            $query->where('closes_at', '>=', $filters['closes_from']);
        }

        if (! empty($filters['closes_to'])) {
            $query->where('closes_at', '<=', $filters['closes_to']);
        }

        if (! empty($filters['store_id'])) {
            $query->forStore((int) $filters['store_id']);
        }

        return $query->get();
    }

    // --------------------------------------------------------------------- CSV

    /**
     * CSV normalizzato: una riga per articolo e punto vendita.
     * UTF-8 con BOM, separatore ';', date dd/mm/YYYY.
     *
     * @return array{filename: string, content: string}
     */
    public function csv(array $filters, ?User $user = null): array
    {
        $opportunities = $this->opportunities($filters);
        $delimiter = config('pescheria.export.csv_delimiter', ';');

        $handle = fopen('php://temp', 'r+');

        fputcsv($handle, self::CSV_HEADER, $delimiter, '"', '');

        foreach ($opportunities as $opportunity) {
            foreach ($opportunity->stores as $store) {
                $response = $opportunity->responses->firstWhere('store_id', $store->id);
                $status = $response?->status ?? ResponseStatus::NON_COMPILATA;

                fputcsv($handle, [
                    $opportunity->article_code,
                    $opportunity->plu,
                    $opportunity->description,
                    $store->code,
                    $store->name,
                    (int) ($response?->packages ?? 0),
                    $this->decimalForCsv($opportunity->kg_per_package, 3),
                    $this->decimalForCsv($response?->kg ?? 0, 3),
                    Format::date($opportunity->delivery_date),
                    $status->value,
                ], $delimiter, '"', '');
            }
        }

        rewind($handle);
        $content = stream_get_contents($handle);
        fclose($handle);

        if (config('pescheria.export.csv_bom', true)) {
            $content = "\xEF\xBB\xBF".$content;
        }

        $filename = $this->filename($opportunities, 'csv');

        $this->audit->log('export.csv', null, [
            'filtri' => $this->auditableFilters($filters),
            'righe' => max(0, substr_count($content, "\n") - 1),
            'file' => $filename,
        ], $user);

        return ['filename' => $filename, 'content' => $content];
    }

    // ------------------------------------------------------ portale fornitore

    /**
     * File nel tracciato richiesto dal portale del fornitore.
     *
     * @return array{filename: string, path: string, mancanti: array{punti_vendita: array, prodotti: array}, righe: int}
     */
    public function assegnazionePortale(array $filters, ?User $user = null): array
    {
        $opportunita = $this->opportunities($filters);
        $scrittore = new AssegnazionePortale;

        $path = tempnam(sys_get_temp_dir(), 'portale_').'.xlsx';
        $scrittore->scrivi($opportunita, $path);

        $righe = count($scrittore->righe($opportunita));
        $mancanti = $scrittore->codiciMancanti($opportunita);

        // Nome identico a quello fornito dal portale: alcuni caricamenti sono
        // sensibili anche al nome del file.
        $filename = 'Assegnazione per portale.xlsx';

        $this->audit->log('export.portale', null, [
            'filtri' => $this->auditableFilters($filters),
            'righe' => $righe,
            'codici_mancanti' => count($mancanti['punti_vendita']) + count($mancanti['prodotti']),
        ], $user);

        return compact('filename', 'path', 'mancanti', 'righe');
    }

    /** Anteprima dei codici mancanti, senza generare il file. */
    public function codiciPortaleMancanti(array $filters): array
    {
        return (new AssegnazionePortale)->codiciMancanti($this->opportunities($filters));
    }

    // -------------------------------------------------------------------- XLSX

    /**
     * XLSX a tre fogli: Matrice ordini, Dettaglio risposte, Mancanti e rifiuti.
     *
     * @return array{filename: string, path: string}
     */
    public function xlsx(array $filters, ?User $user = null): array
    {
        $opportunities = $this->opportunities($filters);
        $stores = $this->storesInvolved($opportunities);

        $spreadsheet = new Spreadsheet;
        $spreadsheet->getProperties()
            ->setCreator(config('app.name'))
            ->setTitle('Ordini pescheria');

        $this->buildMatrixSheet($spreadsheet, $opportunities, $stores);
        $this->buildDetailSheet($spreadsheet, $opportunities);
        $this->buildMissingSheet($spreadsheet, $opportunities);

        $spreadsheet->setActiveSheetIndex(0);

        $filename = $this->filename($opportunities, 'xlsx');
        $path = tempnam(sys_get_temp_dir(), 'export_').'.xlsx';

        (new Xlsx($spreadsheet))->save($path);
        $spreadsheet->disconnectWorksheets();

        $this->audit->log('export.xlsx', null, [
            'filtri' => $this->auditableFilters($filters),
            'opportunita' => $opportunities->count(),
            'punti_vendita' => $stores->count(),
            'file' => $filename,
        ], $user);

        return ['filename' => $filename, 'path' => $path];
    }

    /** @param Collection<int, Opportunity> $opportunities */
    private function buildMatrixSheet(Spreadsheet $spreadsheet, Collection $opportunities, Collection $stores): void
    {
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Matrice ordini');

        $header = ['Codice articolo', 'PLU', 'Descrizione', 'Kg per collo', 'Data consegna'];
        foreach ($stores as $store) {
            $header[] = $store->code;
        }
        $header[] = 'Totale colli';
        $header[] = 'Totale kg';

        $sheet->fromArray($header, null, 'A1');
        $row = 2;

        foreach ($opportunities as $opportunity) {
            $line = [
                $opportunity->article_code,
                $opportunity->plu,
                $opportunity->description,
                (float) $opportunity->kg_per_package,
                Format::date($opportunity->delivery_date),
            ];

            $totalPackages = 0;

            foreach ($stores as $store) {
                $response = $opportunity->responses->firstWhere('store_id', $store->id);
                $isRecipient = $opportunity->stores->contains('id', $store->id);

                if (! $isRecipient) {
                    $line[] = '';     // punto vendita non destinatario: cella vuota, non zero

                    continue;
                }

                $packages = $response && $response->status === ResponseStatus::INVIATA_ACQUISTO
                    ? (int) $response->packages
                    : 0;

                $line[] = $packages;
                $totalPackages += $packages;
            }

            $line[] = $totalPackages;
            $line[] = round($totalPackages * (float) $opportunity->kg_per_package, 3);

            $sheet->fromArray($line, null, 'A'.$row);
            $row++;
        }

        $this->styleHeader($sheet, count($header));
        $this->autosize($sheet, count($header));
        $sheet->freezePane('D2');
    }

    private function buildDetailSheet(Spreadsheet $spreadsheet, Collection $opportunities): void
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Dettaglio risposte');

        $header = [
            'Opportunità', 'Codice articolo', 'PLU', 'Descrizione',
            'Codice punto vendita', 'Punto vendita', 'Stato risposta',
            'Colli ordinati', 'Kg equivalenti', 'Data/ora invio', 'Utente', 'Data consegna',
        ];
        $sheet->fromArray($header, null, 'A1');
        $row = 2;

        foreach ($opportunities as $opportunity) {
            foreach ($opportunity->stores as $store) {
                $response = $opportunity->responses->firstWhere('store_id', $store->id);
                $status = $response?->status ?? ResponseStatus::NON_COMPILATA;

                $sheet->fromArray([
                    $opportunity->reference,
                    $opportunity->article_code,
                    $opportunity->plu,
                    $opportunity->description,
                    $store->code,
                    $store->name,
                    $status->label(),
                    (int) ($response?->packages ?? 0),
                    (float) ($response?->kg ?? 0),
                    $response?->submitted_at ? Format::dateTime($response->submitted_at) : '',
                    $response?->lastActor?->full_name ?? '',
                    Format::date($opportunity->delivery_date),
                ], null, 'A'.$row);
                $row++;
            }
        }

        $this->styleHeader($sheet, count($header));
        $this->autosize($sheet, count($header));
        $sheet->freezePane('A2');
    }

    private function buildMissingSheet(Spreadsheet $spreadsheet, Collection $opportunities): void
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Mancanti e rifiuti');

        $header = [
            'Opportunità', 'Codice articolo', 'Descrizione', 'Codice punto vendita',
            'Punto vendita', 'Stato', 'Motivazione', 'Scadenza',
        ];
        $sheet->fromArray($header, null, 'A1');
        $row = 2;

        foreach ($opportunities as $opportunity) {
            foreach ($opportunity->stores as $store) {
                $response = $opportunity->responses->firstWhere('store_id', $store->id);
                $status = $response?->status ?? ResponseStatus::NON_COMPILATA;

                $isMissing = ! $status->isSubmitted();
                $isRefusal = $status === ResponseStatus::INVIATA_RIFIUTO;

                if (! $isMissing && ! $isRefusal) {
                    continue;
                }

                $sheet->fromArray([
                    $opportunity->reference,
                    $opportunity->article_code,
                    $opportunity->description,
                    $store->code,
                    $store->name,
                    $isRefusal ? 'Non acquista' : $status->label(),
                    $response?->refusal_reason ?? '',
                    Format::dateTime($opportunity->closes_at),
                ], null, 'A'.$row);
                $row++;
            }
        }

        $this->styleHeader($sheet, count($header));
        $this->autosize($sheet, count($header));
        $sheet->freezePane('A2');
    }

    /** @param Collection<int, Opportunity> $opportunities */
    private function storesInvolved(Collection $opportunities): Collection
    {
        return $opportunities
            ->flatMap(fn (Opportunity $o) => $o->stores)
            ->unique('id')
            ->sortBy('code')
            ->values();
    }

    private function styleHeader($sheet, int $columns): void
    {
        $lastColumn = $sheet->getCell([$columns, 1])->getColumn();
        $range = "A1:{$lastColumn}1";

        $sheet->getStyle($range)->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
        $sheet->getStyle($range)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF0B3A53');
        $sheet->getStyle($range)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle($range)->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getRowDimension(1)->setRowHeight(22);
    }

    private function autosize($sheet, int $columns): void
    {
        for ($i = 1; $i <= $columns; $i++) {
            $sheet->getColumnDimension($sheet->getCell([$i, 1])->getColumn())->setAutoSize(true);
        }
    }

    /** Il nome file include data di consegna e timestamp di generazione. */
    private function filename(Collection $opportunities, string $extension): string
    {
        $deliveryDates = $opportunities->pluck('delivery_date')->filter()->unique();

        $delivery = $deliveryDates->count() === 1
            ? $deliveryDates->first()->format('Ymd')
            : 'multi';

        $timestamp = now(config('app.display_timezone'))->format('Ymd-His');

        return "ordini-pescheria_consegna-{$delivery}_{$timestamp}.{$extension}";
    }

    private function decimalForCsv(float|string|null $value, int $decimals): string
    {
        // Excel italiano si aspetta la virgola come separatore decimale.
        return number_format((float) $value, $decimals, ',', '');
    }

    private function auditableFilters(array $filters): array
    {
        return collect($filters)
            ->filter(fn ($v) => $v !== null && $v !== '' && $v !== [])
            ->map(fn ($v) => is_array($v) ? implode(',', array_map('strval', $v)) : (string) $v)
            ->all();
    }
}
