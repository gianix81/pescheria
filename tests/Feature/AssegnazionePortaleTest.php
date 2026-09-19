<?php

namespace Tests\Feature;

use App\Models\Opportunity;
use App\Services\ExportService;
use App\Services\ResponseSubmissionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\CreatesScenario;
use Tests\TestCase;

/**
 * Il tracciato è fissato dal portale del fornitore: va riprodotto identico.
 * Questi test confrontano il file generato con quello reale fornito dal
 * committente, conservato in tests/Fixtures.
 */
class AssegnazionePortaleTest extends TestCase
{
    use CreatesScenario, RefreshDatabase;

    private const RIFERIMENTO = __DIR__.'/../Fixtures/assegnazione-per-portale-riferimento.xlsx';

    /** Ricostruisce esattamente la riga presente nel file di riferimento. */
    private function scenarioDelRiferimento(): Opportunity
    {
        [$store, $cr] = $this->storeWithCr('PV001');
        $store->forceFill(['portal_code' => '566518'])->save();

        $opportunita = $this->openOpportunity([$store], [
            'portal_product_code' => '497109',
            'delivery_date' => '2026-09-15',
        ]);

        app(ResponseSubmissionService::class)->submitPurchase($opportunita, $store, $cr, 1);

        return $opportunita->fresh();
    }

    private function generato(Opportunity $opportunita): Spreadsheet
    {
        $risultato = app(ExportService::class)->assegnazionePortale(['opportunity_id' => $opportunita->id]);

        $foglio = IOFactory::load($risultato['path']);
        @unlink($risultato['path']);

        return $foglio;
    }

    #[Test]
    public function il_file_generato_coincide_con_quello_del_portale(): void
    {
        $opportunita = $this->scenarioDelRiferimento();

        $atteso = IOFactory::load(self::RIFERIMENTO)->getSheetByName('DATI');
        $ottenuto = $this->generato($opportunita)->getSheetByName('DATI');

        $this->assertNotNull($ottenuto, 'Il foglio deve chiamarsi DATI.');

        // Intestazioni e prima riga, cella per cella: valore, tipo e formato.
        foreach (['A1', 'B1', 'C1', 'D1', 'A2', 'B2', 'C2', 'D2'] as $cella) {
            $this->assertSame(
                $atteso->getCell($cella)->getValue(),
                $ottenuto->getCell($cella)->getValue(),
                "Valore diverso in {$cella}",
            );

            $this->assertSame(
                $atteso->getCell($cella)->getDataType(),
                $ottenuto->getCell($cella)->getDataType(),
                "Tipo di cella diverso in {$cella}",
            );

            $this->assertSame(
                $atteso->getCell($cella)->getStyle()->getNumberFormat()->getFormatCode(),
                $ottenuto->getCell($cella)->getStyle()->getNumberFormat()->getFormatCode(),
                "Formato diverso in {$cella}",
            );
        }
    }

    #[Test]
    public function le_intestazioni_sono_nellordine_indicato(): void
    {
        $opportunita = $this->scenarioDelRiferimento();
        $foglio = $this->generato($opportunita)->getSheetByName('DATI');

        $this->assertSame(
            ['DATA CONSEGNA', 'CLIENTE', 'PRODOTTO', 'QUANTITA'],
            $foglio->rangeToArray('A1:D1')[0],
        );

        $this->assertTrue($foglio->getStyle('A1')->getFont()->getBold());
    }

    #[Test]
    public function la_data_e_testo_e_non_una_data_di_excel(): void
    {
        $opportunita = $this->scenarioDelRiferimento();
        $foglio = $this->generato($opportunita)->getSheetByName('DATI');

        // Una data vera verrebbe riscritta secondo le impostazioni locali di
        // chi apre il file: il portale invece la legge come è scritta.
        $this->assertSame('15/09/2026', $foglio->getCell('A2')->getValue());
        $this->assertSame('s', $foglio->getCell('A2')->getDataType());
        $this->assertSame('@', $foglio->getCell('A2')->getStyle()->getNumberFormat()->getFormatCode());
    }

    #[Test]
    public function i_codici_sono_numerici_come_nel_riferimento(): void
    {
        $opportunita = $this->scenarioDelRiferimento();
        $foglio = $this->generato($opportunita)->getSheetByName('DATI');

        $this->assertSame(566518, $foglio->getCell('B2')->getValue());
        $this->assertSame(497109, $foglio->getCell('C2')->getValue());
        $this->assertSame(1, $foglio->getCell('D2')->getValue());
    }

    #[Test]
    public function una_riga_per_ogni_punto_vendita_che_ha_ordinato(): void
    {
        [$a, $crA] = $this->storeWithCr('PV101');
        [$b, $crB] = $this->storeWithCr('PV102');
        [$c, $crC] = $this->storeWithCr('PV103');
        $a->forceFill(['portal_code' => '566518'])->save();
        $b->forceFill(['portal_code' => '566519'])->save();
        $c->forceFill(['portal_code' => '566520'])->save();

        $opportunita = $this->openOpportunity([$a, $b, $c], [
            'portal_product_code' => '497109',
            'delivery_date' => '2026-09-15',
        ]);

        $servizio = app(ResponseSubmissionService::class);
        $servizio->submitPurchase($opportunita, $a, $crA, 3);
        $servizio->submitRefusal($opportunita->fresh(), $b, $crB, 'Coperto');
        // Il terzo non risponde.

        $foglio = $this->generato($opportunita->fresh())->getSheetByName('DATI');

        // Solo l'acquisto confermato produce una riga: al portale si comunica
        // ciò che va consegnato.
        $this->assertSame(2, $foglio->getHighestDataRow());
        $this->assertSame(566518, $foglio->getCell('B2')->getValue());
        $this->assertSame(3, $foglio->getCell('D2')->getValue());
    }

    #[Test]
    public function i_codici_mancanti_vengono_segnalati(): void
    {
        [$store, $cr] = $this->storeWithCr('PV111');
        $store->forceFill(['portal_code' => null])->save();

        $opportunita = $this->openOpportunity([$store], [
            'portal_product_code' => null,
            'product_id' => null,
            'article_code' => 'ART99999',
            'description' => 'Senza codice portale',
        ]);

        app(ResponseSubmissionService::class)->submitPurchase($opportunita, $store, $cr, 2);

        $mancanti = app(ExportService::class)->codiciPortaleMancanti(['opportunity_id' => $opportunita->id]);

        $this->assertNotEmpty($mancanti['punti_vendita']);
        $this->assertNotEmpty($mancanti['prodotti']);
        $this->assertStringContainsString('PV111', $mancanti['punti_vendita'][0]);
        $this->assertStringContainsString('ART99999', $mancanti['prodotti'][0]);
    }

    #[Test]
    public function il_file_si_scarica_col_nome_del_portale(): void
    {
        $opportunita = $this->scenarioDelRiferimento();

        $risposta = $this->actingAs($this->buyer())
            ->get(route('export.portale', ['opportunity_id' => $opportunita->id]));

        $risposta->assertOk();
        $this->assertStringContainsString(
            'Assegnazione per portale.xlsx',
            $risposta->headers->get('content-disposition'),
        );
        $this->assertDatabaseHas('audit_logs', ['action' => 'export.portale']);
    }

    #[Test]
    public function la_pagina_export_distingue_il_file_del_portale_dai_report(): void
    {
        $opportunita = $this->scenarioDelRiferimento();

        $this->actingAs($this->buyer())
            ->get(route('export.index'))
            ->assertOk()
            // Il file da caricare è dichiarato tale, e i report interni non si
            // chiamano più come lui: è così che si prende il file sbagliato.
            ->assertSee('è questo il file da caricare')
            ->assertSee('Scarica il file per il portale (XLSX)')
            ->assertSee('Report interni')
            ->assertSee('Report XLSX (3 fogli)')
            ->assertDontSee('Scarica XLSX (3 fogli)');
    }

    #[Test]
    public function il_file_e_sempre_xlsx_anche_se_vuoto(): void
    {
        [$store, $cr] = $this->storeWithCr();
        $opportunita = $this->openOpportunity([$store]);     // nessun ordine

        $risultato = app(ExportService::class)->assegnazionePortale(['opportunity_id' => $opportunita->id]);

        $this->assertStringEndsWith('.xlsx', $risultato['filename']);
        $this->assertSame(0, $risultato['righe']);

        // Deve restare un XLSX valido e apribile, con le sole intestazioni.
        $foglio = IOFactory::load($risultato['path'])->getSheetByName('DATI');
        $this->assertSame(['DATA CONSEGNA', 'CLIENTE', 'PRODOTTO', 'QUANTITA'], $foglio->rangeToArray('A1:D1')[0]);
        @unlink($risultato['path']);
    }

    #[Test]
    public function un_capo_reparto_non_puo_scaricarlo(): void
    {
        [$store, $cr] = $this->storeWithCr();

        $this->actingAs($cr)->get(route('export.portale'))->assertForbidden();
    }
}
