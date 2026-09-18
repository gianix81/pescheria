<?php

namespace Tests\Feature;

use App\Enums\ResponseStatus;
use App\Exceptions\InsufficientStockException;
use App\Services\AvailabilityService;
use App\Services\ResponseSubmissionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\CreatesScenario;
use Tests\TestCase;

class DisponibilitaLimitataTest extends TestCase
{
    use CreatesScenario, RefreshDatabase;

    private ResponseSubmissionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(ResponseSubmissionService::class);
    }

    #[Test]
    public function lo_stock_si_impegna_solo_allinvio_non_con_la_bozza(): void
    {
        [$store, $cr] = $this->storeWithCr();
        $opportunita = $this->limitedOpportunity([$store], 10);

        $this->service->saveDraft($opportunita, $store, $cr, 4);
        $this->assertSame(10, $opportunita->fresh()->remainingPackages());

        $this->service->submitPurchase($opportunita->fresh(), $store, $cr, 4);
        $this->assertSame(6, $opportunita->fresh()->remainingPackages());
    }

    #[Test]
    public function non_si_puo_superare_la_disponibilita(): void
    {
        [$storeA, $crA] = $this->storeWithCr();
        [$storeB, $crB] = $this->storeWithCr();
        $opportunita = $this->limitedOpportunity([$storeA, $storeB], 10);

        $this->service->submitPurchase($opportunita, $storeA, $crA, 7);

        try {
            $this->service->submitPurchase($opportunita->fresh(), $storeB, $crB, 5);
            $this->fail('La richiesta eccedente doveva essere respinta.');
        } catch (InsufficientStockException $e) {
            $this->assertSame(3, $e->remaining);
            $this->assertStringContainsString('restano 3 colli', $e->getMessage());
        }

        $this->assertSame(7, (int) $opportunita->fresh()->committed_packages);
    }

    #[Test]
    public function ridurre_la_quantita_libera_solo_la_differenza(): void
    {
        [$storeA, $crA] = $this->storeWithCr();
        [$storeB, $crB] = $this->storeWithCr();
        $opportunita = $this->limitedOpportunity([$storeA, $storeB], 10);

        $this->service->submitPurchase($opportunita, $storeA, $crA, 8);
        $this->service->submitPurchase($opportunita->fresh(), $storeA, $crA, 3);

        $this->assertSame(3, (int) $opportunita->fresh()->committed_packages);
        $this->assertSame(7, $opportunita->fresh()->remainingPackages());

        // Ora il secondo punto vendita può prendere ciò che si è liberato.
        $this->service->submitPurchase($opportunita->fresh(), $storeB, $crB, 7);
        $this->assertSame(0, $opportunita->fresh()->remainingPackages());
    }

    #[Test]
    public function il_rifiuto_libera_immediatamente_la_quantita(): void
    {
        [$store, $cr] = $this->storeWithCr();
        $opportunita = $this->limitedOpportunity([$store], 10);

        $this->service->submitPurchase($opportunita, $store, $cr, 9);
        $this->assertSame(1, $opportunita->fresh()->remainingPackages());

        $this->service->submitRefusal($opportunita->fresh(), $store, $cr, 'Cambio programma');

        $this->assertSame(10, $opportunita->fresh()->remainingPackages());
        $this->assertSame(0, (int) $opportunita->fresh()->committed_packages);
    }

    #[Test]
    public function quando_e_esaurito_resta_possibile_il_rifiuto_esplicito(): void
    {
        [$storeA, $crA] = $this->storeWithCr();
        [$storeB, $crB] = $this->storeWithCr();
        $opportunita = $this->limitedOpportunity([$storeA, $storeB], 5);

        $this->service->submitPurchase($opportunita, $storeA, $crA, 5);
        $esaurita = $opportunita->fresh();

        $this->assertTrue($esaurita->isSoldOut());

        try {
            $this->service->submitPurchase($esaurita, $storeB, $crB, 1);
            $this->fail('Acquisto consentito nonostante l\'esaurimento.');
        } catch (InsufficientStockException $e) {
            $this->assertStringContainsString('esaurita', $e->getMessage());
        }

        $rifiuto = $this->service->submitRefusal($esaurita, $storeB, $crB);
        $this->assertSame(ResponseStatus::INVIATA_RIFIUTO, $rifiuto->status);
    }

    #[Test]
    public function buyer_e_tecnico_vedono_iniziale_impegnato_e_residuo(): void
    {
        [$store, $cr] = $this->storeWithCr();
        $opportunita = $this->limitedOpportunity([$store], 20);

        $this->service->submitPurchase($opportunita, $store, $cr, 6);

        $this->actingAs($this->tecnico())
            ->get(route('opportunita.show', $opportunita))
            ->assertOk()
            ->assertSee('20 totali')
            ->assertSee('6 impegnati')
            ->assertSee('14 residui');
    }

    #[Test]
    public function il_ricalcolo_riallinea_il_contatore_alle_risposte(): void
    {
        [$storeA, $crA] = $this->storeWithCr();
        [$storeB, $crB] = $this->storeWithCr();
        $opportunita = $this->limitedOpportunity([$storeA, $storeB], 30);

        $this->service->submitPurchase($opportunita, $storeA, $crA, 4);
        $this->service->submitPurchase($opportunita->fresh(), $storeB, $crB, 6);

        // Simula un disallineamento e verifica il ricalcolo di servizio.
        $opportunita->fresh()->forceFill(['committed_packages' => 99])->save();

        $totale = app(AvailabilityService::class)->recomputeCommitted($opportunita->fresh());

        $this->assertSame(10, $totale);
        $this->assertSame(10, (int) $opportunita->fresh()->committed_packages);
    }
}
