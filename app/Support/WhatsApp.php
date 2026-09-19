<?php

namespace App\Support;

use App\Models\Opportunity;
use App\Models\Response;

/**
 * Testi e collegamenti per WhatsApp.
 *
 * L'azienda usa WhatsApp personale, non Business: nessuna API, nessun token,
 * nessun costo. Tutto passa quindi dai collegamenti «click to chat», che
 * funzionano identici su telefono e su WhatsApp Web.
 *
 *  - verso una PERSONA: wa.me/<numero> apre direttamente quella conversazione
 *    con il messaggio già scritto;
 *  - verso un GRUPPO: wa.me senza numero apre l'elenco delle chat, si sceglie
 *    il gruppo e si invia. Nessuna API ufficiale permette di scrivere nei
 *    gruppi, quindi l'ultimo tocco resta della persona.
 *
 * Il collegamento è sempre lo stesso indirizzo neutro (`/o/{id}`), che smista
 * chi lo apre verso la propria schermata: un messaggio finisce in un gruppo
 * misto e non può sapere in anticipo chi lo leggerà. Richiede comunque
 * l'accesso: WhatsApp è un avviso, mai la fonte dell'ordine.
 */
final class WhatsApp
{
    /** Prefisso usato quando il numero in anagrafica non lo riporta. */
    private const PREFISSO_PREDEFINITO = '39';

    /**
     * Collegamento che apre WhatsApp con il testo già scritto.
     * Con un numero apre quella conversazione, senza apre l'elenco delle chat
     * (è così che si raggiunge un gruppo).
     */
    public static function link(string $testo, ?string $telefono = null): string
    {
        $numero = self::numero($telefono);

        return 'https://wa.me/'.($numero ?? '').'?text='.rawurlencode($testo);
    }

    /**
     * Normalizza un numero per wa.me: solo cifre, con prefisso internazionale.
     * «+39 333 111 2233», «0039 333 1112233» e «333 1112233» danno lo stesso
     * risultato. Restituisce null se non è un numero plausibile.
     */
    public static function numero(?string $telefono): ?string
    {
        $grezzo = trim((string) $telefono);

        if ($grezzo === '') {
            return null;
        }

        $internazionale = str_starts_with($grezzo, '+') || str_starts_with(preg_replace('/\s+/', '', $grezzo), '00');
        $cifre = preg_replace('/\D+/', '', $grezzo);

        if ($cifre === '') {
            return null;
        }

        if (str_starts_with($cifre, '00')) {
            $cifre = substr($cifre, 2);
            $internazionale = true;
        }

        // Numero nazionale: si antepone il prefisso predefinito.
        if (! $internazionale) {
            $cifre = self::PREFISSO_PREDEFINITO.ltrim($cifre, '0');
        }

        return strlen($cifre) >= 8 && strlen($cifre) <= 15 ? $cifre : null;
    }

    /** Sollecito diretto a un punto vendita che non ha ancora risposto. */
    public static function perSollecito(Opportunity $opportunity): string
    {
        return implode("\n", [
            '⏰ Manca la tua risposta: '.$opportunity->title,
            'Scadenza '.Format::dateTime($opportunity->closes_at),
            '',
            'Rispondi qui: '.route('opportunita.apri', $opportunity),
        ]);
    }

    /** Avviso ai Tecnici: c'è una nuova opportunità da verificare. */
    public static function perVerifica(Opportunity $opportunity): string
    {
        return implode("\n", array_filter([
            '🔎 Da verificare: '.$opportunity->title,
            $opportunity->reference.' · '.$opportunity->article_code
                .($opportunity->plu ? ' · PLU '.$opportunity->plu : ''),
            'Consegna '.Format::date($opportunity->delivery_date)
                .' · scadenza ordini '.Format::dateTime($opportunity->closes_at),
            '',
            'Apri la scheda: '.route('opportunita.apri', $opportunity),
        ]));
    }

    /** Annuncio al gruppo dei reparti: l'opportunità è aperta. */
    public static function perApertura(Opportunity $opportunity): string
    {
        $disponibilita = $opportunity->isLimited()
            ? $opportunity->remainingPackages().' colli disponibili'
            : 'colli illimitati';

        return implode("\n", array_filter([
            '🐟 '.$opportunity->title,
            $opportunity->article_code.($opportunity->plu ? ' · PLU '.$opportunity->plu : ''),
            Format::money($opportunity->sale_price_gross).'/kg al pubblico · '
                .Format::decimal($opportunity->kg_per_package, 1).' kg per collo',
            $disponibilita,
            '📅 Consegna '.Format::date($opportunity->delivery_date),
            '⏱ Rispondere entro il '.Format::dateTime($opportunity->closes_at),
            '',
            'Ordina qui: '.route('opportunita.apri', $opportunity),
            '',
            'Le risposte valgono solo dall\'app.',
        ]));
    }

    /** Richiesta di ripubblicazione: l'opportunità era già aperta ed è cambiata. */
    public static function perRipubblicazione(Opportunity $opportunity): string
    {
        return implode("\n", [
            '✏️ Modificata, da confermare: '.$opportunity->title,
            $opportunity->reference.' · '.$opportunity->article_code,
            'Era già aperta: resta ferma finché non la confermi.',
            '',
            'Verifica qui: '.route('opportunita.apri', $opportunity),
        ]);
    }

    /** Annuncio al gruppo dopo una modifica confermata dal Tecnico. */
    public static function perAggiornamento(Opportunity $opportunity): string
    {
        return implode("\n", array_filter([
            '✏️ Aggiornata: '.$opportunity->title,
            $opportunity->article_code.($opportunity->plu ? ' · PLU '.$opportunity->plu : ''),
            Format::money($opportunity->sale_price_gross).'/kg al pubblico · '
                .Format::decimal($opportunity->kg_per_package, 1).' kg per collo',
            $opportunity->isLimited() ? $opportunity->remainingPackages().' colli disponibili' : 'colli illimitati',
            '📅 Consegna '.Format::date($opportunity->delivery_date),
            '⏱ Rispondere entro il '.Format::dateTime($opportunity->closes_at),
            '',
            'Qualcosa è cambiato: ricontrollate prima di confermare.',
            'Scheda: '.route('opportunita.apri', $opportunity),
        ]));
    }

    /** Conferma del punto vendita, da condividere nel gruppo. */
    public static function perRisposta(Response $response): string
    {
        $opportunity = $response->opportunity;
        $store = $response->store;

        $riga = $response->packages > 0
            ? '✅ '.$store->code.' ordina '.$response->packages.' colli ('
                .Format::decimal($response->kg, 1).' kg)'
            : '❌ '.$store->code.' non acquista';

        $totale = $opportunity->totalPackagesOrdered();

        return implode("\n", array_filter([
            $riga,
            $opportunity->title.' · '.$opportunity->article_code,
            'Totale finora: '.$totale.' colli ('
                .Format::decimal($opportunity->totalKgOrdered(), 1).' kg)',
            $opportunity->isLimited()
                ? 'Restano '.$opportunity->remainingPackages().' colli'
                : null,
            '',
            'Scheda: '.route('opportunita.apri', $opportunity),
        ]));
    }
}
