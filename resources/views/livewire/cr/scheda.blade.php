<div class="mx-auto max-w-6xl pb-32 lg:pb-8">

    {{-- Banner persistenti per stati che bloccano l'azione --}}
    @if ($opportunity->status === \App\Enums\OpportunityStatus::ANNULLATA)
        <div class="mb-4 rounded-lg border border-rose-300 bg-rose-50 px-4 py-3 text-sm text-rose-900" role="alert">
            <span aria-hidden="true">⚠</span> <strong>Opportunità annullata.</strong> {{ $opportunity->cancel_reason }}
        </div>
    @elseif (! $apribile)
        <div class="mb-4 rounded-lg border border-slate-300 bg-slate-100 px-4 py-3 text-sm text-slate-800" role="status">
            <span aria-hidden="true">⏱</span>
            <strong>Risposte chiuse.</strong>
            Termine del {{ \App\Support\Format::dateTime($opportunity->closes_at) }}.
        </div>
    @elseif ($opportunity->isSoldOut())
        <div class="mb-4 rounded-lg border border-rose-300 bg-rose-50 px-4 py-3 text-sm text-rose-900" role="alert">
            <span aria-hidden="true">⊘</span> <strong>Esaurito.</strong> Puoi ancora registrare il rifiuto.
        </div>
    @endif

    <nav class="mb-3 text-sm text-slate-500" aria-label="Percorso">
        <a href="{{ route('cr.dashboard') }}" class="underline underline-offset-2">Opportunità</a>
        <span aria-hidden="true">/</span>
        <span class="text-slate-700">{{ $opportunity->reference }}</span>
    </nav>

    <div class="grid gap-6 lg:grid-cols-2">

        {{-- ---------------------------------------------------- Colonna media --}}
        <div class="card p-4">
            <x-galleria-media :opportunita="$opportunity" />
        </div>

        {{-- ---------------------------------------------------- Colonna dati --}}
        <div class="space-y-4">
            <div class="card p-5">
                <div class="flex flex-wrap items-center gap-2">
                    <x-badge-stato :stato="$opportunity->status" />
                    @if ($apribile)
                        <x-countdown :scadenza="$opportunity->closes_at" />
                    @endif
                    @if ($risposta)
                        <x-badge-risposta :stato="$risposta->status" />
                    @endif
                </div>

                <h2 class="mt-3 text-xl font-bold text-slate-900">{{ $opportunity->title }}</h2>
                <p class="mt-1 text-sm text-slate-600">{{ $opportunity->commercial_description }}</p>

                <dl class="mt-4 grid grid-cols-2 gap-x-4 gap-y-3 text-sm">
                    <div>
                        <dt class="text-xs uppercase tracking-wide text-slate-500">Codice articolo</dt>
                        <dd class="font-semibold text-slate-900">{{ $opportunity->article_code }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase tracking-wide text-slate-500">PLU</dt>
                        <dd class="font-semibold text-slate-900">{{ $opportunity->plu ?: '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase tracking-wide text-slate-500">Kg per collo</dt>
                        <dd class="font-semibold text-slate-900">{{ \App\Support\Format::kg($opportunity->kg_per_package, 2) }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase tracking-wide text-slate-500">Prezzo di vendita</dt>
                        <dd class="font-semibold text-slate-900">{{ \App\Support\Format::money($opportunity->sale_price_gross) }}/kg</dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase tracking-wide text-slate-500">Disponibilità</dt>
                        <dd class="font-semibold text-slate-900">
                            @if ($opportunity->isLimited())
                                {{ $residui }} colli residui su {{ $opportunity->total_packages }}
                            @else
                                Colli illimitati
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase tracking-wide text-slate-500">Consegna</dt>
                        <dd class="font-semibold text-slate-900">{{ \App\Support\Format::date($opportunity->delivery_date) }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase tracking-wide text-slate-500">Lotto minimo</dt>
                        <dd class="font-semibold text-slate-900">{{ $opportunity->min_lot }} colli (multipli di {{ $opportunity->order_multiple }})</dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase tracking-wide text-slate-500">Origine</dt>
                        <dd class="font-semibold text-slate-900">{{ $opportunity->origin ?: '—' }}</dd>
                    </div>
                </dl>

                @if ($opportunity->technical_notes || $opportunity->logistics_notes)
                    <div class="mt-4 space-y-1 border-t border-slate-200 pt-3 text-sm text-slate-600">
                        @if ($opportunity->technical_notes)<p><strong>Note tecniche:</strong> {{ $opportunity->technical_notes }}</p>@endif
                        @if ($opportunity->logistics_notes)<p><strong>Logistica:</strong> {{ $opportunity->logistics_notes }}</p>@endif
                    </div>
                @endif
            </div>

            {{-- ------------------------------------------------ Box decisione --}}
            <div class="card border-2 border-mare-700/15 p-5" id="decisione">
                <h3 class="text-base font-bold text-slate-900">La tua decisione</h3>

                @if ($ricevuta)
                    <div class="mt-3 rounded-lg border border-emerald-300 bg-emerald-50 px-4 py-3 text-sm text-emerald-900" role="status">
                        <span aria-hidden="true">✓</span> {{ $ricevuta }}
                        @if ($apribile)
                            <p class="mt-1 text-emerald-800">Puoi modificare la risposta fino alla scadenza.</p>
                        @endif
                    </div>
                @elseif ($risposta?->isSubmitted())
                    <div class="mt-3 rounded-lg border border-emerald-300 bg-emerald-50 px-4 py-3 text-sm text-emerald-900" role="status">
                        <span aria-hidden="true">✓</span>
                        {{ $risposta->status->label() }} il {{ \App\Support\Format::dateTime($risposta->submitted_at) }}
                        @if ($risposta->packages > 0)
                            — {{ $risposta->packages }} colli ({{ \App\Support\Format::kg($risposta->kg, 2) }})
                        @endif
                    </div>
                @endif

                @error('invio')
                    <p class="error mt-3" role="alert"><span aria-hidden="true">⚠</span>{{ $message }}</p>
                @enderror

                @if ($apribile)
                    <div class="mt-4 grid grid-cols-2 gap-3">
                        <button type="button" wire:click="$set('decisione', 'acquista')"
                                @disabled($opportunity->isSoldOut())
                                @class([
                                    'btn',
                                    'bg-mare-700 text-white' => $decisione === 'acquista',
                                    'border border-slate-300 bg-white text-slate-700' => $decisione !== 'acquista',
                                ])
                                aria-pressed="{{ $decisione === 'acquista' ? 'true' : 'false' }}">
                            Acquista
                        </button>
                        <button type="button" wire:click="scegliColli(0)"
                                @class([
                                    'btn',
                                    'bg-slate-800 text-white' => $decisione === 'non_acquista',
                                    'border border-slate-300 bg-white text-slate-700' => $decisione !== 'non_acquista',
                                ])
                                aria-pressed="{{ $decisione === 'non_acquista' ? 'true' : 'false' }}">
                            Non acquista
                        </button>
                    </div>

                    @error('decisione') <p class="error"><span aria-hidden="true">⚠</span>{{ $message }}</p> @enderror

                    @if ($decisione === 'acquista')
                        <fieldset class="mt-5">
                            <legend class="label">Quanti colli?</legend>

                            <div class="mt-2 grid grid-cols-3 gap-2 sm:grid-cols-6">
                                @foreach ($opportunity->quickQuantities() as $q)
                                    <button type="button" wire:click="scegliColli({{ $q }})"
                                            @class([
                                                'btn text-base',
                                                'bg-laguna-500 text-white' => (int) $colli === (int) $q,
                                                'border border-slate-300 bg-white text-slate-800' => (int) $colli !== (int) $q,
                                            ])>{{ $q }}</button>
                                @endforeach
                            </div>

                            <div class="mt-3 flex items-center gap-2">
                                <button type="button" wire:click="decrementa" class="btn-ghost size-11 px-0" aria-label="Diminuisci colli">−</button>
                                <label for="colli" class="sr-only">Altra quantità in colli</label>
                                <input id="colli" type="number" inputmode="numeric" min="{{ $opportunity->min_lot }}"
                                       step="{{ $opportunity->order_multiple }}" wire:model.live="colli"
                                       class="input text-center text-lg font-semibold" placeholder="Altra quantità">
                                <button type="button" wire:click="incrementa" class="btn-ghost size-11 px-0" aria-label="Aumenta colli">+</button>
                            </div>

                            @error('colli') <p class="error"><span aria-hidden="true">⚠</span>{{ $message }}</p> @enderror

                            <p class="mt-3 rounded-lg bg-mare-50 px-3 py-2 text-sm font-semibold text-mare-800" aria-live="polite">
                                {{ (int) $colli }} colli × {{ \App\Support\Format::decimal($opportunity->kg_per_package, 2) }} kg
                                = {{ \App\Support\Format::decimal($kgPrevisti, 2) }} kg
                            </p>

                            @if ($opportunity->isLimited())
                                <p class="help">Residui disponibili: {{ $residui }} colli.</p>
                            @endif
                        </fieldset>
                    @endif

                    @if ($decisione === 'non_acquista')
                        <div class="mt-5">
                            <label for="motivazione" class="label">
                                Motivazione
                                {{ $opportunity->requires_refusal_reason || config('pescheria.require_refusal_reason') ? '(obbligatoria)' : '(facoltativa)' }}
                            </label>
                            <textarea id="motivazione" rows="2" wire:model="motivazione" class="input py-2"></textarea>
                        </div>
                    @endif

                    {{-- Azioni desktop --}}
                    <div class="mt-5 hidden gap-3 lg:flex">
                        <button type="button" wire:click="salvaBozza" class="btn-ghost flex-1">Salva bozza</button>
                        <button type="button" wire:click="apriConferma" class="btn-primary flex-1">Invia risposta</button>
                    </div>
                @else
                    <p class="mt-3 text-sm text-slate-600">
                        Le risposte sono chiuse. Per correzioni contatta il Tecnico.
                    </p>
                @endif
            </div>

            {{-- ------------------------------------------- Ordini degli altri PdV --}}
            <div class="card p-5">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <h3 class="text-base font-bold text-slate-900">Ordini degli altri punti vendita</h3>
                    <p class="text-sm text-slate-600">
                        <strong class="text-slate-900">{{ $colliTotali }}</strong> colli
                        ({{ \App\Support\Format::decimal($kgTotali, 2) }} kg)
                        da {{ $puntiVenditaConOrdine }} punti vendita
                    </p>
                </div>

                @if ($opportunity->isLimited())
                    @php $percentuale = $opportunity->total_packages > 0 ? min(100, round($colliTotali / $opportunity->total_packages * 100)) : 0; @endphp
                    <div class="mt-3">
                        <div class="h-2 w-full overflow-hidden rounded bg-slate-200">
                            <div class="h-full {{ $percentuale >= 90 ? 'bg-rose-600' : 'bg-laguna-500' }}" style="width: {{ $percentuale }}%"></div>
                        </div>
                        <p class="help">{{ $percentuale }}% della disponibilità già impegnato — restano {{ $residui }} colli.</p>
                    </div>
                @endif

                <ul class="mt-4 divide-y divide-slate-100">
                    @foreach ($classifica as $indice => $riga)
                        <li @class([
                                'flex items-center justify-between gap-3 py-2.5 text-sm',
                                'rounded-lg bg-mare-50 px-2' => $riga['proprio'],
                            ])>
                            <span class="flex min-w-0 items-center gap-2">
                                <span class="w-5 shrink-0 text-right text-xs font-semibold text-slate-400">{{ $indice + 1 }}</span>
                                <span class="min-w-0">
                                    <span class="block truncate font-semibold text-slate-900">
                                        {{ $riga['store']->code }}
                                        @if ($riga['proprio'])
                                            <span class="ml-1 rounded bg-mare-700 px-1.5 py-0.5 text-[10px] font-bold uppercase text-white">Tu</span>
                                        @endif
                                    </span>
                                    <span class="block truncate text-xs text-slate-500">{{ $riga['store']->name }}</span>
                                </span>
                            </span>

                            <span class="flex shrink-0 items-center gap-3">
                                @if ($riga['colli'] > 0)
                                    <span class="text-right">
                                        <span class="block font-bold text-slate-900">{{ $riga['colli'] }} colli</span>
                                        <span class="block text-xs text-slate-500">{{ \App\Support\Format::decimal($riga['kg'], 1) }} kg</span>
                                    </span>
                                @else
                                    <x-badge-risposta :stato="$riga['stato']" />
                                @endif
                            </span>
                        </li>
                    @endforeach
                </ul>

                <p class="help mt-3">
                    Le quantità degli altri punti vendita sono visibili a tutti i destinatari.
                    Puoi modificare soltanto la tua risposta.
                </p>
            </div>
        </div>
    </div>

    {{-- ---------------------------------------------------- Barra azioni mobile --}}
    @if ($apribile)
        <div class="fixed inset-x-0 bottom-0 z-30 border-t border-slate-200 bg-white px-4 py-3 shadow-lg lg:hidden">
            <div class="mx-auto flex max-w-2xl items-center gap-3">
                <button type="button" wire:click="salvaBozza" class="btn-ghost flex-1">Bozza</button>
                <button type="button" wire:click="apriConferma" class="btn-primary flex-[2]">Invia risposta</button>
            </div>
        </div>
    @endif

    {{-- ---------------------------------------------------- Dialog di conferma --}}
    @if ($confermaAperta)
        <div class="fixed inset-0 z-50 flex items-end justify-center bg-slate-900/60 p-4 sm:items-center"
             role="dialog" aria-modal="true" aria-labelledby="titolo-conferma">
            <div class="card w-full max-w-md p-5">
                <h3 id="titolo-conferma" class="text-lg font-bold text-slate-900">Confermi la risposta?</h3>

                <div class="mt-3 space-y-1 rounded-lg bg-slate-50 p-3 text-sm text-slate-700">
                    <p><strong>{{ $opportunity->description }}</strong> ({{ $opportunity->article_code }})</p>
                    @if ($decisione === 'acquista')
                        <p>{{ (int) $colli }} colli × {{ \App\Support\Format::decimal($opportunity->kg_per_package, 2) }} kg
                            = <strong>{{ \App\Support\Format::decimal($kgPrevisti, 2) }} kg</strong></p>
                    @else
                        <p><strong>Non acquisto</strong>{{ $motivazione ? ' — '.$motivazione : '' }}</p>
                    @endif
                    <p>Consegna {{ \App\Support\Format::date($opportunity->delivery_date) }}</p>
                </div>

                <div class="mt-5 flex gap-3">
                    <button type="button" wire:click="$set('confermaAperta', false)" class="btn-ghost flex-1">Annulla</button>
                    <button type="button" wire:click="invia" class="btn-primary flex-1" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="invia">Conferma e invia</span>
                        <span wire:loading wire:target="invia">Invio…</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
