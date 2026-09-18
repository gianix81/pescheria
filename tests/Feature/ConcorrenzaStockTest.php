<?php

namespace Tests\Feature;

use App\Enums\ResponseStatus;
use App\Models\Opportunity;
use App\Models\Response;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\CreatesScenario;
use Tests\TestCase;

/**
 * Concorrenza reale su disponibilità limitata.
 *
 * Il test NON usa transazioni di test (DatabaseMigrations invece di RefreshDatabase):
 * i dati devono essere visibili a processi PHP separati, avviati in parallelo, che
 * competono sullo stesso stock. È l'unico modo per verificare davvero il lock di riga.
 */
class ConcorrenzaStockTest extends TestCase
{
    use CreatesScenario, DatabaseMigrations;

    #[Test]
    public function invii_simultanei_non_possono_sovra_allocare(): void
    {
        $totaleColli = 10;
        $numeroPuntiVendita = 6;
        $colliRichiestiCiascuno = 3;        // 6 × 3 = 18 richiesti su 10 disponibili

        $coppie = collect(range(1, $numeroPuntiVendita))
            ->map(fn (int $i) => $this->storeWithCr('PVC'.$i))
            ->all();

        $opportunita = $this->limitedOpportunity(array_column($coppie, 0), $totaleColli);

        $avvio = microtime(true) + 1.5;     // barriera comune
        $processi = [];

        foreach ($coppie as [$store, $cr]) {
            $comando = sprintf(
                'DB_DATABASE=%s APP_ENV=testing %s %s %d %d %d %d %s',
                escapeshellarg(config('database.connections.mysql.database')),
                escapeshellarg(PHP_BINARY),
                escapeshellarg(base_path('tests/Support/invio_concorrente.php')),
                $opportunita->id,
                $store->id,
                $cr->id,
                $colliRichiestiCiascuno,
                escapeshellarg((string) $avvio),
            );

            $processi[] = proc_open($comando, [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
            $pipeStdout[] = $pipes[1];
            $pipeStderr[] = $pipes[2];
        }

        $uscite = [];

        foreach ($processi as $i => $processo) {
            $uscite[] = trim((string) stream_get_contents($pipeStdout[$i]));
            fclose($pipeStdout[$i]);
            fclose($pipeStderr[$i]);
            proc_close($processo);
        }

        $errori = array_filter($uscite, fn (string $u) => str_starts_with($u, 'ERRORE'));
        $this->assertSame([], $errori, 'Errori inattesi: '.implode(' | ', $errori));

        $confermati = Response::where('opportunity_id', $opportunita->id)
            ->where('status', ResponseStatus::INVIATA_ACQUISTO)
            ->sum('packages');

        $aggiornata = Opportunity::find($opportunita->id);

        // L'invariante: mai più colli di quelli disponibili.
        $this->assertLessThanOrEqual($totaleColli, (int) $confermati, 'Sovra-allocazione: '.implode(' | ', $uscite));
        $this->assertSame((int) $confermati, (int) $aggiornata->committed_packages);

        // Con 3 colli a testa su 10 disponibili passano esattamente 3 punti vendita.
        $this->assertSame(9, (int) $confermati);
        $this->assertSame(3, collect($uscite)->filter(fn ($u) => str_starts_with($u, 'OK'))->count());
        $this->assertSame(3, collect($uscite)->filter(fn ($u) => str_starts_with($u, 'STOCK'))->count());
    }
}
