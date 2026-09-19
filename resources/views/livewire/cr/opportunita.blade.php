<div class="space-y-4">

    {{-- Barra di filtro compatta: i conteggi stanno qui, non in riquadri dedicati --}}
    <div class="flex flex-wrap items-center gap-2" role="tablist" aria-label="Filtro opportunità">
        @foreach ([
            'da_completare' => ['Da rispondere', $conteggi['da_completare']],
            'bozze' => ['Bozze', $conteggi['bozze']],
            'inviate' => ['Inviate', $conteggi['inviate']],
            'storico' => ['Storico', null],
        ] as $chiave => [$etichetta, $quanti])
            <button type="button" wire:click="aggiornaVista('{{ $chiave }}')" role="tab"
                    aria-selected="{{ $vista === $chiave ? 'true' : 'false' }}"
                    @class([
                        'btn gap-1.5 px-3 py-2 text-sm',
                        'bg-mare-700 text-white' => $vista === $chiave,
                        'border border-slate-300 bg-white text-slate-700' => $vista !== $chiave,
                    ])>
                {{ $etichetta }}
                @if ($quanti)
                    <span @class([
                        'rounded-full px-1.5 text-xs font-bold',
                        'bg-white/20 text-white' => $vista === $chiave,
                        'bg-slate-200 text-slate-700' => $vista !== $chiave,
                    ])>{{ $quanti }}</span>
                @endif
            </button>
        @endforeach

        <div class="ml-auto flex flex-wrap gap-2">
            <label for="ricerca" class="sr-only">Cerca</label>
            <input id="ricerca" type="search" wire:model.live.debounce.400ms="ricerca"
                   placeholder="Cerca articolo o PLU" class="input w-48 py-2 text-sm sm:w-56">
            <label for="categoria" class="sr-only">Categoria</label>
            <select id="categoria" wire:model.live="categoria" class="input w-40 py-2 text-sm">
                <option value="">Tutte</option>
                @foreach ($categorie as $c)
                    <option value="{{ $c }}">{{ $c }}</option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- Una riga sola: che cosa ti aspetta e dove andare. Nessun riquadro riepilogativo. --}}
    @if ($conteggi['da_completare'] > 0)
        <x-obiettivo
            :titolo="$conteggi['da_completare'].' '.($conteggi['da_completare'] === 1 ? 'opportunità aspetta la tua risposta' : 'opportunità aspettano la tua risposta').($prossimaScadenza ? ' — la prima scade il '.\App\Support\Format::dateTime($prossimaScadenza) : '')"
            :urgente="true"
            :azione="$vista === 'da_completare' ? null : 'Vedi'"
            :url-azione="$vista === 'da_completare' ? null : route('cr.opportunita.index', ['vista' => 'da_completare'])" />
    @endif

    {{-- Skeleton durante i caricamenti --}}
    <div wire:loading.delay class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @for ($i = 0; $i < 3; $i++)
            <div class="card space-y-3 p-4">
                <div class="skeleton h-32 w-full sm:h-40"></div>
                <div class="skeleton h-5 w-3/4"></div>
                <div class="skeleton h-4 w-1/2"></div>
            </div>
        @endfor
    </div>

    <div wire:loading.remove.delay>
        @if ($opportunita->isEmpty())
            <x-vuoto
                :titolo="match ($vista) {
                    'da_completare' => 'Nessuna opportunità in attesa: sei in pari',
                    'bozze' => 'Nessuna bozza salvata',
                    'inviate' => 'Non hai ancora inviato risposte',
                    default => 'Nessuna opportunità nello storico',
                }"
                descrizione="Quando il Buyer pubblica una nuova opportunità per il tuo punto vendita la trovi qui, con foto o video, prezzo e scadenza."
                icona="🐟" />
        @else
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($opportunita as $o)
                    @php
                        $risposta = $risposte[$o->id] ?? null;
                        $anteprima = $o->media->first();
                        $totale = $ordinato[$o->id] ?? null;
                        $urgente = $o->status === \App\Enums\OpportunityStatus::APERTA
                            && $o->closes_at->isFuture()
                            && $o->closes_at->diffInHours(now(), true) <= 3;
                    @endphp

                    <article @class([
                        'card flex flex-col overflow-hidden transition',
                        'ring-2 ring-amber-400' => $urgente && ! $risposta?->isSubmitted(),
                    ])>
                        {{-- L'anteprima occupa spazio: è la merce, deve vedersi --}}
                        <a href="{{ route('cr.opportunita.show', $o) }}" class="block">
                            <div class="relative flex h-32 items-center justify-center bg-slate-100 sm:h-44">
                                @if ($anteprima && ! $anteprima->esiste())
                                    <span class="text-sm text-slate-500">Immagine non disponibile</span>
                                @elseif ($anteprima && ! $anteprima->isVideo())
                                    <img src="{{ $anteprima->temporaryUrl() }}" alt="" class="h-32 w-full object-cover sm:h-44">
                                @elseif ($anteprima)
                                    <span class="text-4xl" aria-hidden="true">▶</span>
                                    <span class="sr-only">Video disponibile</span>
                                @else
                                    <span class="text-sm text-slate-500">Nessuna immagine</span>
                                @endif

                                <div class="absolute left-2 top-2 flex flex-wrap gap-1">
                                    @if ($o->status === \App\Enums\OpportunityStatus::APERTA && $o->closes_at->isFuture())
                                        <x-countdown :scadenza="$o->closes_at" etichetta="" class="shadow-sm" />
                                    @else
                                        <x-badge-stato :stato="$o->status" class="shadow-sm" />
                                    @endif
                                </div>

                                @if ($risposta)
                                    <div class="absolute right-2 top-2">
                                        <x-badge-risposta :stato="$risposta->status" class="shadow-sm" />
                                    </div>
                                @endif

                                @if ($o->isSoldOut())
                                    <div class="absolute inset-x-0 bottom-0 bg-rose-700/90 px-2 py-1 text-center text-xs font-bold text-white">
                                        Esaurito
                                    </div>
                                @endif
                            </div>
                        </a>

                        <div class="flex flex-1 flex-col gap-1.5 p-3 sm:gap-2 sm:p-4">
                            <div>
                                <h3 class="text-base font-bold leading-tight text-slate-900">
                                    <a href="{{ route('cr.opportunita.show', $o) }}" class="hover:underline">{{ $o->title }}</a>
                                </h3>
                                <p class="mt-0.5 text-xs text-slate-500">
                                    {{ $o->article_code }} · PLU {{ $o->plu ?: '—' }}
                                    @if ($o->origin) · {{ $o->origin }} @endif
                                </p>
                            </div>

                            <dl class="grid grid-cols-2 gap-x-3 gap-y-1.5 text-sm">
                                <div>
                                    <dt class="text-xs text-slate-500">Vendita</dt>
                                    <dd class="font-bold text-slate-900">{{ \App\Support\Format::money($o->sale_price_gross) }}/kg</dd>
                                </div>
                                <div>
                                    <dt class="text-xs text-slate-500">Peso collo</dt>
                                    <dd class="font-bold text-slate-900">{{ \App\Support\Format::kg($o->kg_per_package, 1) }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs text-slate-500">Disponibilità</dt>
                                    <dd class="font-semibold text-slate-800">
                                        {{ $o->isLimited() ? $o->remainingPackages().' colli' : 'Illimitati' }}
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-xs text-slate-500">Consegna</dt>
                                    <dd class="font-semibold text-slate-800">{{ \App\Support\Format::date($o->delivery_date) }}</dd>
                                </div>
                            </dl>

                            <p class="rounded-lg bg-slate-50 px-2 py-1.5 text-xs text-slate-700">
                                @if ($totale && $totale->colli > 0)
                                    <span aria-hidden="true">📦</span>
                                    Già ordinati <strong>{{ $totale->colli }} colli</strong>
                                    da {{ $totale->punti_vendita }} {{ $totale->punti_vendita === 1 ? 'punto vendita' : 'punti vendita' }}
                                @else
                                    <span aria-hidden="true">📦</span> Nessun ordine ancora: sei il primo
                                @endif
                            </p>

                            @if ($risposta?->isSubmitted() && $risposta->packages > 0)
                                <p class="text-sm font-semibold text-emerald-800">
                                    <span aria-hidden="true">✓</span>
                                    Hai ordinato {{ $risposta->packages }} colli ({{ \App\Support\Format::kg($risposta->kg, 1) }})
                                </p>
                            @endif

                            <a href="{{ route('cr.opportunita.show', $o) }}"
                               @class(['mt-auto w-full', 'btn-primary' => ! $risposta?->isSubmitted(), 'btn-ghost' => $risposta?->isSubmitted()])>
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
