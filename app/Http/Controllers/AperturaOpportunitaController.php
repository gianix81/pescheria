<?php

namespace App\Http\Controllers;

use App\Models\Opportunity;
use Illuminate\Http\Request;

/**
 * Indirizzo unico di un'opportunità, da mettere nei messaggi.
 *
 * Un messaggio WhatsApp finisce in un gruppo misto: Buyer, Tecnici e capi
 * reparto aprono lo stesso collegamento, ma ognuno ha la propria schermata.
 * Mettere nel testo l'indirizzo di una sola di quelle viste condannava tutti
 * gli altri a un 403.
 *
 * Qui si smista in base a chi apre. Chi non ha ancora fatto l'accesso viene
 * portato al login e poi riportato qui, quindi il collegamento funziona anche
 * da un telefono che non ha mai aperto l'applicazione.
 */
class AperturaOpportunitaController extends Controller
{
    public function __invoke(Request $request, Opportunity $opportunity)
    {
        $utente = $request->user();

        if ($utente->isCapoReparto()) {
            abort_unless(
                $utente->can('view', $opportunity),
                403,
                'Questa opportunità non è destinata al tuo punto vendita.',
            );

            return redirect()->route('cr.opportunita.show', $opportunity);
        }

        return redirect()->route('opportunita.show', $opportunity);
    }
}
