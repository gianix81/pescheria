<?php

namespace App\Support;

use App\Models\Opportunity;
use App\Models\Response;

/**
 * Testi e collegamenti per WhatsApp.
 *
 * Due meccanismi distinti, perché WhatsApp stesso li distingue:
 *
 *  - verso una PERSONA l'invio può essere automatico, tramite WhatsApp Business
 *    API (vedi Services\Notifications\WhatsAppGateway);
 *  - verso un GRUPPO nessuna API ufficiale consente di scrivere. Qui si genera
 *    quindi un collegamento «click to chat» con il messaggio già pronto: chi lo
 *    tocca sceglie il gruppo e invia. Un tocco, nessuna riscrittura a mano.
 *
 * In entrambi i casi il messaggio porta un collegamento alla scheda, che
 * richiede autenticazione: WhatsApp resta un canale di avviso, mai la fonte
 * dell'ordine.
 */
final class WhatsApp
{
    /** Collegamento che apre WhatsApp con il testo già scritto. */
    public static function link(string $testo): string
    {
        return 'https://wa.me/?text='.rawurlencode($testo);
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
            'Apri la scheda: '.route('opportunita.show', $opportunity),
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
            'Ordina qui: '.route('cr.opportunita.show', $opportunity),
            '',
            'Le risposte valgono solo dall\'app.',
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
            'Scheda: '.route('cr.opportunita.show', $opportunity),
        ]));
    }
}
