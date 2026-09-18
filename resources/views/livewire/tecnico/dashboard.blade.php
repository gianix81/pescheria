<div class="space-y-6">
    <div class="grid gap-4 sm:grid-cols-4">
        @foreach ([
            ['Da verificare', $daVerificare->count()],
            ['Scadono oggi', $inScadenzaOggi->count()],
            ['PdV mancanti', $mancantiTotali],
            ['Anomalie', $anomalie->count()],
        ] as [$etichetta, $valore])
            <div class="card p-4">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $etichetta }}</p>
                <p class="mt-1 text-3xl font-bold text-slate-900">{{ $valore }}</p>
            </div>
        @endforeach
    </div>

    <section class="card overflow-hidden">
        <h3 class="border-b border-slate-200 px-5 py-3 text-sm font-bold uppercase tracking-wide text-slate-500">
            Opportunità da verificare
        </h3>
        @if ($daVerificare->isEmpty())
            <x-vuoto titolo="Nessuna opportunità in attesa" descrizione="Appena il Buyer invia una scheda la trovi qui." icona="✓" />
        @else
            <ul class="divide-y divide-slate-100">
                @foreach ($daVerificare as $o)
                    <li class="flex flex-wrap items-center justify-between gap-3 px-5 py-3">
                        <div>
                            <p class="font-semibold text-slate-900">{{ $o->reference }} — {{ $o->title }}</p>
                            <p class="text-xs text-slate-500">
                                {{ $o->article_code }} · inviata {{ \App\Support\Format::dateTime($o->submitted_at) }}
                                da {{ $o->creator?->full_name }}
                            </p>
                        </div>
                        <a href="{{ route('tecnico.verifica', $o) }}" class="btn-primary">Verifica</a>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>

    <section class="card overflow-hidden">
        <div class="flex items-center justify-between border-b border-slate-200 px-5 py-3">
            <h3 class="text-sm font-bold uppercase tracking-wide text-slate-500">Monitoraggio compilazioni</h3>
            <a href="{{ route('tecnico.monitor') }}" class="text-sm font-semibold text-laguna-600 underline">Matrice completa</a>
        </div>

        @if ($monitor->isEmpty())
            <x-vuoto titolo="Nessuna opportunità aperta" />
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th scope="col" class="th">Articolo</th>
                            <th scope="col" class="th">Scadenza</th>
                            <th scope="col" class="th">Destinatari</th>
                            <th scope="col" class="th">Risposte</th>
                            <th scope="col" class="th">Mancanti</th>
                            <th scope="col" class="th">Completamento</th>
                            <th scope="col" class="th">Stato</th>
                            <th scope="col" class="th"><span class="sr-only">Azioni</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($monitor as $riga)
                            @php $o = $riga['opportunita']; $s = $riga['stat']; @endphp
                            <tr class="hover:bg-slate-50">
                                <td class="td">
                                    <span class="block font-medium text-slate-900">{{ $o->description }}</span>
                                    <span class="block text-xs text-slate-500">{{ $o->reference }} · {{ $o->article_code }}</span>
                                </td>
                                <td class="td">{{ \App\Support\Format::dateTime($o->closes_at) }}</td>
                                <td class="td">{{ $s['destinatari'] }}</td>
                                <td class="td">{{ $s['inviate'] }}</td>
                                <td class="td">
                                    <span @class(['font-semibold', 'text-rose-700' => $s['mancanti'] > 0])>{{ $s['mancanti'] }}</span>
                                </td>
                                <td class="td">
                                    <div class="flex items-center gap-2">
                                        <div class="h-2 w-24 overflow-hidden rounded bg-slate-200">
                                            <div class="h-full bg-laguna-500" style="width: {{ $s['percentuale'] }}%"></div>
                                        </div>
                                        <span class="text-xs">{{ \App\Support\Format::percent($s['percentuale']) }}</span>
                                    </div>
                                </td>
                                <td class="td"><x-badge-stato :stato="$o->status" /></td>
                                <td class="td text-right whitespace-nowrap">
                                    <button type="button" wire:click="sollecita({{ $o->id }})" class="font-semibold text-laguna-600 underline">Sollecita</button>
                                    <a href="{{ route('opportunita.show', $o) }}" class="ml-2 font-semibold text-laguna-600 underline">Dettaglio</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>

    @if ($anomalie->isNotEmpty())
        <section class="card p-5">
            <h3 class="text-sm font-bold uppercase tracking-wide text-slate-500">Anomalie di compilazione</h3>
            <ul class="mt-3 space-y-2 text-sm">
                @foreach ($anomalie as $riga)
                    <li class="flex flex-wrap items-center justify-between gap-2 rounded-lg bg-amber-50 px-3 py-2 text-amber-900">
                        <span>
                            <strong>{{ $riga['opportunita']->reference }}</strong> —
                            @if ($riga['stat']['bozze'] > 0)
                                {{ $riga['stat']['bozze'] }} bozze non inviate
                            @endif
                            @if ($riga['opportunita']->status === \App\Enums\OpportunityStatus::SCADUTA && $riga['stat']['mancanti'] > 0)
                                {{ $riga['stat']['mancanti'] }} punti vendita scaduti senza risposta
                            @endif
                        </span>
                        <a href="{{ route('opportunita.show', $riga['opportunita']) }}" class="font-semibold underline">Apri</a>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif
</div>
