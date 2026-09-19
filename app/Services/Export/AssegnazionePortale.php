<?php

namespace App\Services\Export;

use App\Enums\ResponseStatus;
use App\Models\Opportunity;
use App\Support\Format;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * File da caricare sul portale del fornitore.
 *
 * Il tracciato è fissato dal portale e va riprodotto identico: foglio «DATI»,
 * quattro colonne in quest'ordine, con questi tipi di cella.
 *
 *   DATA CONSEGNA | CLIENTE | PRODOTTO | QUANTITA
 *   15/09/2026    |  566518 |   497109 |        1
 *      testo      | numero  |  numero  |  numero
 *
 * La data è TESTO con formato «@», non una data di Excel: il portale la legge
 * come è scritta, e una data vera verrebbe riscritta secondo le impostazioni
 * locali di chi apre il file.
 *
 * CLIENTE e PRODOTTO sono i codici del portale, non quelli interni: si
 * prendono da `stores.portal_code` e dallo snapshot `portal_product_code`.
 */
final class AssegnazionePortale
{
    public const FOGLIO = 'DATI';

    public const INTESTAZIONI = ['DATA CONSEGNA', 'CLIENTE', 'PRODOTTO', 'QUANTITA'];

    /** Larghezze del file di riferimento, per non stravolgerne l'aspetto. */
    private const LARGHEZZE = ['A' => 16.5703125, 'B' => 8, 'C' => 10.85546875, 'D' => 10.28515625];

    /**
     * Righe da esportare: una per ogni acquisto confermato.
     * Rifiuti e mancate risposte non producono righe — al portale si comunica
     * ciò che va consegnato, non ciò che non è stato ordinato.
     *
     * @param  Collection<int, Opportunity>  $opportunita
     * @return array<int, array{data: string, cliente: ?string, prodotto: ?string, quantita: int}>
     */
    public function righe(Collection $opportunita): array
    {
        $righe = [];

        foreach ($opportunita as $o) {
            foreach ($o->responses as $risposta) {
                if ($risposta->status !== ResponseStatus::INVIATA_ACQUISTO || $risposta->packages < 1) {
                    continue;
                }

                $righe[] = [
                    'data' => Format::date($o->delivery_date),
                    'cliente' => $risposta->store?->portal_code,
                    'prodotto' => $o->portal_product_code ?: $o->product?->portal_code,
                    'quantita' => (int) $risposta->packages,
                ];
            }
        }

        return $righe;
    }

    /**
     * Codici mancanti: senza, il portale non può accettare la riga.
     *
     * @return array{punti_vendita: array<int, string>, prodotti: array<int, string>}
     */
    public function codiciMancanti(Collection $opportunita): array
    {
        $puntiVendita = [];
        $prodotti = [];

        foreach ($opportunita as $o) {
            $haOrdini = $o->responses->contains(
                fn ($r) => $r->status === ResponseStatus::INVIATA_ACQUISTO && $r->packages > 0
            );

            if ($haOrdini && blank($o->portal_product_code ?: $o->product?->portal_code)) {
                $prodotti[$o->article_code] = $o->article_code.' — '.$o->description;
            }

            foreach ($o->responses as $risposta) {
                if ($risposta->status !== ResponseStatus::INVIATA_ACQUISTO || $risposta->packages < 1) {
                    continue;
                }

                if (blank($risposta->store?->portal_code)) {
                    $puntiVendita[$risposta->store->code] = $risposta->store->code.' — '.$risposta->store->name;
                }
            }
        }

        return [
            'punti_vendita' => array_values($puntiVendita),
            'prodotti' => array_values($prodotti),
        ];
    }

    /** @param Collection<int, Opportunity> $opportunita */
    public function scrivi(Collection $opportunita, string $percorso): string
    {
        $documento = new Spreadsheet;
        $foglio = $documento->getActiveSheet();
        $foglio->setTitle(self::FOGLIO);

        foreach (self::INTESTAZIONI as $i => $intestazione) {
            $foglio->getCell([$i + 1, 1])->setValueExplicit($intestazione, DataType::TYPE_STRING);
        }

        $foglio->getStyle('A1:D1')->getFont()->setBold(true);

        $numeroRiga = 2;

        foreach ($this->righe($opportunita) as $dati) {
            // Data come testo, esattamente come nel file del portale.
            $foglio->getCell([1, $numeroRiga])->setValueExplicit($dati['data'], DataType::TYPE_STRING);
            $foglio->getStyle('A'.$numeroRiga)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);

            $this->codice($foglio, 'B'.$numeroRiga, $dati['cliente']);
            $this->codice($foglio, 'C'.$numeroRiga, $dati['prodotto']);

            $foglio->getCell([4, $numeroRiga])->setValueExplicit($dati['quantita'], DataType::TYPE_NUMERIC);

            $numeroRiga++;
        }

        foreach (self::LARGHEZZE as $colonna => $larghezza) {
            $foglio->getColumnDimension($colonna)->setWidth($larghezza);
        }

        (new Xlsx($documento))->save($percorso);
        $documento->disconnectWorksheets();

        return $percorso;
    }

    /**
     * I codici del portale sono numerici nel file di riferimento: restano tali
     * se lo sono davvero. Se contenessero zeri iniziali o lettere si scrivono
     * come testo, per non alterarli.
     */
    private function codice(Worksheet $foglio, string $cella, ?string $valore): void
    {
        $valore = (string) $valore;

        if ($valore === '') {
            return;         // cella vuota: il codice mancante è segnalato a parte
        }

        if (ctype_digit($valore) && ! str_starts_with($valore, '0')) {
            $foglio->getCell($cella)->setValueExplicit((int) $valore, DataType::TYPE_NUMERIC);

            return;
        }

        $foglio->getCell($cella)->setValueExplicit($valore, DataType::TYPE_STRING);
        $foglio->getStyle($cella)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);
    }
}
