<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Montando un volume su /app/storage il volume, vuoto, copre la struttura
 * creata durante il build: senza storage/framework/views Laravel non compila
 * più le viste e la pagina resta bianca.
 */
class CartelleScrivibiliTest extends TestCase
{
    #[Test]
    public function le_cartelle_di_storage_vengono_ricreate_se_mancano(): void
    {
        $cartelle = [
            storage_path('framework/views'),
            storage_path('framework/cache/data'),
            storage_path('framework/sessions'),
            storage_path('logs'),
        ];

        foreach ($cartelle as $cartella) {
            $this->assertDirectoryExists($cartella);
        }
    }

    #[Test]
    public function lapplicazione_risponde_anche_dopo_che_sono_state_rimosse(): void
    {
        $viste = storage_path('framework/views');

        // Simula il montaggio di un volume vuoto su storage/.
        if (is_dir($viste)) {
            foreach (glob($viste.'/*') as $file) {
                @unlink($file);
            }
            @rmdir($viste);
        }

        $this->assertDirectoryDoesNotExist($viste);

        // Una nuova richiesta ricrea ciò che serve e la pagina si rende.
        $this->refreshApplication();

        $this->get(route('login'))->assertOk()->assertSee('Accedi');
        $this->assertDirectoryExists($viste);
    }
}
