<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h2 class="text-lg font-bold text-slate-900">Panoramica</h2>
        <a href="{{ route('buyer.opportunita.create') }}" class="btn-primary">＋ Nuova opportunità</a>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @foreach ([
            ['Bozze', $conteggi[\App\Enums\OpportunityStatus::BOZZA->value] ?? 0],
            ['In verifica', $conteggi[\App\Enums\OpportunityStatus::IN_VERIFICA->value] ?? 0],
            ['Aperte', $conteggi[\App\Enums\OpportunityStatus::APERTA->value] ?? 0],
            ['In scadenza (6h)', $inScadenza->count()],
        ] as [$etichetta, $valore])
            <div class="card p-4">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $etichetta }}</p>
                <p class="mt-1 text-3xl font-bold text-slate-900">{{ $valore }}</p>
            </div>
        @endforeach
    </div>

    <div class="grid gap-4 sm:grid-cols-3">
        <div class="card p-4">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Colli ordinati (aperte)</p>
            <p class="mt-1 text-2xl font-bold text-slate-900">{{ $colliTotali }}</p>
        </div>
        <div class="card p-4">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Kg ordinati (aperte)</p>
            <p class="mt-1 text-2xl font-bold text-slate-900">{{ \App\Support\Format::decimal($kgTotali, 2) }}</p>
        </div>
        <div class="card p-4">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Tasso di risposta</p>
            <p class="mt-1 text-2xl font-bold text-slate-900">{{ \App\Support\Format::percent($tassoRisposta) }}</p>
        </div>
    </div>

    @if ($limitate->isNotEmpty())
        <section class="card p-5">
            <h3 class="text-sm font-bold uppercase tracking-wide text-slate-500">Disponibilità residue</h3>
            <ul class="mt-3 divide-y divide-slate-100">
                @foreach ($limitate as $o)
                    <li class="flex flex-wrap items-center justify-between gap-2 py-2.5 text-sm">
                        <a href="{{ route('opportunita.show', $o) }}" class="font-semibold text-mare-700 hover:underline">
                            {{ $o->reference }} — {{ $o->description }}
                        </a>
                        <span class="text-slate-600">
                            {{ $o->committed_packages }}/{{ $o->total_packages }} impegnati ·
                            <strong class="{{ $o->isSoldOut() ? 'text-rose-700' : 'text-emerald-700' }}">
                                {{ $o->isSoldOut() ? 'Esaurito' : $o->remainingPackages().' residui' }}
                            </strong>
                        </span>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif

    <section class="card overflow-hidden">
        <div class="flex items-center justify-between border-b border-slate-200 px-5 py-3">
            <h3 class="text-sm font-bold uppercase tracking-wide text-slate-500">Opportunità recenti</h3>
            <a href="{{ route('opportunita.index') }}" class="text-sm font-semibold text-laguna-600 underline">Vedi tutte</a>
        </div>

        @if ($recenti->isEmpty())
            <x-vuoto titolo="Nessuna opportunità" descrizione="Crea la prima opportunità per i reparti pescheria.">
                <a href="{{ route('buyer.opportunita.create') }}" class="btn-primary">Nuova opportunità</a>
            </x-vuoto>
        @else
            <div class="overflow-x-auto">
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
                                <td class="td text-right">
                                    <a href="{{ route('opportunita.show', $o) }}" class="font-semibold text-laguna-600 underline">Apri</a>
                                    @if ($o->status->isEditableByBuyer())
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
