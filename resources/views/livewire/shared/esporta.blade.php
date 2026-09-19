<div class="space-y-5">
    <div class="card p-5">
        <h2 class="text-lg font-bold text-slate-900">Export risultati</h2>
        <p class="mt-1 text-sm text-slate-600">
            I file rispettano esattamente i filtri impostati qui. Ogni export viene registrato nell'audit log.
        </p>

        <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <div>
                <label for="opportunity_id" class="label">Singola opportunità</label>
                <select id="opportunity_id" wire:model.live="opportunity_id" class="input py-2 text-sm">
                    <option value="">Tutte</option>
                    @foreach ($opportunitaElenco as $o)
                        <option value="{{ $o->id }}">{{ $o->reference }} — {{ $o->description }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="delivery_from" class="label">Consegna dal</label>
                <input id="delivery_from" type="date" wire:model.live="delivery_from" class="input py-2 text-sm">
            </div>

            <div>
                <label for="delivery_to" class="label">Consegna al</label>
                <input id="delivery_to" type="date" wire:model.live="delivery_to" class="input py-2 text-sm">
            </div>

            <div>
                <label for="store_id" class="label">Punto vendita</label>
                <select id="store_id" wire:model.live="store_id" class="input py-2 text-sm">
                    <option value="">Tutti</option>
                    @foreach ($puntiVendita as $pv)
                        <option value="{{ $pv->id }}">{{ $pv->code }} — {{ $pv->name }}</option>
                    @endforeach
                </select>
            </div>

            <fieldset class="lg:col-span-2">
                <legend class="label">Stato opportunità</legend>
                <div class="mt-1 flex flex-wrap gap-2">
                    @foreach ($stati as $s)
                        <label class="flex items-center gap-2 rounded-lg border border-slate-300 px-3 py-1.5 text-sm has-checked:border-mare-700 has-checked:bg-mare-50">
                            <input type="checkbox" value="{{ $s->value }}" wire:model.live="status"
                                   class="rounded border-slate-300 text-mare-700 focus:ring-laguna-500">
                            {{ $s->label() }}
                        </label>
                    @endforeach
                </div>
            </fieldset>
        </div>

        <div class="mt-5 space-y-4 border-t border-slate-200 pt-4">

            {{-- Tracciato del portale del fornitore: è il file che si carica davvero --}}
            <div class="rounded-lg border border-mare-200 bg-mare-50 p-4">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <p class="text-sm font-bold text-mare-800">
                            Assegnazione per portale — è questo il file da caricare
                        </p>
                        <p class="help">
                            Tracciato del fornitore: foglio «DATI» con DATA CONSEGNA, CLIENTE, PRODOTTO, QUANTITA.
                            Una riga per ogni acquisto confermato: {{ $righePortale }}
                            {{ $righePortale === 1 ? 'riga' : 'righe' }} con i filtri attuali.
                        </p>
                    </div>

                    <a href="{{ route('export.portale', $this->filtri()) }}"
                       @class(['btn-primary', 'pointer-events-none opacity-50' => $righePortale === 0])>
                        ⤓ Scarica il file per il portale (XLSX)
                    </a>
                </div>

                @if ($codiciMancanti['punti_vendita'] || $codiciMancanti['prodotti'])
                    <div class="mt-3 rounded-lg border border-rose-300 bg-rose-50 px-3 py-2 text-sm text-rose-900" role="alert">
                        <p class="font-semibold"><span aria-hidden="true">⚠</span> Codici portale mancanti</p>
                        <p class="mt-1">Senza questi codici il portale rifiuta le righe. Compilali nelle anagrafiche.</p>

                        @if ($codiciMancanti['punti_vendita'])
                            <p class="mt-2 font-medium">Punti vendita:</p>
                            <ul class="list-inside list-disc">
                                @foreach ($codiciMancanti['punti_vendita'] as $voce)
                                    <li>{{ $voce }}</li>
                                @endforeach
                            </ul>
                        @endif

                        @if ($codiciMancanti['prodotti'])
                            <p class="mt-2 font-medium">Prodotti:</p>
                            <ul class="list-inside list-disc">
                                @foreach ($codiciMancanti['prodotti'] as $voce)
                                    <li>{{ $voce }}</li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                @endif
            </div>

        <div>
            <p class="text-sm font-semibold text-slate-700">Report interni</p>
            <p class="help">
                Non servono al portale: sono per analisi e archivio, con matrice per punto vendita,
                dettaglio delle risposte e mancanti.
            </p>

            <div class="mt-2 flex flex-wrap gap-3">
                <a href="{{ route('export.xlsx', $this->filtri()) }}" class="btn-ghost">⤓ Report XLSX (3 fogli)</a>
                <a href="{{ route('export.csv', $this->filtri()) }}" class="btn-ghost">⤓ Report CSV</a>
            </div>
            <p class="w-full text-xs text-slate-500">
                CSV in UTF-8 con BOM, separatore «;», date gg/mm/aaaa: si apre correttamente in Excel italiano.
            </p>
        </div>
        </div>
    </div>

    <div class="card overflow-hidden">
        <h3 class="border-b border-slate-200 px-5 py-3 text-sm font-bold uppercase tracking-wide text-slate-500">
            Anteprima ({{ $totale }} opportunità)
        </h3>

        @if ($anteprima->isEmpty())
            <x-vuoto titolo="Nessun dato con questi filtri" descrizione="Allarga l'intervallo di date o rimuovi un filtro." icona="⤓" />
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th scope="col" class="th">Riferimento</th>
                            <th scope="col" class="th">Articolo</th>
                            <th scope="col" class="th">Consegna</th>
                            <th scope="col" class="th">PdV</th>
                            <th scope="col" class="th">Colli</th>
                            <th scope="col" class="th">Kg</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($anteprima as $o)
                            <tr>
                                <td class="td font-semibold">{{ $o->reference }}</td>
                                <td class="td">{{ $o->article_code }} — {{ $o->description }}</td>
                                <td class="td">{{ \App\Support\Format::date($o->delivery_date) }}</td>
                                <td class="td">{{ $o->stores->count() }}</td>
                                <td class="td">{{ $o->totalPackagesOrdered() }}</td>
                                <td class="td">{{ \App\Support\Format::decimal($o->totalKgOrdered(), 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
