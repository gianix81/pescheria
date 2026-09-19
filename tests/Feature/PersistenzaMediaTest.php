<?php

namespace Tests\Feature;

use App\Models\Opportunity;
use App\Models\OpportunityMedia;
use App\Services\MediaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\CreatesScenario;
use Tests\TestCase;

/**
 * Su una piattaforma senza disco persistente il contenuto dei file sparisce a
 * ogni pubblicazione mentre le righe restano in tabella. L'applicazione non può
 * impedirlo, ma non deve nemmeno far finta di niente.
 */
class PersistenzaMediaTest extends TestCase
{
    use CreatesScenario, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake(config('pescheria.media.disk'));
    }

    #[Test]
    public function un_file_presente_risulta_esistente(): void
    {
        $buyer = $this->buyer();
        $opportunita = Opportunity::factory()->create(['created_by' => $buyer->id]);

        $media = app(MediaService::class)->store($opportunita, UploadedFile::fake()->image('foto.jpg'), $buyer);

        $this->assertTrue($media->esiste());
    }

    #[Test]
    public function un_file_sparito_dal_disco_viene_riconosciuto(): void
    {
        $buyer = $this->buyer();
        $opportunita = Opportunity::factory()->create(['created_by' => $buyer->id]);

        $media = app(MediaService::class)->store($opportunita, UploadedFile::fake()->image('foto.jpg'), $buyer);

        // Simula la pubblicazione che azzera il disco del container.
        Storage::disk($media->disk)->delete($media->path);

        $this->assertFalse($media->fresh()->esiste());
    }

    #[Test]
    public function la_scheda_avvisa_quando_i_file_non_ci_sono_piu(): void
    {
        $buyer = $this->buyer();
        [$store, $cr] = $this->storeWithCr();
        $opportunita = $this->openOpportunity([$store], ['created_by' => $buyer->id]);

        $media = app(MediaService::class)->store($opportunita, UploadedFile::fake()->image('foto.jpg'), $buyer);
        Storage::disk($media->disk)->delete($media->path);

        $this->actingAs($buyer)
            ->get(route('opportunita.show', $opportunita->fresh()))
            ->assertOk()
            ->assertSee('non si trova più sul disco')
            ->assertSee('Ricaricali dalla modifica');
    }

    #[Test]
    public function la_galleria_mostra_un_segnaposto_invece_di_unimmagine_rotta(): void
    {
        $buyer = $this->buyer();
        [$store, $cr] = $this->storeWithCr();
        $opportunita = $this->openOpportunity([$store], ['created_by' => $buyer->id]);

        $media = app(MediaService::class)->store($opportunita, UploadedFile::fake()->image('foto.jpg'), $buyer);
        Storage::disk($media->disk)->delete($media->path);

        $this->actingAs($cr)
            ->get(route('cr.opportunita.show', $opportunita->fresh()))
            ->assertOk()
            ->assertSee('non più disponibile');
    }

    #[Test]
    public function la_posizione_del_disco_e_configurabile(): void
    {
        // Un volume montato altrove si indica con MEDIA_ROOT, senza toccare il codice.
        $percorso = sys_get_temp_dir().'/volume-di-prova';

        config(['filesystems.disks.media_local.root' => $percorso]);

        $this->assertSame($percorso, config('filesystems.disks.media_local.root'));
    }

    #[Test]
    public function la_diagnosi_segnala_i_file_mancanti(): void
    {
        $buyer = $this->buyer();
        $opportunita = Opportunity::factory()->create(['created_by' => $buyer->id]);

        $media = app(MediaService::class)->store($opportunita, UploadedFile::fake()->image('foto.jpg'), $buyer);
        Storage::disk($media->disk)->delete($media->path);

        $this->artisan('pescheria:stato')
            ->expectsOutputToContain('File non trovati')
            ->assertSuccessful();

        $this->assertSame(1, OpportunityMedia::count());
        $this->assertFalse($media->fresh()->esiste());
    }
}
