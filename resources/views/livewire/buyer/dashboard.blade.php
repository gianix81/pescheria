@php
    $bozze = $conteggi[\App\Enums\OpportunityStatus::BOZZA->value] ?? 0;
    $inVerifica = $conteggi[\App\Enums\OpportunityStatus::IN_VERIFICA->value] ?? 0;
    $daCorreggere = $conteggi[\App\Enums\OpportunityStatus::DA_CORREGGERE->value] ?? 0;
    $aperte = $conteggi[\App\Enums\OpportunityStatus::APERTA->value] ?? 0;

    // Una sola frase, in ordine di urgenza: prima ciò che blocca.
    [$frase, $urgente] = match (true) {
        $daCorreggere > 0 => [$daCorreggere.' '.($daCorreggere === 1 ? 'opportunità respinta dal Tecnico: correggila e rimandala.' : 'opportunità respinte dal Tecnico: correggile e rimandale.'), true],
        $inScadenza->isNotEmpty() => [$inScadenza->count().' '.($inScadenza->count() === 1 ? 'opportunità scade' : 'opportunità scadono').' entro 6 ore: controlla chi manca.', true],
        $aperte > 0 => [$aperte.' '.($aperte === 1 ? 'opportunità aperta' : 'opportunità aperte').': stanno arrivando le risposte.', false],
        $bozze > 0 => [$bozze.' '.($bozze === 1 ? 'bozza da completare' : 'bozze da completare').' e inviare in verifica.', false],
        default => ['Nessuna opportunità in corso: pubblicane una nuova.', false],
    };
@endphp

<div class="space-y-4">

    <x-obiettivo :titolo="$frase" :urgente="$urgente"
                 azione="＋ Nuova" :url-azione="route('buyer.opportunita.create')" />

    {{-- Conteggi: due per riga su telefono, quattro da tablet in su --}}
    <div class="grid grid-cols-2 gap-2 lg:grid-cols-4">
        <x-riquadro-numero etichetta="Aperte" :valore="$aperte"
                           :url="route('opportunita.index', ['stato' => 'APERTA'])" />
        <x-riquadro-numero etichetta="In verifica" :valore="$inVerifica"
                           :url="route('opportunita.index', ['stato' => 'IN_VERIFICA'])" />
        <x-riquadro-numero etichetta="Da correggere" :valore="$daCorreggere" :evidenzia="$daCorreggere > 0"
                           :url="route('opportunita.index', ['stato' => 'DA_CORREGGERE'])" />
        <x-riquadro-numero etichetta="Bozze" :valore="$bozze"
                           :url="route('opportunita.index', ['stato' => 'BOZZA'])" />
    </div>

    {{-- Risultato di ciò che è aperto: una riga sola --}}
    <div class="card flex flex-wrap items-center justify-between gap-x-6 gap-y-2 px-4 py-3 text-sm">
        <span class="text-slate-600">Ordinato sulle aperte</span>
        <span class="flex flex-wrap items-center gap-x-5 gap-y-1">
            <span><strong class="text-lg text-slate-900">{{ $colliTotali }}</strong> colli</span>
            <span><strong class="text-lg text-slate-900">{{ \App\Support\Format::decimal($kgTotali, 0) }}</strong> kg</span>
            <span><strong class="text-lg text-slate-900">{{ \App\Support\Format::percent($tassoRisposta, 0) }}</strong> risposte</span>
        </span>
    </div>

    @if ($limitate->isNotEmpty())
        <section class="card p-4">
            <h2 class="text-xs font-bold uppercase tracking-wide text-slate-500">Disponibilità residue</h2>
            <ul class="mt-2 divide-y divide-slate-100 text-sm">
                @foreach ($limitate as $o)
                    <li class="flex items-center justify-between gap-3 py-2">
                        <a href="{{ route('opportunita.show', $o) }}" class="min-w-0 flex-1 truncate font-medium text-mare-700">
                            {{ $o->description }}
                        </a>
                        <span @class([
                            'shrink-0 text-xs font-bold',
                            'text-rose-700' => $o->isSoldOut(),
                            'text-emerald-700' => ! $o->isSoldOut(),
                        ])>
                            {{ $o->isSoldOut() ? 'Esaurito' : $o->remainingPackages().' colli' }}
                        </span>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif

    <section class="card overflow-hidden">
        <div class="flex items-center justify-between px-4 py-3">
            <h2 class="text-xs font-bold uppercase tracking-wide text-slate-500">Ultime opportunità</h2>
            <a href="{{ route('opportunita.index') }}" class="text-xs font-semibold text-laguna-600 underline">Tutte</a>
        </div>

        @if ($recenti->isEmpty())
            <x-vuoto titolo="Nessuna opportunità" descrizione="Crea la prima per i reparti pescheria." icona="🐟">
                <a href="{{ route('buyer.opportunita.create') }}" class="btn-primary">Nuova opportunità</a>
            </x-vuoto>
        @else
            {{-- Telefono: lista compatta. Da tablet: tabella --}}
            <ul class="divide-y divide-slate-100 lg:hidden">
                @foreach ($recenti as $o)
                    <li>
                        <a href="{{ route('opportunita.show', $o) }}" class="flex items-center justify-between gap-3 px-4 py-2.5">
                            <span class="min-w-0">
                                <span class="block truncate text-sm font-semibold text-slate-900">{{ $o->description }}</span>
                                <span class="block truncate text-xs text-slate-500">
                                    {{ $o->reference }} · consegna {{ \App\Support\Format::date($o->delivery_date) }}
                                </span>
                            </span>
                            <x-badge-stato :stato="$o->status" class="shrink-0" />
                        </a>
                    </li>
                @endforeach
            </ul>

            <div class="hidden overflow-x-auto lg:block">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th scope="col" class="th">Riferimento</th>
                            <th scope="col" class="th">Articolo</th>
                            <th scope="col" class="th">Stato</th>
                            <th scope="col" class="th">Scadenza</th>
                            <th scope="col" class="th">Consegna</th>
                            <th scope="col" class="th"><span class="sr-only">Azioni</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($recenti as $o)
                            <tr class="hover:bg-slate-50">
                                <td class="td font-semibold">{{ $o->reference }}</td>
                                <td class="td">{{ $o->article_code }} — {{ $o->description }}</td>
                                <td class="td"><x-badge-stato :stato="$o->status" /></td>
                                <td class="td">{{ \App\Support\Format::dateTime($o->closes_at) }}</td>
                                <td class="td">{{ \App\Support\Format::date($o->delivery_date) }}</td>
                                <td class="td text-right whitespace-nowrap">
                                    <a href="{{ route('opportunita.show', $o) }}" class="font-semibold text-laguna-600 underline">Apri</a>
                                    @if ($o->status->isModificabile())
                                        <a href="{{ route('buyer.opportunita.edit', $o) }}" class="ml-2 font-semibold text-laguna-600 underline">Modifica</a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
</div>
