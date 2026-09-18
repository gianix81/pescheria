<?php

/*
|--------------------------------------------------------------------------
| Punto di ingresso per le piattaforme serverless (Vercel)
|--------------------------------------------------------------------------
| Qui si prepara l'ambiente PRIMA di avviare Laravel: /tmp scrivibile, log su
| stream, sessioni e cache sul database. Se qualcosa manca davvero, si risponde
| con un messaggio comprensibile invece del classico 500 a corpo vuoto, che non
| dice nulla a chi sta configurando il progetto.
|
| Questo file non viene usato in nessun altro ambiente.
*/

$radice = dirname(__DIR__);

require $radice.'/app/Support/Serverless.php';

use App\Support\Serverless;

function rispostaDiServizio(string $titolo, string $messaggio, array $elenco = [], int $stato = 500): never
{
    http_response_code($stato);
    header('Content-Type: text/html; charset=UTF-8');

    $voci = '';

    foreach ($elenco as $chiave => $spiegazione) {
        $voci .= '<li><code>'.htmlspecialchars((string) $chiave).'</code> — '
            .htmlspecialchars((string) $spiegazione).'</li>';
    }

    echo '<!DOCTYPE html><html lang="it"><head><meta charset="utf-8">'
        .'<meta name="viewport" content="width=device-width,initial-scale=1">'
        .'<title>Configurazione incompleta</title>'
        .'<style>body{font-family:system-ui,sans-serif;background:#f5f7f8;color:#0f172a;margin:0;'
        .'display:flex;min-height:100vh;align-items:center;justify-content:center;padding:24px}'
        .'main{max-width:640px;background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:28px}'
        .'h1{font-size:19px;margin:0 0 8px;color:#0b3a53}p{line-height:1.6;color:#334155}'
        .'code{background:#f1f5f9;padding:2px 6px;border-radius:4px;font-size:13px}'
        .'li{margin:6px 0;line-height:1.5}</style></head><body><main>'
        .'<h1>'.htmlspecialchars($titolo).'</h1><p>'.htmlspecialchars($messaggio).'</p>'
        .($voci !== '' ? '<ul>'.$voci.'</ul>' : '')
        .'</main></body></html>';

    exit;
}

// 1. Dipendenze PHP: senza vendor/ il require successivo sarebbe un errore fatale muto.
if (! Serverless::dipendenzeInstallate($radice)) {
    rispostaDiServizio(
        'Dipendenze PHP non installate',
        'La cartella vendor/ non è presente nel bundle: il build non ha eseguito «composer install». '
        .'Controlla i log di build del deploy.',
    );
}

// 2. Valori che su serverless hanno un solo significato sensato.
Serverless::preparaAmbiente();

// 2-bis. Modalità dimostrativa: nessun servizio esterno richiesto.
//        Si attiva con DEMO_MODE=true oppure, da sola, quando non è configurato
//        alcun database: senza dati reali è meglio un'applicazione funzionante
//        di una pagina di errore.
if (Serverless::inDemo()) {
    Serverless::preparaDemo(getenv('DEMO_DATABASE') ?: '/tmp/demo/pescheria.sqlite');

    // Rende la demo visibile anche a config/pescheria.php (banner, avvisi).
    putenv('DEMO_MODE=true');
    $_ENV['DEMO_MODE'] = 'true';
    $_SERVER['DEMO_MODE'] = 'true';
}

// 3. Variabili che solo chi configura il progetto può fornire.
//    Il controllo si applica solo dove NON esiste un file .env: su un server
//    tradizionale i valori arrivano da lì e Laravel li legge da solo.
$mancanti = is_file($radice.'/.env') ? [] : Serverless::variabiliMancanti();

if ($mancanti !== []) {
    rispostaDiServizio(
        'Configurazione incompleta',
        'Queste variabili d\'ambiente mancano o sono vuote. Impostale nel pannello del progetto '
        .'(Settings → Environment Variables) e rilancia il deploy.',
        $mancanti,
    );
}

// 4. Avvio di Laravel. Un errore qui finisce nei log della piattaforma; a schermo
//    il dettaglio compare solo con APP_DEBUG attivo, per non esporre dati interni.
try {
    require $radice.'/public/index.php';
} catch (Throwable $errore) {
    error_log('[avvio] '.$errore::class.': '.$errore->getMessage().' @ '
        .$errore->getFile().':'.$errore->getLine());

    $debug = filter_var(getenv('APP_DEBUG'), FILTER_VALIDATE_BOOL);

    rispostaDiServizio(
        'Avvio non riuscito',
        $debug
            ? $errore::class.': '.$errore->getMessage()
            : 'L\'applicazione non è riuscita ad avviarsi. Il dettaglio è nei log della piattaforma; '
              .'per vederlo a schermo imposta temporaneamente APP_DEBUG=true.',
    );
}
