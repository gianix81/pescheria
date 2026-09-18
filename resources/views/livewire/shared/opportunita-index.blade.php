<div class="space-y-5">
    <div class="card flex flex-wrap items-end gap-3 p-4">
        <div class="min-w-48 flex-1">
            <label for="ricerca" class="label">Cerca</label>
            <input id="ricerca" type="search" wire:model.live.debounce.400ms="ricerca" class="input py-2 text-sm"
                   placeholder="Riferimento, codice, PLU o descrizione">
        </div>
        <div>
            <label for="stato" class="label">Stato</label>
            <select id="stato" wire:model.live="stato" class="input py-2 text-sm">
                <option value="">Tutti</option>
                @foreach ($stati as $s)
                    <option value="{{ $s->value }}">{{ $s->label() }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="consegna" class="label">Consegna</label>
            <input id="consegna" type="date" wire:model.live="consegna" class="input py-2 text-sm">
        </div>
        <a href="{{ route('export.index') }}" class="btn-ghost">⤓ Export</a>
    </div>

    @if ($opportunita->isEmpty())
        <x-vuoto titolo="Nessuna opportunità trovata"
                 descrizione="Modifica i filtri oppure crea una nuova opportunità." />
    @else
        <div class="card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="sticky top-0 bg-slate-50">
                        <tr>
                            <th scope="col" class="th">Riferimento</th>
                            <th scope="col" class="th">Articolo</th>
                            <th scope="col" class="th">Stato</th>
                            <th scope="col" class="th">Disponibilità</th>
                            <th scope="col" class="th">Scadenza</th>
                            <th scope="col" class="th">Consegna</th>
                            <th scope="col" class="th">PdV</th>
                            <th scope="col" class="th"><span class="sr-only">Azioni</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($opportunita as $o)
                            <tr class="hover:bg-slate-50">
                                <td class="td font-semibold">{{ $o->reference }}</td>
                                <td class="td">
                                    <span class="block font-medium text-slate-900">{{ $o->description }}</span>
                                    <span class="block text-xs text-slate-500">{{ $o->article_code }} · PLU {{ $o->plu ?: '—' }}</span>
                                </td>
                                <td class="td"><x-badge-stato :stato="$o->status" /></td>
                                <td class="td">
                                    @if ($o->isLimited())
                                        {{ $o->remainingPackages() }}/{{ $o->total_packages }} colli
                                    @else
                                        Illimitati
                                    @endif
                                </td>
                                <td class="td">{{ \App\Support\Format::dateTime($o->closes_at) }}</td>
                                <td class="td">{{ \App\Support\Format::date($o->delivery_date) }}</td>
                                <td class="td">{{ $o->stores_count }}</td>
                                <td class="td text-right whitespace-nowrap">
                                    <a href="{{ route('opportunita.show', $o) }}" class="font-semibold text-laguna-600 underline">Apri</a>
                                    @if (auth()->user()->isTecnico() && $o->status === \App\Enums\OpportunityStatus::IN_VERIFICA)
                                        <a href="{{ route('tecnico.verifica', $o) }}" class="ml-2 font-semibold text-laguna-600 underline">Verifica</a>
                                    @endif
                                    @if (auth()->user()->isBuyer() && $o->status->isEditableByBuyer())
                                        <a href="{{ route('buyer.opportunita.edit', $o) }}" class="ml-2 font-semibold text-laguna-600 underline">Modifica</a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div>{{ $opportunita->links() }}</div>
    @endif
</div>
