<?php

use App\Services\Availability\FirstConfirmedFirstServed;

/*
|--------------------------------------------------------------------------
| Configurazione di dominio
|--------------------------------------------------------------------------
| Qui vivono le assunzioni MVP che il committente potrà rivedere senza
| toccare il codice. Vedi docs/01-analisi-e-assunzioni.md.
*/

return [

    // Pulsanti quantità rapida proposti al Capo Reparto (oltre a "Altra quantità").
    'quick_quantities' => [1, 2, 3, 4, 5, 6],

    // Rende obbligatoria la motivazione quando il CR sceglie "Non acquista".
    'require_refusal_reason' => env('REQUIRE_REFUSAL_REASON', false),

    // Politica di assegnazione della disponibilità limitata (assunzione A4).
    'allocation_strategy' => FirstConfirmedFirstServed::class,

    'media' => [
        'disk' => env('MEDIA_DISK', 'media_local'),
        'max_image_mb' => (int) env('MEDIA_MAX_IMAGE_MB', 10),
        'max_video_mb' => (int) env('MEDIA_MAX_VIDEO_MB', 100),
        'signed_url_minutes' => (int) env('MEDIA_SIGNED_URL_MINUTES', 30),
        'max_files' => (int) env('MEDIA_MAX_FILES', 10),
        'antivirus_enabled' => env('MEDIA_ANTIVIRUS_ENABLED', false),
        /*
        | Compressione lato browser, prima dell'invio (resources/js/compressione).
        | Valori passati al componente Alpine: cambiarli qui cambia il comportamento
        | dell'interfaccia senza toccare il JavaScript.
        */
        'compressione' => [
            'immagini' => [
                'latoMassimo' => (int) env('MEDIA_IMG_LATO_MAX', 1920),
                'qualita' => (float) env('MEDIA_IMG_QUALITA', 0.82),
                'sogliaByte' => (int) env('MEDIA_IMG_SOGLIA_KB', 200) * 1024,
            ],
            'video' => [
                'latoMassimo' => (int) env('MEDIA_VIDEO_LATO_MAX', 1280),
                'frameRate' => (int) env('MEDIA_VIDEO_FPS', 30),
                'sogliaByte' => (int) env('MEDIA_VIDEO_SOGLIA_MB', 3) * 1024 * 1024,
            ],
        ],

        'image_mimes' => ['image/jpeg', 'image/png', 'image/webp', 'image/heic', 'image/heif'],
        'video_mimes' => ['video/mp4', 'video/quicktime', 'video/webm', 'video/x-m4v'],
    ],

    'notifiche' => [
        'email_enabled' => env('NOTIFY_EMAIL_ENABLED', false),
        'whatsapp_enabled' => env('NOTIFY_WHATSAPP_ENABLED', false),
        // Minuti prima della scadenza in cui inviare il sollecito ai CR mancanti.
        'reminder_minutes' => array_map('intval', array_filter(explode(',', (string) env('NOTIFY_REMINDER_MINUTES', '120,30')))),
        'whatsapp' => [
            'api_url' => env('WHATSAPP_API_URL'),
            'token' => env('WHATSAPP_API_TOKEN'),
            'phone_id' => env('WHATSAPP_PHONE_ID'),
        ],
    ],

    /*
    | Segreto dell'endpoint /cron/esegui, usato solo dove non esiste un cron di
    | sistema (per esempio su Vercel). Vuoto = endpoint disattivato.
    */
    'cron_secret' => env('CRON_SECRET', ''),

    'export' => [
        'csv_delimiter' => ';',
        'csv_bom' => true,
    ],
];
