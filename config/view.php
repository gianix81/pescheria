<?php

return [

    /*
    |--------------------------------------------------------------------------
    | View Storage Paths
    |--------------------------------------------------------------------------
    */

    'paths' => [
        resource_path('views'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Compiled View Path
    |--------------------------------------------------------------------------
    |
    | Il valore predefinito del framework usa realpath(): se la cartella non
    | esiste ancora — succede quando un volume vuoto viene montato su storage/ —
    | realpath() restituisce false e l'applicazione si ferma con «Please provide
    | a valid cache path», prima ancora che un provider possa ricrearla.
    |
    | Qui si tiene il percorso così com'è: AppServiceProvider crea la cartella
    | se manca, e Blade ci compila dentro.
    |
    */

    'compiled' => env('VIEW_COMPILED_PATH') ?: storage_path('framework/views'),

];
