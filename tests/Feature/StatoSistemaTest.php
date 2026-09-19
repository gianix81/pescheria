<?php

namespace Tests\Feature;

use App\Models\Opportunity;
use App\Services\MediaService;
use App\Support\Sistema;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\CreatesScenario;
use Tests\TestCase;

/**
 * La pagina «Stato sistema» risponde senza aprire una shell alle domande che
 * tornano di continuo durante la messa in linea: è pubblicata l'ultima
 * versione? il database è allineato? i file sopravvivono alle pubblicazioni?
 * lo scheduler sta girando?
 */
class StatoSistemaTest extends TestCase
{
    use CreatesScenario, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake(config('pescheria.media.disk'));
    }

    #[Test]
    public function e_riservata_al_super_admin(): void
    {
        [$store, $cr] = $this->storeWithCr();

        $this->actingAs($this->admin())->get(route('tecnico.stato'))->assertOk();
        $this->actingAs($this->tecnico())->get(route('tecnico.stato'))->assertForbidden();
        $this->actingAs($this->buyer())->get(route('tecnico.stato'))->assertForbidden();
        $this->actingAs($cr)->get(route('tecnico.stato'))->assertForbidden();
    }

    #[Test]
    public function mostra_versione_database_e_conteggi(): void
    {
        [$store, $cr] = $this->storeWithCr();
        $this->openOpportunity([$store]);

        $this->actingAs($this->admin())
            ->get(route('tecnico.stato'))
            ->assertOk()
            ->assertSee('Versione in linea')
            ->assertSee('Collegato e allineato')
            ->assertSee('Punti vendita')
            ->assertSee('Opportunità');
    }

    #[Test]
    public function segnala_i_file_che_non_si_trovano_piu(): void
    {
        $buyer = $this->buyer();
        $opportunita = Opportunity::factory()->create(['created_by' => $buyer->id]);

        $media = app(MediaService::class)->store($opportunita, UploadedFile::fake()->image('foto.jpg'), $buyer);
        Storage::disk($media->disk)->delete($media->path);

        $this->actingAs($this->admin())
            ->get(route('tecnico.stato'))
            ->assertOk()
            ->assertSee('File non trovati')
            ->assertSee('i file caricati sono andati persi');
    }

    #[Test]
    public function riconosce_il_disco_persistente_dai_contrassegni(): void
    {
        // Contrassegno lasciato da un rilascio precedente.
        Storage::disk(config('pescheria.media.disk'))->put('_persistenza/altrorilascio.txt', 'x');

        $media = Sistema::media();

        $this->assertTrue($media['persistente']);
        $this->assertStringContainsString('rilasci precedenti', $media['descrizione']);

        $this->actingAs($this->admin())
            ->get(route('tecnico.stato'))
            ->assertOk()
            ->assertSee('Disco persistente');
    }

    #[Test]
    public function dice_se_lo_scheduler_non_ha_mai_girato(): void
    {
        $this->assertNull(Sistema::ultimaEsecuzioneScheduler());

        $this->actingAs($this->admin())
            ->get(route('tecnico.stato'))
            ->assertOk()
            ->assertSee('Lo scheduler non è mai stato eseguito');
    }

    #[Test]
    public function i_comandi_pianificati_lasciano_il_battito(): void
    {
        $this->artisan('opportunita:scadi')->assertSuccessful();

        $ultima = Sistema::ultimaEsecuzioneScheduler();

        $this->assertNotNull($ultima);
        $this->assertLessThan(5, $ultima->diffInSeconds(now()));

        $this->actingAs($this->admin())
            ->get(route('tecnico.stato'))
            ->assertOk()
            ->assertSee('Attivo');
    }

    #[Test]
    public function segnala_lo_scheduler_fermo_da_troppo(): void
    {
        Cache::forever(
            'sistema.scheduler.ultima_esecuzione',
            now()->subHours(3)->toIso8601String(),
        );

        $this->actingAs($this->admin())
            ->get(route('tecnico.stato'))
            ->assertOk()
            ->assertSee('Fermo da')
            ->assertSee('partono solleciti');
    }

    #[Test]
    public function un_disco_non_ancora_verificato_non_e_segnalato_come_errore(): void
    {
        // Nessun file mancante e nessun contrassegno precedente: è semplicemente
        // troppo presto per dirlo, e la pagina non deve allarmare.
        $media = Sistema::media();

        $this->assertNull($media['persistente']);
        $this->assertSame(0, $media['mancanti']);

        $this->actingAs($this->admin())
            ->get(route('tecnico.stato'))
            ->assertOk()
            ->assertSee('non ancora determinabile')
            ->assertSee('Dopo il prossimo rilascio')
            ->assertDontSee('Serve un volume sul servizio');
    }

    #[Test]
    public function i_negozi_non_si_confondono_con_gli_utenti_di_punto_vendita(): void
    {
        [$store, $cr] = $this->storeWithCr();

        // Il ruolo si chiama "Punto vendita" come i negozi: le due voci vanno
        // distinte, altrimenti si leggono due numeri uguali senza capirli.
        $this->actingAs($this->admin())
            ->get(route('tecnico.stato'))
            ->assertOk()
            ->assertSee('Utenti attivi')
            ->assertSee('Anagrafiche')
            ->assertSee('Negozi registrati');
    }

    #[Test]
    public function avvisa_quando_la_modalita_dimostrativa_e_attiva(): void
    {
        config(['pescheria.demo.enabled' => true]);

        $this->actingAs($this->admin())
            ->get(route('tecnico.stato'))
            ->assertOk()
            ->assertSee('i dati sono temporanei');
    }
}
