<?php

namespace Tests\Feature;

use App\Enums\OpportunityStatus;
use App\Livewire\Buyer\Dashboard;
use App\Models\Opportunity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\CreatesScenario;
use Tests\TestCase;

/**
 * Lo stato di un'opportunità avanza grazie allo scheduler, che può essere fermo
 * o in ritardo. Tutto ciò che si vede a schermo deve reggere quel disallineamento
 * senza dire assurdità come «Scade tra Scaduta».
 */
class ScadenzaVisualizzataTest extends TestCase
{
    use CreatesScenario, RefreshDatabase;

    #[Test]
    public function con_il_termine_passato_si_legge_solo_scaduta(): void
    {
        [$store, $cr] = $this->storeWithCr();
        $opportunita = $this->openOpportunity([$store], ['closes_at' => now()->subMinutes(10)]);

        $risposta = $this->actingAs($this->buyer())
            ->get(route('opportunita.show', $opportunita))
            ->assertOk();

        $risposta->assertSee('Scaduta');
        $risposta->assertDontSee('Scade tra Scaduta');
    }

    #[Test]
    public function con_il_termine_futuro_si_legge_quanto_manca(): void
    {
        [$store, $cr] = $this->storeWithCr();
        $opportunita = $this->openOpportunity([$store], ['closes_at' => now()->addHours(3)]);

        $this->actingAs($this->buyer())
            ->get(route('opportunita.show', $opportunita))
            ->assertOk()
            ->assertSee('Scade tra')
            ->assertDontSee('Scade tra Scaduta');
    }

    #[Test]
    public function unopportunita_scaduta_ma_ancora_aperta_e_segnalata(): void
    {
        [$store, $cr] = $this->storeWithCr();
        $opportunita = $this->openOpportunity([$store], ['closes_at' => now()->subMinute()]);

        // Lo scheduler non l'ha ancora portata a SCADUTA.
        $this->assertSame(OpportunityStatus::APERTA, $opportunita->fresh()->status);

        $this->actingAs($this->tecnico())
            ->get(route('opportunita.show', $opportunita))
            ->assertOk()
            ->assertSee('In attesa di chiusura automatica');
    }

    #[Test]
    public function il_buyer_non_conta_fra_le_aperte_quelle_col_termine_passato(): void
    {
        $buyer = $this->buyer();
        [$store, $cr] = $this->storeWithCr();

        $this->openOpportunity([$store], ['created_by' => $buyer->id, 'closes_at' => now()->addHours(5)]);
        $this->openOpportunity([$store], ['created_by' => $buyer->id, 'closes_at' => now()->subHour()]);
        $this->openOpportunity([$store], ['created_by' => $buyer->id, 'closes_at' => now()->subDay()]);

        $componente = Livewire::actingAs($buyer)->test(Dashboard::class);

        $this->assertSame(1, $componente->viewData('aperteReali'));
        $this->assertSame(2, $componente->viewData('daChiudere'));

        $componente->assertSee('2 con termine passato');
    }

    #[Test]
    public function le_disponibilita_residue_mostrano_solo_le_aperte_davvero(): void
    {
        $buyer = $this->buyer();
        [$store, $cr] = $this->storeWithCr();

        $this->limitedOpportunity([$store], 10, [
            'created_by' => $buyer->id,
            'description' => 'Ancora ordinabile',
            'closes_at' => now()->addHours(4),
        ]);
        $this->limitedOpportunity([$store], 10, [
            'created_by' => $buyer->id,
            'description' => 'Termine passato',
            'closes_at' => now()->subHour(),
        ]);

        // Si guarda l'elenco delle disponibilità residue, non tutta la pagina:
        // l'opportunità scaduta compare comunque fra le ultime create.
        $limitate = Livewire::actingAs($buyer)
            ->test(Dashboard::class)
            ->viewData('limitate');

        $this->assertSame(['Ancora ordinabile'], $limitate->pluck('description')->all());
    }

    #[Test]
    public function lo_scope_ancora_aperta_richiede_stato_e_orologio(): void
    {
        [$store] = $this->storeWithCr();

        $valida = $this->openOpportunity([$store], ['closes_at' => now()->addHour()]);
        $scaduta = $this->openOpportunity([$store], ['closes_at' => now()->subHour()]);
        $chiusa = $this->openOpportunity([$store], [
            'status' => OpportunityStatus::CHIUSA,
            'closes_at' => now()->addHour(),
        ]);

        $ids = Opportunity::ancoraAperta()->pluck('id');

        $this->assertTrue($ids->contains($valida->id));
        $this->assertFalse($ids->contains($scaduta->id));
        $this->assertFalse($ids->contains($chiusa->id));
    }
}
