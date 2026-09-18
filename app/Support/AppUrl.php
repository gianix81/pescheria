<?php

namespace App\Support;

/**
 * Determina l'URL di base dell'applicazione.
 *
 * Su alcune piattaforme la variabile APP_URL viene valorizzata con un
 * riferimento (`https://${{RAILWAY_PUBLIC_DOMAIN}}`) che al momento del build
 * non è ancora risolto: il dominio pubblico non esiste finché il primo deploy
 * non è andato a buon fine. Laravel riceve allora un URL non valido e si ferma
 * con «Invalid URI» già durante `config:cache`.
 *
 * Qui si accetta solo un URL effettivamente valido; altrimenti si ricade sul
 * dominio fornito dalla piattaforma e, in ultima istanza, su localhost.
 */
final class AppUrl
{
    /** Variabili con cui le piattaforme espongono il dominio assegnato. */
    private const DOMINI_PIATTAFORMA = [
        'RAILWAY_PUBLIC_DOMAIN',
        'VERCEL_PROJECT_PRODUCTION_URL',
        'VERCEL_URL',
        'RENDER_EXTERNAL_HOSTNAME',
        'FLY_APP_NAME',
    ];

    public static function risolvi(): string
    {
        $dichiarato = self::normalizza(env('APP_URL'));

        if ($dichiarato !== null) {
            return $dichiarato;
        }

        foreach (self::DOMINI_PIATTAFORMA as $variabile) {
            $dominio = trim((string) env($variabile));

            if ($dominio === '' || ! self::dominioPlausibile($dominio)) {
                continue;
            }

            return 'https://'.$dominio;
        }

        return 'http://localhost';
    }

    /** Restituisce l'URL solo se è utilizzabile davvero. */
    private static function normalizza(mixed $valore): ?string
    {
        $url = trim((string) $valore);

        if ($url === '') {
            return null;
        }

        $url = rtrim($url, '/');

        // Riferimento non risolto dalla piattaforma: "https://${{...}}".
        if (str_contains($url, '${') || str_contains($url, '{{')) {
            return null;
        }

        $parti = parse_url($url);

        if ($parti === false || empty($parti['scheme']) || empty($parti['host'])) {
            return null;
        }

        return $url;
    }

    private static function dominioPlausibile(string $dominio): bool
    {
        return ! str_contains($dominio, '${')
            && ! str_contains($dominio, '{{')
            && ! str_contains($dominio, '/')
            && str_contains($dominio, '.');
    }
}
