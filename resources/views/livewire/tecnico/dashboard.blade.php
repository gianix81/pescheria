@php
    $daVerificareN = $daVerificare->count();
    $scadonoOggi = $inScadenzaOggi->count();

    // Il Tecnico è il ponte: prima ciò che tiene ferma un'opportunità, poi chi manca.
    [$frase, $urgente, $azione, $url] = match (true) {
        $daVerificareN > 0 => [
            $daVerificareN.' '.($daVerificareN === 1 ? 'opportunità aspetta la tua verifica' : 'opportunità aspettano la tua verifica').': finché non confermi, i reparti non la vedono.',
            true,
            'Verifica',
            route('tecnico.verifica', $daVerificare->first()),
        ],
        $mancantiTotali > 0 => [
            $mancantiTotali.' '.($mancantiTotali === 1 ? 'punto vendita non ha ancora risposto' : 'punti vendita non hanno ancora risposto').': sollecitali prima della scadenza.',
            $scadonoOggi > 0,
            'Monitor',
            route('tecnico.monitor'),
        ],
        default => ['Tutto in ordine: nessuna verifica in attesa e nessun punto vendita mancante.', false, 'Monitor', route('tecnico.monitor')],
    };
@endphp

<div class="space-y-4">

    <x-obiettivo :titolo="$frase" :urgente="$urgente" :azione="$azione" :url-azione="$url" />

    <div class="grid grid-cols-2 gap-2 lg:grid-cols-4">
        <x-riquadro-numero etichetta="Da verificare" :valore="$daVerificareN" :evidenzia="$daVerificareN > 0"
                           :url="route('opportunita.index', ['preset' => 'da_verificare'])" />
        <x-riquadro-numero etichetta="Scadono oggi" :valore="$scadonoOggi" :evidenzia="$scadonoOggi > 0" />
        <x-riquadro-numero etichetta="PdV mancanti" :valore="$mancantiTotali" :url="route('tecnico.monitor')" />
        <x-riquadro-numero etichetta="Anomalie" :valore="$anomalie->count()" />
    </div>

    {{-- Da verificare: è il lavoro del Tecnico, sta in cima --}}
    @if ($daVerificare->isNotEmpty())
        <section class="card overflow-hidden">
            <h2 class="px-4 py-3 text-xs font-bold uppercase tracking-wide text-slate-500">Da verificare</h2>
            <ul class="divide-y divide-slate-100">
                @foreach ($daVerificare as $o)
                    <li class="flex items-center justify-between gap-3 px-4 py-2.5">
                        <span class="min-w-0">
                            <span class="block truncate text-sm font-semibold text-slate-900">{{ $o->title }}</span>
                            <span class="block truncate text-xs text-slate-500">
                                {{ $o->reference }} · {{ $o->creator?->full_name }}
                                @if ($o->isRipubblicazione()) · <span class="text-amber-800">ripubblicazione</span> @endif
                            </span>
                        </span>
                        <a href="{{ route('tecnico.verifica', $o) }}" class="btn-primary shrink-0 px-3 py-2 text-sm">Verifica</a>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif

    {{-- Monitoraggio: lista su telefono, tabella da tablet --}}
    <section class="card overflow-hidden">
        <div class="flex items-center justify-between px-4 py-3">
            <h2 class="text-xs font-bold uppercase tracking-wide text-slate-500">Compilazioni</h2>
            <a href="{{ route('tecnico.monitor') }}" class="text-xs font-semibold text-laguna-600 underline">Matrice</a>
        </div>

        @if ($monitor->isEmpty())
            <x-vuoto titolo="Nessuna opportunità aperta" icona="✓" />
        @else
            <ul class="divide-y divide-slate-100 lg:hidden">
                @foreach ($monitor as $riga)
                    @php $o = $riga['opportunita']; $s = $riga['stat']; @endphp
                    <li class="px-4 py-2.5">
                        <div class="flex items-start justify-between gap-3">
                            <a href="{{ route('opportunita.show', $o) }}" class="min-w-0">
                                <span class="block truncate text-sm font-semibold text-slate-900">{{ $o->description }}</span>
                                <span class="block truncate text-xs text-slate-500">
                                    scade {{ \App\Support\Format::dateTime($o->closes_at) }}
                                </span>
                            </a>
                            <span @class([
                                'shrink-0 text-sm font-bold',
                                'text-rose-700' => $s['mancanti'] > 0,
                                'text-emerald-700' => $s['mancanti'] === 0,
                            ])>{{ $s['inviate'] }}/{{ $s['destinatari'] }}</span>
                        </div>

                        <div class="mt-1.5 flex items-center gap-2">
                            <div class="h-1.5 flex-1 overflow-hidden rounded bg-slate-200">
                                <div class="h-full bg-laguna-500" style="width: {{ $s['percentuale'] }}%"></div>
                            </div>
                            @if ($s['mancanti'] > 0)
                                <button type="button" wire:click="sollecita({{ $o->id }})"
                                        class="shrink-0 text-xs font-semibold text-laguna-600 underline">Sollecita</button>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>

            <div class="hidden overflow-x-auto lg:block">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th scope="col" class="th">Articolo</th>
                            <th scope="col" class="th">Scadenza</th>
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
                                    <span class="block text-xs text-slate-500">{{ $o->reference }}</span>
                                </td>
                                <td class="td">{{ \App\Support\Format::dateTime($o->closes_at) }}</td>
                                <td class="td">{{ $s['inviate'] }}/{{ $s['destinatari'] }}</td>
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
        <section class="card p-4">
            <h2 class="text-xs font-bold uppercase tracking-wide text-slate-500">Anomalie</h2>
            <ul class="mt-2 space-y-1.5 text-sm">
                @foreach ($anomalie as $riga)
                    <li class="flex flex-wrap items-center justify-between gap-2 rounded-lg bg-amber-50 px-3 py-2 text-amber-900">
                        <span class="min-w-0 truncate">
                            <strong>{{ $riga['opportunita']->reference }}</strong> —
                            @if ($riga['stat']['bozze'] > 0)
                                {{ $riga['stat']['bozze'] }} bozze non inviate
                            @endif
                            @if ($riga['opportunita']->status === \App\Enums\OpportunityStatus::SCADUTA && $riga['stat']['mancanti'] > 0)
                                {{ $riga['stat']['mancanti'] }} scaduti senza risposta
                            @endif
                        </span>
                        <a href="{{ route('opportunita.show', $riga['opportunita']) }}" class="shrink-0 font-semibold underline">Apri</a>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif
</div>
