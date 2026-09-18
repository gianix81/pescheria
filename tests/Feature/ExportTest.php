<?php

namespace Tests\Feature;

use App\Models\Opportunity;
use App\Services\ExportService;
use App\Services\ResponseSubmissionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\CreatesScenario;
use Tests\TestCase;

class ExportTest extends TestCase
{
    use CreatesScenario, RefreshDatabase;

    /** @return array{0: Opportunity, 1: array} */
    private function scenario(): array
    {
        [$storeA, $crA] = $this->storeWithCr('PV001');
        [$storeB, $crB] = $this->storeWithCr('PV002');
        [$storeC, $crC] = $this->storeWithCr('PV003');

        $opportunita = $this->openOpportunity([$storeA, $storeB, $storeC], [
            'article_code' => 'ART99001',
            'plu' => '9901',
            'description' => 'Orata fresca è così',       // accenti: verifica UTF-8
            'kg_per_package' => 5.0,
            'delivery_date' => now()->addDays(3)->toDateString(),
        ]);

        $service = app(ResponseSubmissionService::class);
        $service->submitPurchase($opportunita, $storeA, $crA, 4);
        $service->submitRefusal($opportunita->fresh(), $storeB, $crB, 'Assortimento coperto');
        // Il terzo punto vendita non risponde: deve risultare NON_COMPILATA, non zero.

        return [$opportunita->fresh(), compact('storeA', 'storeB', 'storeC')];
    }

    #[Test]
    public function il_csv_normalizzato_ha_intestazioni_bom_e_accenti(): void
    {
        [$opportunita] = $this->scenario();

        $risultato = app(ExportService::class)->csv(['opportunity_id' => $opportunita->id], $this->buyer());

        $this->assertStringStartsWith("\xEF\xBB\xBF", $risultato['content']);
        $this->assertStringContainsString(
            'codice_articolo;plu;descrizione;codice_punto_vendita;punto_vendita;colli;kg_per_collo;kg_totali;data_consegna;stato_risposta',
            $risultato['content'],
        );

        $this->assertStringContainsString('Orata fresca è così', $risultato['content']);
        $this->assertStringContainsString('ART99001;9901', $risultato['content']);
        $this->assertStringContainsString('PV001', $risultato['content']);
        $this->assertStringContainsString('INVIATA_ACQUISTO', $risultato['content']);
        $this->assertStringContainsString('INVIATA_RIFIUTO', $risultato['content']);
        $this->assertStringContainsString('NON_COMPILATA', $risultato['content']);
        $this->assertStringContainsString($opportunita->delivery_date->format('d/m/Y'), $risultato['content']);
        $this->assertStringContainsString('20,000', $risultato['content']);     // 4 colli × 5 kg

        $this->assertStringContainsString('consegna-'.$opportunita->delivery_date->format('Ymd'), $risultato['filename']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'export.csv']);
    }

    #[Test]
    public function lxlsx_contiene_i_tre_fogli_richiesti(): void
    {
        [$opportunita, $stores] = $this->scenario();

        $risultato = app(ExportService::class)->xlsx(['opportunity_id' => $opportunita->id], $this->tecnico());
        $foglio = IOFactory::load($risultato['path']);

        $this->assertSame(
            ['Matrice ordini', 'Dettaglio risposte', 'Mancanti e rifiuti'],
            $foglio->getSheetNames(),
        );

        // --- Foglio 1: matrice con una colonna per punto vendita e i totali
        $matrice = $foglio->getSheetByName('Matrice ordini');
        $intestazioni = $matrice->rangeToArray('A1:I1')[0];

        $this->assertSame('Codice articolo', $intestazioni[0]);
        $this->assertSame('PLU', $intestazioni[1]);
        $this->assertSame('Kg per collo', $intestazioni[3]);
        $this->assertSame('Data consegna', $intestazioni[4]);
        $this->assertSame('PV001', $intestazioni[5]);
        $this->assertSame('PV003', $intestazioni[7]);
        $this->assertSame('Totale colli', $intestazioni[8]);

        $riga = $matrice->rangeToArray('A2:J2')[0];
        $this->assertSame('ART99001', $riga[0]);
        $this->assertSame(4, (int) $riga[5]);          // PV001: 4 colli
        $this->assertSame(0, (int) $riga[6]);          // PV002: rifiuto = 0
        $this->assertSame(4, (int) $riga[8]);          // totale colli
        $this->assertEqualsWithDelta(20.0, (float) $riga[9], 0.001);

        // --- Foglio 2: una riga per opportunità e punto vendita
        $dettaglio = $foglio->getSheetByName('Dettaglio risposte');
        $this->assertSame(4, $dettaglio->getHighestRow());      // intestazione + 3 PdV

        // --- Foglio 3: mancanti e rifiuti
        $mancanti = $foglio->getSheetByName('Mancanti e rifiuti');
        $valori = $mancanti->toArray();
        $this->assertCount(3, $valori);                          // intestazione + rifiuto + mancante
        $this->assertStringContainsString('Assortimento coperto', json_encode($valori, JSON_UNESCAPED_UNICODE));

        @unlink($risultato['path']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'export.xlsx']);
    }

    #[Test]
    public function lexport_rispetta_i_filtri(): void
    {
        [$opportunita] = $this->scenario();
        [$altroStore] = $this->storeWithCr('PV777');
        $altra = $this->openOpportunity([$altroStore], [
            'article_code' => 'ART00777',
            'delivery_date' => now()->addDays(10)->toDateString(),
        ]);

        $service = app(ExportService::class);

        $soloPrima = $service->csv(['opportunity_id' => $opportunita->id]);
        $this->assertStringNotContainsString('ART00777', $soloPrima['content']);

        $perData = $service->csv(['delivery_date' => $altra->delivery_date->toDateString()]);
        $this->assertStringContainsString('ART00777', $perData['content']);
        $this->assertStringNotContainsString('ART99001', $perData['content']);

        $perStato = $service->csv(['status' => ['CHIUSA']]);
        $this->assertStringNotContainsString('ART99001', $perStato['content']);
    }

    #[Test]
    public function le_rotte_di_export_sono_riservate_a_buyer_e_tecnico(): void
    {
        [$opportunita] = $this->scenario();
        [$store, $cr] = $this->storeWithCr('PV888');

        $this->actingAs($this->buyer())->get(route('export.csv', ['opportunity_id' => $opportunita->id]))->assertOk();
        $this->actingAs($this->tecnico())->get(route('export.xlsx', ['opportunity_id' => $opportunita->id]))->assertOk();
        $this->actingAs($cr)->get(route('export.csv'))->assertForbidden();
    }
}
