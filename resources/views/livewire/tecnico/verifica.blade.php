<div class="grid gap-5 lg:grid-cols-3">

    {{-- Pannello principale: anteprima completa --}}
    <div class="space-y-4 lg:col-span-2">
        @if ($opportunity->isRipubblicazione())
            <div class="rounded-lg border border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-900" role="status">
                <p class="font-semibold"><span aria-hidden="true">✏️</span> Ripubblicazione</p>
                <p class="mt-1">
                    Questa opportunità era già aperta ed è stata modificata dal Buyer: in questo momento i punti
                    vendita non la vedono. Ha già raccolto {{ $opportunity->responses()->count() }} risposte, che
                    restano valide. Confermala per rimetterla in linea.
                </p>
            </div>
        @endif

        <div class="card p-5">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $opportunity->reference }}</p>
                    <h2 class="text-xl font-bold text-slate-900">{{ $opportunity->title }}</h2>
                </div>
                <x-badge-stato :stato="$opportunity->status" />
            </div>

            <div class="mt-4 grid gap-5 sm:grid-cols-2">
                <x-galleria-media :opportunita="$opportunity" />
                <div class="space-y-4">
                    <x-prezzo-box :opportunita="$opportunity" />
                    <dl class="grid grid-cols-2 gap-3 text-sm">
                        <div><dt class="text-xs uppercase text-slate-500">Codice</dt><dd class="font-semibold">{{ $opportunity->article_code }}</dd></div>
                        <div><dt class="text-xs uppercase text-slate-500">PLU</dt><dd class="font-semibold">{{ $opportunity->plu ?: '—' }}</dd></div>
                        <div><dt class="text-xs uppercase text-slate-500">Kg per collo</dt><dd class="font-semibold">{{ \App\Support\Format::kg($opportunity->kg_per_package, 2) }}</dd></div>
                        <div><dt class="text-xs uppercase text-slate-500">Disponibilità</dt><dd class="font-semibold">{{ $opportunity->isLimited() ? $opportunity->total_packages.' colli' : 'Illimitata' }}</dd></div>
                        <div><dt class="text-xs uppercase text-slate-500">Apertura</dt><dd class="font-semibold">{{ \App\Support\Format::dateTime($opportunity->opens_at) }}</dd></div>
                        <div><dt class="text-xs uppercase text-slate-500">Scadenza</dt><dd class="font-semibold">{{ \App\Support\Format::dateTime($opportunity->closes_at) }}</dd></div>
                        <div><dt class="text-xs uppercase text-slate-500">Consegna</dt><dd class="font-semibold">{{ \App\Support\Format::date($opportunity->delivery_date) }}</dd></div>
                        <div><dt class="text-xs uppercase text-slate-500">Destinatari</dt><dd class="font-semibold">{{ $opportunity->stores->count() }} PdV</dd></div>
                    </dl>
                    <p class="text-sm text-slate-600">{{ $opportunity->commercial_description }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Pannello laterale sticky: checklist e decisione --}}
    <aside class="lg:sticky lg:top-20 lg:self-start">
        <div class="card p-5">
            <h3 class="text-sm font-bold uppercase tracking-wide text-slate-500">Checklist di verifica</h3>

            <ul class="mt-3 space-y-2 text-sm">
                @foreach ([
                    'anagrafica' => 'Codice articolo, PLU e descrizione corretti',
                    'prezzi' => 'Prezzi, IVA, ricarico e margine coerenti',
                    'confezionamento' => 'Kg per collo e lotto minimo verificati',
                    'tempi' => 'Scadenza e data di consegna sostenibili',
                    'media' => 'Foto o video rappresentativi del prodotto',
                    'destinatari' => 'Punti vendita destinatari corretti',
                ] as $chiave => $etichetta)
                    <li>
                        <label class="flex items-start gap-2">
                            <input type="checkbox" wire:model="checklist.{{ $chiave }}"
                                   class="mt-0.5 rounded border-slate-300 text-mare-700 focus:ring-laguna-500">
                            <span class="text-slate-700">{{ $etichetta }}</span>
                        </label>
                    </li>
                @endforeach
            </ul>

            @if ($problemi)
                <div class="mt-4 rounded-lg border border-amber-300 bg-amber-50 px-3 py-2 text-sm text-amber-900" role="status">
                    <p class="font-semibold"><span aria-hidden="true">⚠</span> Blocchi alla pubblicazione</p>
                    <ul class="mt-1 list-inside list-disc">
                        @foreach ($problemi as $problema)
                            <li>{{ $problema }}</li>
                        @endforeach
                    </ul>

                    @if (! $opportunity->hasMedia() && ! $opportunity->media_exception)
                        <button type="button" wire:click="$toggle('eccezioneMediaAperta')" class="mt-2 text-xs font-semibold underline">
                            Autorizza eccezione senza foto/video
                        </button>
                    @endif
                </div>
            @endif

            @if ($eccezioneMediaAperta)
                <div class="mt-3 rounded-lg border border-slate-300 p-3">
                    <label for="motivazioneEccezioneMedia" class="label">Motivazione dell'eccezione *</label>
                    <textarea id="motivazioneEccezioneMedia" rows="2" wire:model="motivazioneEccezioneMedia" class="input py-2"></textarea>
                    <button type="button" wire:click="concediEccezioneMedia" class="btn-ghost mt-2 w-full">Registra eccezione</button>
                </div>
            @endif

            {{-- Correzione del prezzo di vendita: unico dato che il Tecnico tocca --}}
            @can('updatePrice', $opportunity)
                <div class="mt-4 rounded-lg border border-slate-200 bg-slate-50 p-3">
                    <p class="text-sm font-semibold text-slate-800">Prezzo di vendita</p>
                    <p class="help">
                        Acquisto {{ \App\Support\Format::money($opportunity->purchase_price) }}/kg ·
                        IVA {{ \App\Support\Format::percent($opportunity->vat_rate, 0) }}
                    </p>

                    <div class="mt-2">
                        <label for="prezzoVendita" class="sr-only">Prezzo di vendita al pubblico</label>
                        <div class="flex items-center gap-2">
                            <input id="prezzoVendita" type="number" step="0.01" min="0.01"
                                   wire:model.live.debounce.400ms="prezzoVendita"
                                   class="input @error('prezzoVendita') input-error @enderror">
                            <span class="shrink-0 text-sm text-slate-600">/kg</span>
                        </div>
                        @error('prezzoVendita') <p class="error"><span aria-hidden="true">⚠</span>{{ $message }}</p> @enderror
                    </div>

                    {{-- Effetto della correzione, prima di salvare --}}
                    <dl class="mt-2 grid grid-cols-3 gap-2 text-center text-xs" aria-live="polite">
                        <div class="rounded bg-white px-2 py-1.5">
                            <dt class="text-slate-500">Netto</dt>
                            <dd class="font-bold text-slate-900">{{ \App\Support\Format::money($this->prezziProposti['net']) }}</dd>
                        </div>
                        <div class="rounded bg-white px-2 py-1.5">
                            <dt class="text-slate-500" title="Utile diviso il prezzo di acquisto.">Ricarico</dt>
                            <dd class="font-bold text-slate-900">{{ \App\Support\Format::percent($this->prezziProposti['markup']) }}</dd>
                        </div>
                        <div class="rounded bg-white px-2 py-1.5">
                            <dt class="text-slate-500" title="Utile diviso il prezzo di vendita netto IVA.">Margine</dt>
                            <dd class="font-bold text-slate-900">{{ \App\Support\Format::percent($this->prezziProposti['margin']) }}</dd>
                        </div>
                    </dl>

                    <div class="mt-2">
                        <label for="notaPrezzo" class="sr-only">Motivo della correzione</label>
                        <input id="notaPrezzo" wire:model="notaPrezzo" class="input py-2 text-sm"
                               placeholder="Motivo della correzione (facoltativo)">
                    </div>

                    <button type="button" wire:click="aggiornaPrezzo" class="btn-ghost mt-2 w-full"
                            @disabled((float) $prezzoVendita === (float) $opportunity->sale_price_gross)>
                        Aggiorna prezzo
                    </button>

                    <p class="help">
                        Il Buyer viene avvisato e la correzione resta nell'audit log.
                        Dopo la pubblicazione il prezzo non è più modificabile da qui.
                    </p>
                </div>
            @endcan

            @error('verifica') <p class="error" role="alert"><span aria-hidden="true">⚠</span>{{ $message }}</p> @enderror

            <div class="mt-4">
                <label for="note" class="label">Note di verifica (facoltative)</label>
                <textarea id="note" rows="2" wire:model="note" class="input py-2"></textarea>
            </div>

            <div class="mt-4 space-y-2">
                <button type="button" wire:click="approva" class="btn-primary w-full">Approva e pubblica</button>
                <button type="button" wire:click="$toggle('rifiutoAperto')" class="btn-ghost w-full">Richiedi correzioni</button>
            </div>

            @if ($rifiutoAperto)
                <div class="mt-3 rounded-lg border border-rose-300 bg-rose-50 p-3">
                    <label for="motivazioneRifiuto" class="label">Motivazione del rifiuto *</label>
                    <textarea id="motivazioneRifiuto" rows="3" wire:model="motivazioneRifiuto" class="input py-2"></textarea>
                    <button type="button" wire:click="respingi" class="btn-danger mt-2 w-full">Rimanda al Buyer</button>
                </div>
            @endif
        </div>
    </aside>
</div>
