<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\CreatesScenario;
use Tests\TestCase;

/**
 * La firma del progetto deve comparire in fondo a ogni pagina, comprese quelle
 * di accesso: è un credito, e se manca da qualche parte non se ne accorge
 * nessuno finché non serve.
 */
class FirmaProgettoTest extends TestCase
{
    use CreatesScenario, RefreshDatabase;

    private const NOMI = [
        'Antonio Rolletta',
        'Michele Fabbricino',
        'Vincenzo Salvemini',
        'De.Ca. Digital Solution',
        'Gennaro Carandente',
        'Giovanni De Rosa',
    ];

    private function assertFirmaPresente($risposta): void
    {
        $risposta->assertSee('Progetto pilota su idea dei tecnici');

        foreach (self::NOMI as $nome) {
            $risposta->assertSee($nome);
        }
    }

    #[Test]
    public function compare_sulle_pagine_di_accesso(): void
    {
        $this->assertFirmaPresente($this->get(route('login'))->assertOk());
        $this->assertFirmaPresente($this->get(route('password.request'))->assertOk());
    }

    #[Test]
    public function compare_sulle_pagine_del_capo_reparto(): void
    {
        [$store, $cr] = $this->storeWithCr();
        $opportunita = $this->openOpportunity([$store]);

        $this->assertFirmaPresente($this->actingAs($cr)->get(route('cr.opportunita.index'))->assertOk());
        $this->assertFirmaPresente($this->actingAs($cr)->get(route('cr.opportunita.show', $opportunita))->assertOk());
    }

    #[Test]
    #[DataProvider('paginePrincipali')]
    public function compare_sulle_pagine_di_buyer_tecnico_e_admin(string $rotta): void
    {
        [$store, $cr] = $this->storeWithCr();

        $this->assertFirmaPresente($this->actingAs($this->admin())->get(route($rotta))->assertOk());
    }

    public static function paginePrincipali(): array
    {
        return [
            'dashboard buyer' => ['buyer.dashboard'],
            'nuova opportunità' => ['buyer.opportunita.create'],
            'ordini' => ['buyer.ordini'],
            'elenco opportunità' => ['opportunita.index'],
            'export' => ['export.index'],
            'dashboard tecnico' => ['tecnico.dashboard'],
            'monitor' => ['tecnico.monitor'],
            'audit' => ['tecnico.audit'],
            'utenti' => ['tecnico.anagrafiche.utenti'],
            'punti vendita' => ['tecnico.anagrafiche.punti-vendita'],
            'prodotti' => ['tecnico.anagrafiche.prodotti'],
            'notifiche' => ['notifiche.index'],
        ];
    }

    #[Test]
    public function resta_discreta_ma_leggibile(): void
    {
        $contenuto = file_get_contents(resource_path('views/components/firma-progetto.blade.php'));

        // Piccola, come richiesto.
        $this->assertStringContainsString('text-[11px]', $contenuto);

        // Ma non sotto il contrasto minimo: slate-400 su bianco non basta.
        $this->assertStringContainsString('text-slate-500', $contenuto);
        $this->assertStringNotContainsString('text-slate-300', $contenuto);
        $this->assertStringNotContainsString('opacity-0', $contenuto);
    }
}
