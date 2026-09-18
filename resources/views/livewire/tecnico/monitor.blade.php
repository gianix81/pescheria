<div class="space-y-5">
    <div class="card flex flex-wrap items-end gap-3 p-4">
        <div class="min-w-48 flex-1">
            <label for="ricerca" class="label">Cerca opportunità</label>
            <input id="ricerca" type="search" wire:model.live.debounce.400ms="ricerca" class="input py-2 text-sm">
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
    </div>

    {{-- Legenda: stato comunicato da icona + testo, non dal solo colore --}}
    <div class="card flex flex-wrap gap-3 p-3 text-xs">
        @foreach (\App\Enums\ResponseStatus::cases() as $s)
            <x-badge-risposta :stato="$s" />
        @endforeach
    </div>

    @if ($opportunita->isEmpty())
        <x-vuoto titolo="Nessuna opportunità con questi filtri" />
    @else
        {{-- ------------------------------------------------ Matrice (desktop) --}}
        <div class="card hidden overflow-x-auto lg:block">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th scope="col" class="th sticky left-0 z-10 bg-slate-50">Opportunità</th>
                        @foreach ($puntiVendita as $pv)
                            <th scope="col" class="th text-center" title="{{ $pv->name }}">{{ $pv->code }}</th>
                        @endforeach
                        <th scope="col" class="th text-center">Mancanti</th>
                        <th scope="col" class="th"><span class="sr-only">Azioni</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($opportunita as $o)
                        @php
                            $righe = $risposte[$o->id] ?? collect();
                            $stat = $o->completionStats();
                            $destinatari = $o->stores->pluck('id')->all();
                        @endphp
                        <tr class="hover:bg-slate-50">
                            <td class="td sticky left-0 z-10 bg-white">
                                <a href="{{ route('opportunita.show', $o) }}" class="font-semibold text-mare-700 underline">{{ $o->reference }}</a>
                                <span class="block text-xs text-slate-500">{{ $o->description }}</span>
                                <span class="block text-xs text-slate-500">Scade {{ \App\Support\Format::dateTime($o->closes_at) }}</span>
                            </td>

                            @foreach ($puntiVendita as $pv)
                                <td class="td text-center">
                                    @if (! in_array($pv->id, $destinatari, true))
                                        <span class="text-slate-300" aria-label="Non destinatario">·</span>
                                    @else
                                        @php $r = $righe[$pv->id] ?? null; $stato = $r?->status ?? $statoPredefinito; @endphp
                                        <button type="button" wire:click="toggleSelezione({{ $o->id }}, {{ $pv->id }})"
                                                @class([
                                                    'rounded px-1 py-0.5',
                                                    'ring-2 ring-laguna-500' => in_array($pv->id, $selezione[$o->id] ?? [], true),
                                                ])
                                                title="{{ $pv->code }} — {{ $stato->label() }}{{ $r && $r->packages ? ' ('.$r->packages.' colli)' : '' }}">
                                            <x-badge-risposta :stato="$stato" :compatto="true" />
                                            @if ($r && $r->packages > 0)
                                                <span class="block text-[10px] font-semibold text-slate-700">{{ $r->packages }}</span>
                                            @endif
                                        </button>
                                    @endif
                                </td>
                            @endforeach

                            <td class="td text-center font-semibold {{ $stat['mancanti'] > 0 ? 'text-rose-700' : 'text-emerald-700' }}">
                                {{ $stat['mancanti'] }}
                            </td>
                            <td class="td whitespace-nowrap text-right">
                                <button type="button" wire:click="selezionaTuttiMancanti({{ $o->id }})" class="text-xs font-semibold text-laguna-600 underline">
                                    Seleziona mancanti
                                </button>
                                <button type="button" wire:click="sollecitaSelezionati({{ $o->id }})" class="ml-2 text-xs font-semibold text-laguna-600 underline">
                                    Sollecita
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- ------------------------------------------------ Lista raggruppata (mobile) --}}
        <div class="space-y-4 lg:hidden">
            @foreach ($opportunita as $o)
                @php $righe = $risposte[$o->id] ?? collect(); $stat = $o->completionStats(); @endphp
                <section class="card p-4">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <a href="{{ route('opportunita.show', $o) }}" class="font-semibold text-mare-700 underline">{{ $o->reference }}</a>
                            <p class="text-xs text-slate-500">{{ $o->description }}</p>
                        </div>
                        <x-badge-stato :stato="$o->status" />
                    </div>

                    <p class="mt-2 text-xs text-slate-600">
                        {{ $stat['inviate'] }}/{{ $stat['destinatari'] }} risposte ·
                        <strong class="{{ $stat['mancanti'] > 0 ? 'text-rose-700' : 'text-emerald-700' }}">{{ $stat['mancanti'] }} mancanti</strong>
                    </p>

                    <ul class="mt-3 divide-y divide-slate-100 text-sm">
                        @foreach ($o->stores as $pv)
                            @php $r = $righe[$pv->id] ?? null; $stato = $r?->status ?? $statoPredefinito; @endphp
                            <li class="flex items-center justify-between gap-2 py-2">
                                <span class="font-medium text-slate-800">{{ $pv->code }}</span>
                                <span class="flex items-center gap-2">
                                    @if ($r && $r->packages > 0)<span class="text-xs text-slate-600">{{ $r->packages }} colli</span>@endif
                                    <x-badge-risposta :stato="$stato" />
                                </span>
                            </li>
                        @endforeach
                    </ul>

                    <button type="button" wire:click="sollecitaSelezionati({{ $o->id }})" class="btn-secondary mt-3 w-full">
                        Sollecita mancanti
                    </button>
                </section>
            @endforeach
        </div>
    @endif
</div>
