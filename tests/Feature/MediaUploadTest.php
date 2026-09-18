<?php

namespace Tests\Feature;

use App\Enums\MediaType;
use App\Exceptions\DomainException;
use App\Models\Opportunity;
use App\Services\MediaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\CreatesScenario;
use Tests\TestCase;

class MediaUploadTest extends TestCase
{
    use CreatesScenario, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake(config('pescheria.media.disk'));
    }

    #[Test]
    public function un_immagine_valida_viene_salvata_con_nome_casuale(): void
    {
        $buyer = $this->buyer();
        $opportunita = Opportunity::factory()->create(['created_by' => $buyer->id]);

        $file = UploadedFile::fake()->image('prodotto.jpg', 800, 600);
        $media = app(MediaService::class)->store($opportunita, $file, $buyer);

        $this->assertSame(MediaType::IMAGE, $media->type);
        $this->assertStringEndsWith('.jpg', $media->path);
        $this->assertStringNotContainsString('prodotto', $media->path);      // nome non prevedibile
        Storage::disk($media->disk)->assertExists($media->path);
    }

    #[Test]
    public function un_video_valido_viene_riconosciuto(): void
    {
        $buyer = $this->buyer();
        $opportunita = Opportunity::factory()->create(['created_by' => $buyer->id]);

        $file = UploadedFile::fake()->create('clip.mp4', 2048, 'video/mp4');
        $media = app(MediaService::class)->store($opportunita, $file, $buyer);

        $this->assertSame(MediaType::VIDEO, $media->type);
        $this->assertStringEndsWith('.mp4', $media->path);
    }

    #[Test]
    public function i_tipi_non_ammessi_sono_rifiutati(): void
    {
        $buyer = $this->buyer();
        $opportunita = Opportunity::factory()->create(['created_by' => $buyer->id]);

        $this->expectException(DomainException::class);

        app(MediaService::class)->store(
            $opportunita,
            UploadedFile::fake()->create('script.php', 10, 'application/x-php'),
            $buyer,
        );
    }

    #[Test]
    public function i_file_oltre_il_limite_sono_rifiutati(): void
    {
        config(['pescheria.media.max_image_mb' => 1]);

        $buyer = $this->buyer();
        $opportunita = Opportunity::factory()->create(['created_by' => $buyer->id]);

        $this->expectException(DomainException::class);

        app(MediaService::class)->store(
            $opportunita,
            UploadedFile::fake()->create('grande.jpg', 2048, 'image/jpeg'),
            $buyer,
        );
    }

    #[Test]
    public function i_media_non_sono_accessibili_senza_firma(): void
    {
        $buyer = $this->buyer();
        $opportunita = Opportunity::factory()->create(['created_by' => $buyer->id]);
        $media = app(MediaService::class)->store($opportunita, UploadedFile::fake()->image('foto.jpg'), $buyer);

        $this->actingAs($buyer)->get(route('media.show', $media))->assertForbidden();

        $urlFirmato = URL::temporarySignedRoute('media.show', now()->addMinutes(10), ['media' => $media->id]);
        $this->actingAs($buyer)->get($urlFirmato)->assertOk();
    }

    #[Test]
    public function un_cr_non_destinatario_non_scarica_il_media(): void
    {
        $buyer = $this->buyer();
        [$storeA, $crA] = $this->storeWithCr('PV310');
        [$storeB, $crB] = $this->storeWithCr('PV311');

        $opportunita = $this->openOpportunity([$storeB], ['created_by' => $buyer->id]);
        $media = app(MediaService::class)->store($opportunita, UploadedFile::fake()->image('foto.jpg'), $buyer);

        $urlFirmato = URL::temporarySignedRoute('media.show', now()->addMinutes(10), ['media' => $media->id]);

        $this->actingAs($crA)->get($urlFirmato)->assertForbidden();
        $this->actingAs($crB)->get($urlFirmato)->assertOk();
    }

    #[Test]
    public function eliminare_il_media_rimuove_anche_il_file(): void
    {
        $buyer = $this->buyer();
        $opportunita = Opportunity::factory()->create(['created_by' => $buyer->id]);
        $media = app(MediaService::class)->store($opportunita, UploadedFile::fake()->image('foto.jpg'), $buyer);
        $percorso = $media->path;

        app(MediaService::class)->delete($media);

        Storage::disk(config('pescheria.media.disk'))->assertMissing($percorso);
        $this->assertDatabaseMissing('opportunity_media', ['id' => $media->id]);
    }

    #[Test]
    public function solo_il_buyer_puo_caricare_media(): void
    {
        [$store, $cr] = $this->storeWithCr();
        $opportunita = $this->openOpportunity([$store]);

        $this->assertTrue($this->buyer()->can('uploadMedia', $opportunita));
        $this->assertFalse($this->tecnico()->can('uploadMedia', $opportunita));
        $this->assertFalse($cr->can('uploadMedia', $opportunita));
    }
}
