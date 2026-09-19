<div class="space-y-5">

    @if ($opportunity->status === \App\Enums\OpportunityStatus::ANNULLATA)
        <div class="rounded-lg border border-rose-300 bg-rose-50 px-4 py-3 text-sm text-rose-900" role="alert">
            <span aria-hidden="true">⚠</span> <strong>Annullata</strong> il {{ \App\Support\Format::dateTime($opportunity->cancelled_at) }} — {{ $opportunity->cancel_reason }}
        </div>
    @elseif ($opportunity->status === \App\Enums\OpportunityStatus::CHIUSA)
        <div class="rounded-lg border border-slate-300 bg-slate-100 px-4 py-3 text-sm text-slate-800" role="status">
            <span aria-hidden="true">■</span> <strong>Chiusa</strong> il {{ \App\Support\Format::dateTime($opportunity->closed_at) }} — {{ $opportunity->close_reason }}
        </div>
    @elseif ($opportunity->isSoldOut())
        <div class="rounded-lg border border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-900" role="status">
            <span aria-hidden="true">⊘</span> <strong>Esaurita:</strong> tutti i colli disponibili sono stati impegnati.
        </div>
    @endif

    @php $mediaMancanti = $opportunity->media->reject->esiste(); @endphp

    @if ($mediaMancanti->isNotEmpty())
        <div class="rounded-lg border border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-900" role="status">
            <p class="font-semibold">
                <span aria-hidden="true">🖼</span>
                {{ $mediaMancanti->count() }}
                {{ $mediaMancanti->count() === 1 ? 'file non si trova più sul disco' : 'file non si trovano più sul disco' }}
            </p>
            <p class="mt-1">
                Le righe ci sono ma il contenuto no: succede quando il disco non è persistente e viene
                azzerato a ogni pubblicazione.
                @can('update', $opportunity)
                    <a href="{{ route('buyer.opportunita.edit', $opportunity) }}" class="font-semibold underline">Ricaricali dalla modifica</a>.
                @endcan
            </p>
        </div>
    @endif

    @error('azione') <p class="error" role="alert"><span aria-hidden="true">⚠</span>{{ $message }}</p> @enderror

    {{-- Intestazione --}}
    <div class="card p-5">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $opportunity->reference }}</p>
                <h2 class="text-xl font-bold text-slate-900">{{ $opportunity->title }}</h2>
                <p class="text-sm text-slate-600">{{ $opportunity->article_code }} · PLU {{ $opportunity->plu ?: '—' }} · {{ $opportunity->description }}</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <x-badge-stato :stato="$opportunity->status" />
                @if ($opportunity->status === \App\Enums\OpportunityStatus::APERTA)
                    <x-countdown :scadenza="$opportunity->closes_at" />

                    @if ($opportunity->isExpired())
                        <span class="badge bg-amber-50 text-amber-900 ring-amber-300">
                            <span aria-hidden="true">⚠</span> In attesa di chiusura automatica
                        </span>
                    @endif
                @endif
            </div>
        </div>

        <div class="mt-4 grid gap-5 lg:grid-cols-2">
            <x-galleria-media :opportunita="$opportunity" />

            <div class="space-y-4">
                <x-prezzo-box :opportunita="$opportunity" />

                <dl class="grid grid-cols-2 gap-x-4 gap-y-3 text-sm">
                    <div><dt class="text-xs uppercase text-slate-500">Kg per collo</dt><dd class="font-semibold">{{ \App\Support\Format::kg($opportunity->kg_per_package, 2) }}</dd></div>
                    <div><dt class="text-xs uppercase text-slate-500">Lotto minimo</dt><dd class="font-semibold">{{ $opportunity->min_lot }} (×{{ $opportunity->order_multiple }})</dd></div>
                    <div><dt class="text-xs uppercase text-slate-500">Apertura</dt><dd class="font-semibold">{{ \App\Support\Format::dateTime($opportunity->opens_at) }}</dd></div>
                    <div><dt class="text-xs uppercase text-slate-500">Scadenza</dt><dd class="font-semibold">{{ \App\Support\Format::dateTime($opportunity->closes_at) }}</dd></div>
                    <div><dt class="text-xs uppercase text-slate-500">Consegna</dt><dd class="font-semibold">{{ \App\Support\Format::date($opportunity->delivery_date) }}</dd></div>
                    <div>
                        <dt class="text-xs uppercase text-slate-500">Disponibilità</dt>
                        <dd class="font-semibold">
                            @if ($opportunity->isLimited())
                                {{ $opportunity->total_packages }} totali · {{ $opportunity->committed_packages }} impegnati · {{ $opportunity->remainingPackages() }} residui
                            @else
                                Colli illimitati
                            @endif
                        </dd>
                    </div>
                    <div><dt class="text-xs uppercase text-slate-500">Autore</dt><dd class="font-semibold">{{ $opportunity->creator?->full_name }}</dd></div>
                    <div><dt class="text-xs uppercase text-slate-500">Verificatore</dt><dd class="font-semibold">{{ $opportunity->reviewer?->full_name ?? '—' }}</dd></div>
                </dl>

                @if ($opportunity->review_notes)
                    <p class="rounded-lg bg-amber-50 px-3 py-2 text-sm text-amber-900">
                        <strong>Note del Tecnico:</strong> {{ $opportunity->review_notes }}
                    </p>
                @endif
            </div>
        </div>

        {{-- Azioni --}}
        <div class="mt-5 flex flex-wrap gap-2 border-t border-slate-200 pt-4">
            @can('update', $opportunity)
                <a href="{{ route('buyer.opportunita.edit', $opportunity) }}" class="btn-ghost">Modifica</a>
            @endcan
            @can('duplicate', $opportunity)
                <button type="button" wire:click="duplica" class="btn-ghost">Duplica</button>
            @endcan
            @can('review', $opportunity)
                <a href="{{ route('tecnico.verifica', $opportunity) }}" class="btn-primary">Verifica</a>
            @endcan
            @can('close', $opportunity)
                <button type="button" wire:click="apriAzione('chiudi')" class="btn-ghost">Chiudi</button>
            @endcan
            @can('cancel', $opportunity)
                <button type="button" wire:click="apriAzione('annulla')" class="btn-ghost text-rose-700">Annulla</button>
            @endcan
            @can('delete', $opportunity)
                <button type="button" wire:click="apriAzione('elimina')" class="btn-danger">Elimina</button>
            @endcan
            <button type="button" wire:click="sollecita" class="btn-secondary">Sollecita mancanti</button>
            <a href="{{ route('export.index', ['opportunity_id' => $opportunity->id]) }}" class="btn-ghost">⤓ Export</a>
        </div>

        {{-- ------------------------------------------------- Avvisi su WhatsApp --}}
        @php $stato = $opportunity->status; @endphp

        @if ($stato === \App\Enums\OpportunityStatus::IN_VERIFICA)
            <div class="mt-4 rounded-lg border border-slate-200 bg-slate-50 p-4">
                <p class="text-sm font-semibold text-slate-800">
                    {{ $opportunity->isRipubblicazione() ? 'Chiedi al Tecnico di ripubblicarla' : 'Avvisa i Tecnici su WhatsApp' }}
                </p>
                <p class="help">
                    @if ($opportunity->isRipubblicazione())
                        L'opportunità è ferma: i punti vendita non la vedono finché un Tecnico non conferma.
                    @else
                        La notifica in-app è già partita: questo serve a farli intervenire subito.
                    @endif
                </p>

                <div class="mt-3 space-y-3">
                    @foreach ($tecnici as $t)
                        <div class="flex flex-wrap items-center justify-between gap-2 rounded-lg bg-white px-3 py-2">
                            <span class="text-sm">
                                <span class="font-semibold text-slate-900">{{ $t->full_name }}</span>
                                <span class="block text-xs text-slate-500">{{ $t->phone ?: 'nessun numero in anagrafica' }}</span>
                            </span>
                            <x-condividi-whatsapp
                                :testo="$opportunity->isRipubblicazione()
                                    ? \App\Support\WhatsApp::perRipubblicazione($opportunity)
                                    : \App\Support\WhatsApp::perVerifica($opportunity)"
                                :numero="$t->phone"
                                :etichetta="$t->phone ? 'Scrivi a '.$t->first_name : 'Scegli la chat'" />
                        </div>
                    @endforeach

                    @if ($tecnici->isEmpty())
                        <p class="text-sm text-slate-600">Nessun Tecnico attivo in anagrafica.</p>
                    @endif
                </div>
            </div>
        @elseif ($stato->isPubblicata() && $stato !== \App\Enums\OpportunityStatus::SCADUTA)
            <div class="mt-4 rounded-lg border border-emerald-200 bg-emerald-50 p-4">
                @php $aggiornamento = $opportunity->reviews->count() > 1; @endphp

                <p class="text-sm font-semibold text-emerald-900">
                    {{ $aggiornamento ? 'Comunica l\'aggiornamento al gruppo dei reparti' : 'Annuncia l\'apertura nel gruppo dei reparti' }}
                </p>
                <x-condividi-whatsapp class="mt-2"
                    variante="principale"
                    :testo="$aggiornamento
                        ? \App\Support\WhatsApp::perAggiornamento($opportunity)
                        : \App\Support\WhatsApp::perApertura($opportunity)"
                    :etichetta="$aggiornamento ? 'Comunica la modifica nel gruppo' : 'Condividi nel gruppo WhatsApp'"
                    descrizione="Scegli il gruppo dei reparti pescheria e invia. Il messaggio porta il collegamento alla scheda: l'ordine resta valido solo dall'app." />
            </div>
        @endif

        @if ($stato->isPubblicata() && $mancanti->isNotEmpty())
            <div class="mt-4 rounded-lg border border-amber-200 bg-amber-50 p-4">
                <p class="text-sm font-semibold text-amber-900">
                    Sollecita chi non ha ancora risposto ({{ $mancanti->count() }})
                </p>
                <p class="help">Scrivi direttamente a chi ordina per quel punto vendita, senza passare dal gruppo.</p>

                <div class="mt-3 space-y-2">
                    @foreach ($mancanti as $riga)
                        <div class="flex flex-wrap items-center justify-between gap-2 rounded-lg bg-white px-3 py-2">
                            <span class="text-sm">
                                <span class="font-semibold text-slate-900">{{ $riga['store']->code }}</span>
                                <span class="text-slate-600">— {{ $riga['store']->name }}</span>
                                @if ($riga['utenti']->isEmpty())
                                    <span class="block text-xs text-rose-700">Nessun utente attivo</span>
                                @endif
                            </span>

                            @foreach ($riga['utenti'] as $u)
                                <x-condividi-whatsapp
                                    :testo="\App\Support\WhatsApp::perSollecito($opportunity)"
                                    :numero="$u->phone"
                                    :etichetta="$u->phone ? 'Scrivi a '.$u->first_name : 'Scegli la chat'" />
                            @endforeach
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    {{-- Stato compilazioni --}}
    <div class="grid gap-4 sm:grid-cols-4">
        @foreach ([
            ['Destinatari', $statistiche['destinatari']],
            ['Risposte inviate', $statistiche['inviate']],
            ['Mancanti', $statistiche['mancanti']],
            ['Completamento', \App\Support\Format::percent($statistiche['percentuale'])],
        ] as [$etichetta, $valore])
            <div class="card p-4">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $etichetta }}</p>
                <p class="mt-1 text-2xl font-bold text-slate-900">{{ $valore }}</p>
            </div>
        @endforeach
    </div>

    {{-- Risposte per punto vendita --}}
    <div class="card overflow-hidden">
        <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-200 px-5 py-3">
            <h3 class="text-sm font-bold uppercase tracking-wide text-slate-500">Risposte dei punti vendita</h3>
            <p class="text-sm text-slate-700">
                Totale <strong>{{ $colliTotali }}</strong> colli ·
                <strong>{{ \App\Support\Format::decimal($kgTotali, 2) }}</strong> kg
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th scope="col" class="th">Punto vendita</th>
                        <th scope="col" class="th">Stato</th>
                        <th scope="col" class="th">Colli</th>
                        <th scope="col" class="th">Kg</th>
                        <th scope="col" class="th">Inviata</th>
                        <th scope="col" class="th">Utente</th>
                        <th scope="col" class="th"><span class="sr-only">Azioni</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($opportunity->stores as $store)
                        @php $r = $risposte[$store->id] ?? null; @endphp
                        <tr class="hover:bg-slate-50">
                            <td class="td font-medium">{{ $store->code }} — {{ $store->name }}</td>
                            <td class="td"><x-badge-risposta :stato="$r?->status ?? $statoPredefinito" /></td>
                            <td class="td font-semibold">{{ $r?->packages ?? 0 }}</td>
                            <td class="td">{{ \App\Support\Format::decimal($r?->kg ?? 0, 2) }}</td>
                            <td class="td">{{ $r?->submitted_at ? \App\Support\Format::dateTime($r->submitted_at) : '—' }}</td>
                            <td class="td">{{ $r?->lastActor?->full_name ?? '—' }}</td>
                            <td class="td text-right">
                                @if ($r && auth()->user()->isTecnico())
                                    <button type="button" wire:click="apriAzione('riapri', {{ $r->id }})"
                                            class="font-semibold text-laguna-600 underline">Riapri</button>
                                @endif
                                @if ($r?->refusal_reason)
                                    <span class="block text-xs text-slate-500">{{ $r->refusal_reason }}</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Dialog azione con motivazione obbligatoria --}}
    @if ($azione !== '')
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4" role="dialog" aria-modal="true" aria-labelledby="titolo-azione">
            <div class="card w-full max-w-md p-5">
                <h3 id="titolo-azione" class="text-lg font-bold text-slate-900">
                    {{ [
                        'chiudi' => 'Chiudi opportunità',
                        'annulla' => 'Annulla opportunità',
                        'riapri' => 'Riapri risposta',
                        'elimina' => 'Eliminare definitivamente?',
                    ][$azione] }}
                </h3>

                @if ($azione === 'elimina')
                    <div class="mt-2 space-y-2 text-sm text-slate-700">
                        <p>
                            <strong>{{ $opportunity->reference }}</strong> — {{ $opportunity->description }}
                        </p>
                        <p class="rounded-lg bg-rose-50 px-3 py-2 text-rose-900">
                            Spariscono anche <strong>{{ $opportunity->media->count() }}</strong> file,
                            <strong>{{ $statistiche['inviate'] }}</strong> risposte dei punti vendita e
                            <strong>{{ $colliTotali }}</strong> colli ordinati.
                            L'operazione non si annulla: nell'audit log resta la traccia di cosa è stato eliminato.
                        </p>

                        @if ($opportunity->eliminabileSenzaMotivazione())
                            <p class="text-slate-600">
                                Scaduta da {{ $opportunity->giorniDallaScadenza() }} giorni: la motivazione non è richiesta.
                            </p>
                        @else
                            <p class="text-slate-600">
                                Non è scaduta da almeno un mese: indica il motivo.
                            </p>
                        @endif
                    </div>
                @else
                    <p class="mt-1 text-sm text-slate-600">La motivazione è obbligatoria e viene registrata nell'audit log.</p>
                @endif

                @if ($azione === 'riapri')
                    <div class="mt-3">
                        <label for="nuovaScadenza" class="label">Nuova scadenza per il punto vendita</label>
                        <input id="nuovaScadenza" type="datetime-local" wire:model="nuovaScadenza" class="input">
                    </div>
                @endif

                @unless ($azione === 'elimina' && $opportunity->eliminabileSenzaMotivazione())
                    <div class="mt-3">
                        <label for="motivazione" class="label">Motivazione *</label>
                        <textarea id="motivazione" rows="3" wire:model="motivazione" class="input py-2"></textarea>
                    </div>
                @endunless

                <div class="mt-5 flex gap-3">
                    <button type="button" wire:click="$set('azione', '')" class="btn-ghost flex-1">Annulla</button>
                    <button type="button" wire:click="conferma"
                            @class(['flex-1', 'btn-danger' => $azione === 'elimina', 'btn-primary' => $azione !== 'elimina'])>
                        {{ $azione === 'elimina' ? 'Elimina definitivamente' : 'Conferma' }}
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
