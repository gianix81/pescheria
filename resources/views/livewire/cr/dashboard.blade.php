<div class="mx-auto max-w-6xl space-y-6">

    {{-- Card "Da completare": prima cosa che il CR deve vedere --}}
    <div class="grid gap-4 sm:grid-cols-3">
        <div class="card border-l-4 border-l-amber-500 p-4">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Da completare</p>
            <p class="mt-1 text-3xl font-bold text-slate-900">{{ $conteggi['da_completare'] }}</p>
            <p class="mt-1 text-sm text-slate-600">
                @if ($prossimaScadenza)
                    Prima scadenza: {{ \App\Support\Format::dateTime($prossimaScadenza) }}
                @else
                    Nessuna scadenza in corso
                @endif
            </p>
        </div>
        <div class="card p-4">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Bozze</p>
            <p class="mt-1 text-3xl font-bold text-slate-900">{{ $conteggi['bozze'] }}</p>
            <p class="mt-1 text-sm text-slate-600">Salvate ma non inviate</p>
        </div>
        <div class="card p-4">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Inviate</p>
            <p class="mt-1 text-3xl font-bold text-slate-900">{{ $conteggi['inviate'] }}</p>
            <p class="mt-1 text-sm text-slate-600">Risposte definitive</p>
        </div>
    </div>

    {{-- Separazione netta fra le viste --}}
    <div class="flex flex-wrap items-center gap-2" role="tablist" aria-label="Filtro opportunità">
        @foreach ([
            'da_completare' => 'Da rispondere',
            'bozze' => 'Bozze',
            'inviate' => 'Inviate',
            'storico' => 'Storico',
        ] as $chiave => $etichetta)
            <button type="button" wire:click="aggiornaVista('{{ $chiave }}')" role="tab"
                    aria-selected="{{ $vista === $chiave ? 'true' : 'false' }}"
                    @class([
                        'btn px-3 py-2 text-sm',
                        'bg-mare-700 text-white' => $vista === $chiave,
                        'border border-slate-300 bg-white text-slate-700' => $vista !== $chiave,
                    ])>{{ $etichetta }}</button>
        @endforeach

        <div class="ml-auto flex flex-wrap gap-2">
            <label for="ricerca" class="sr-only">Cerca</label>
            <input id="ricerca" type="search" wire:model.live.debounce.400ms="ricerca" placeholder="Cerca articolo o PLU"
                   class="input w-56 py-2 text-sm">
            <label for="categoria" class="sr-only">Categoria</label>
            <select id="categoria" wire:model.live="categoria" class="input w-44 py-2 text-sm">
                <option value="">Tutte le categorie</option>
                @foreach ($categorie as $c)
                    <option value="{{ $c }}">{{ $c }}</option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- Skeleton durante il caricamento --}}
    <div wire:loading.delay class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @for ($i = 0; $i < 3; $i++)
            <div class="card space-y-3 p-4">
                <div class="skeleton h-32 w-full"></div>
                <div class="skeleton h-4 w-3/4"></div>
                <div class="skeleton h-4 w-1/2"></div>
            </div>
        @endfor
    </div>

    <div wire:loading.remove.delay>
        @if ($opportunita->isEmpty())
            <x-vuoto titolo="Nessuna opportunità in questa vista"
                     descrizione="Quando il Buyer pubblica una nuova opportunità per il tuo punto vendita la trovi qui, con foto o video e scadenza." />
        @else
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($opportunita as $o)
                    @php
                        $risposta = $risposte[$o->id] ?? null;
                        $anteprima = $o->media->first();
                    @endphp
                    <article class="card flex flex-col overflow-hidden">
                        <a href="{{ route('cr.opportunita.show', $o) }}" class="block">
                            <div class="flex h-36 items-center justify-center bg-slate-100">
                                @if ($anteprima && ! $anteprima->isVideo())
                                    <img src="{{ $anteprima->temporaryUrl() }}" alt="" class="h-36 w-full object-cover">
                                @elseif ($anteprima)
                                    <span class="text-3xl" aria-hidden="true">▶</span>
                                    <span class="sr-only">Video disponibile</span>
                                @else
                                    <span class="text-sm text-slate-500">Nessuna immagine</span>
                                @endif
                            </div>
                        </a>

                        <div class="flex flex-1 flex-col gap-2 p-4">
                            <div class="flex flex-wrap items-center gap-1.5">
                                <x-badge-stato :stato="$o->status" />
                                @if ($o->status === \App\Enums\OpportunityStatus::APERTA && $o->closes_at->isFuture())
                                    <x-countdown :scadenza="$o->closes_at" />
                                @endif
                                @if ($risposta)
                                    <x-badge-risposta :stato="$risposta->status" />
                                @endif
                            </div>

                            <h3 class="text-sm font-bold text-slate-900">
                                <a href="{{ route('cr.opportunita.show', $o) }}" class="hover:underline">{{ $o->title }}</a>
                            </h3>

                            <p class="text-xs text-slate-600">
                                {{ $o->article_code }} · PLU {{ $o->plu ?: '—' }}
                            </p>

                            <dl class="mt-1 grid grid-cols-2 gap-1 text-xs text-slate-600">
                                <div><dt class="inline font-medium">Vendita:</dt> <dd class="inline">{{ \App\Support\Format::money($o->sale_price_gross) }}/kg</dd></div>
                                <div><dt class="inline font-medium">Collo:</dt> <dd class="inline">{{ \App\Support\Format::kg($o->kg_per_package, 1) }}</dd></div>
                                <div><dt class="inline font-medium">Disp.:</dt> <dd class="inline">{{ $o->isLimited() ? $o->remainingPackages().' colli' : 'Illimitati' }}</dd></div>
                                <div><dt class="inline font-medium">Consegna:</dt> <dd class="inline">{{ \App\Support\Format::date($o->delivery_date) }}</dd></div>
                            </dl>

                            <a href="{{ route('cr.opportunita.show', $o) }}" class="btn-primary mt-auto w-full">
                                {{ $risposta?->isSubmitted() ? 'Vedi o modifica' : 'Rispondi' }}
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="mt-6">{{ $opportunita->links() }}</div>
        @endif
    </div>
</div>
