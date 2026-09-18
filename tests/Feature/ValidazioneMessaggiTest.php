<?php

namespace Tests\Feature;

use App\Livewire\Buyer\OpportunitaForm;
use App\Models\Opportunity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\CreatesScenario;
use Tests\TestCase;

/**
 * L'interfaccia è in italiano: nessun messaggio deve mai mostrare la chiave
 * grezza del traduttore (per esempio "validation.uploaded").
 */
class ValidazioneMessaggiTest extends TestCase
{
    use CreatesScenario, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake(config('pescheria.media.disk'));
    }

    #[Test]
    public function i_messaggi_di_validazione_sono_tradotti_in_italiano(): void
    {
        $componente = Livewire::actingAs($this->buyer())
            ->test(OpportunitaForm::class)
            ->set('article_code', '')
            ->set('description', '')
            ->set('kg_per_package', null)
            ->set('store_ids', [])
            ->call('salvaBozza');

        $messaggi = $componente->errors()->all();

        $this->assertNotEmpty($messaggi);

        foreach ($messaggi as $messaggio) {
            $this->assertStringNotContainsString('validation.', $messaggio, "Messaggio non tradotto: {$messaggio}");
        }

        $this->assertContains('Il campo codice articolo è obbligatorio.', $messaggi);
        $this->assertContains('Seleziona almeno un punto vendita destinatario.', $messaggi);
    }

    #[Test]
    public function il_messaggio_di_upload_fallito_e_leggibile(): void
    {
        $traduzione = trans('validation.custom')['nuoviMedia.*']['uploaded'];

        $this->assertStringNotContainsString('validation.', $traduzione);
        $this->assertStringContainsString('caricamento', $traduzione);

        $generico = trans('validation.uploaded', ['attribute' => 'file']);
        $this->assertStringNotContainsString('validation.', $generico);
    }

    #[Test]
    public function i_limiti_di_upload_coprono_i_video_da_100_mb(): void
    {
        // Livewire: il default del pacchetto sarebbe 12 MB.
        $regole = config('livewire.temporary_file_upload.rules');

        $this->assertContains('max:102400', $regole, 'Livewire limiterebbe gli upload sotto i 100 MB.');

        // PHP: i default (2M/8M) fanno fallire l'upload prima della validazione.
        $this->assertGreaterThanOrEqual(
            100 * 1024 * 1024,
            $this->inBytes(ini_get('upload_max_filesize')),
            'upload_max_filesize di PHP è sotto i 100 MB: vedi docker/php/uploads.ini.',
        );

        $this->assertGreaterThanOrEqual(
            100 * 1024 * 1024,
            $this->inBytes(ini_get('post_max_size')),
            'post_max_size di PHP è sotto i 100 MB: vedi docker/php/uploads.ini.',
        );
    }

    #[Test]
    public function un_video_da_50_mb_viene_accettato(): void
    {
        [$store] = $this->storeWithCr();

        Livewire::actingAs($this->buyer())
            ->test(OpportunitaForm::class)
            ->set('article_code', 'ART50MB')
            ->set('description', 'Orata')
            ->set('title', 'Orata del giorno')
            ->set('kg_per_package', 5)
            ->set('purchase_price', 5)
            ->set('sale_price_gross', 8)
            ->set('store_ids', [$store->id])
            ->set('nuoviMedia', [UploadedFile::fake()->create('verticale.mp4', 50 * 1024, 'video/mp4')])
            ->call('caricaMedia')
            ->assertHasNoErrors();

        $this->assertSame(1, Opportunity::firstWhere('article_code', 'ART50MB')->media()->count());
    }

    #[Test]
    public function un_tipo_di_file_non_ammesso_viene_respinto_con_messaggio_chiaro(): void
    {
        [$store] = $this->storeWithCr();

        $componente = Livewire::actingAs($this->buyer())
            ->test(OpportunitaForm::class)
            ->set('article_code', 'ARTPDF')
            ->set('description', 'Orata')
            ->set('title', 'Orata')
            ->set('kg_per_package', 5)
            ->set('purchase_price', 5)
            ->set('sale_price_gross', 8)
            ->set('store_ids', [$store->id])
            ->set('nuoviMedia', [UploadedFile::fake()->create('listino.pdf', 100, 'application/pdf')])
            ->call('caricaMedia')
            ->assertHasErrors('nuoviMedia.*');

        $messaggi = $componente->errors()->all();
        $this->assertStringContainsString('Sono ammesse foto', implode(' ', $messaggi));
    }

    private function inBytes(string $valore): int
    {
        $unita = strtolower(substr($valore, -1));
        $numero = (int) $valore;

        return match ($unita) {
            'g' => $numero * 1024 ** 3,
            'm' => $numero * 1024 ** 2,
            'k' => $numero * 1024,
            default => $numero,
        };
    }
}
