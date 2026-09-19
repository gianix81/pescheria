<div class="mx-auto max-w-4xl space-y-5">

    {{-- Riepilogo errori in testa alla pagina --}}
    @if ($errors->any())
        <div class="rounded-lg border border-rose-300 bg-rose-50 px-4 py-3 text-sm text-rose-900" role="alert" tabindex="-1">
            <p class="font-semibold"><span aria-hidden="true">⚠</span> Correggi questi punti prima di continuare</p>
            <ul class="mt-1 list-inside list-disc">
                @foreach ($errors->all() as $errore)
                    <li>{{ $errore }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if ($this->pubblicata)
        <div class="rounded-lg border border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-900" role="status">
            <p class="font-semibold"><span aria-hidden="true">⚠</span> Opportunità già pubblicata ({{ $opportunity->status->label() }})</p>
            <p class="mt-1">
                Salvando, l'opportunità <strong>torna in verifica</strong>: resta ferma finché un Tecnico non
                la conferma, e solo allora torna disponibile per i punti vendita. Le risposte già raccolte
                restano dove sono.
            </p>
            <p class="mt-1">
                Non puoi ridurre i colli sotto quelli già confermati, né togliere punti vendita che hanno
                già risposto. Ogni modifica finisce nell'audit log.
            </p>
        </div>
    @endif

    @error('salvataggio')
        <p class="error" role="alert"><span aria-hidden="true">⚠</span>{{ $message }}</p>
    @enderror

    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-lg font-bold text-slate-900">
                {{ $opportunity ? 'Modifica '.$opportunity->reference : 'Nuova opportunità' }}
            </h2>
            <p class="text-sm text-slate-600">Le due modalità salvano esattamente gli stessi dati.</p>
        </div>

        <div class="flex gap-2" role="tablist" aria-label="Modalità di creazione">
            @foreach (['rapida' => 'Creazione rapida', 'guidata' => 'Creazione guidata'] as $chiave => $etichetta)
                <button type="button" wire:click="$set('modalita', '{{ $chiave }}')" role="tab"
                        aria-selected="{{ $modalita === $chiave ? 'true' : 'false' }}"
                        @class([
                            'btn px-3 py-2 text-sm',
                            'bg-mare-700 text-white' => $modalita === $chiave,
                            'border border-slate-300 bg-white text-slate-700' => $modalita !== $chiave,
                        ])>{{ $etichetta }}</button>
            @endforeach
        </div>
    </div>

    {{-- ============================================================ RAPIDA --}}
    @if ($modalita === 'rapida')
        <div class="space-y-4">
            <section class="card p-5" aria-labelledby="sez-media">
                <h3 id="sez-media" class="mb-3 text-sm font-bold uppercase tracking-wide text-slate-500">1 · Video o foto</h3>
                @include('livewire.buyer.partials.media')
            </section>

            <section class="card p-5" aria-labelledby="sez-articolo">
                <h3 id="sez-articolo" class="mb-3 text-sm font-bold uppercase tracking-wide text-slate-500">2 · Articolo</h3>
                @include('livewire.buyer.partials.articolo')
            </section>

            <section class="card p-5" aria-labelledby="sez-prezzi">
                <h3 id="sez-prezzi" class="mb-3 text-sm font-bold uppercase tracking-wide text-slate-500">3 · Prezzi e confezionamento</h3>
                @include('livewire.buyer.partials.prezzi')
            </section>

            <section class="card p-5" aria-labelledby="sez-disp">
                <h3 id="sez-disp" class="mb-3 text-sm font-bold uppercase tracking-wide text-slate-500">4 · Disponibilità, scadenza e consegna</h3>
                @include('livewire.buyer.partials.disponibilita')
            </section>

            <section class="card p-5" aria-labelledby="sez-dest">
                <h3 id="sez-dest" class="mb-3 text-sm font-bold uppercase tracking-wide text-slate-500">5 · Destinatari</h3>
                @include('livewire.buyer.partials.destinatari')
            </section>
        </div>
    @else
        {{-- ============================================================ GUIDATA --}}
        <ol class="flex flex-wrap gap-2 text-sm" aria-label="Passaggi">
            @foreach ([1 => 'Prodotto', 2 => 'Media e prezzi', 3 => 'Disponibilità', 4 => 'Destinatari'] as $numero => $etichetta)
                <li>
                    <button type="button" wire:click="vaiAlPasso({{ $numero }})"
                            @class([
                                'btn px-3 py-2',
                                'bg-mare-700 text-white' => $passo === $numero,
                                'border border-slate-300 bg-white text-slate-700' => $passo !== $numero,
                            ])
                            aria-current="{{ $passo === $numero ? 'step' : 'false' }}">
                        {{ $numero }}. {{ $etichetta }}
                    </button>
                </li>
            @endforeach
        </ol>

        <div class="card p-5">
            @if ($passo === 1)
                @include('livewire.buyer.partials.articolo')
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="technical_notes" class="label">Note tecniche</label>
                        <textarea id="technical_notes" rows="2" wire:model="technical_notes" class="input py-2"></textarea>
                    </div>
                    <div>
                        <label for="logistics_notes" class="label">Note logistiche</label>
                        <textarea id="logistics_notes" rows="2" wire:model="logistics_notes" class="input py-2"></textarea>
                    </div>
                    <div>
                        <label for="origin" class="label">Origine</label>
                        <input id="origin" wire:model="origin" class="input">
                    </div>
                    <div>
                        <label for="fao_zone" class="label">Zona FAO</label>
                        <input id="fao_zone" wire:model="fao_zone" class="input">
                    </div>
                    <div>
                        <label for="production_method" class="label">Metodo di produzione/pesca</label>
                        <input id="production_method" wire:model="production_method" class="input">
                    </div>
                    <div>
                        <label for="caliber" class="label">Calibro</label>
                        <input id="caliber" wire:model="caliber" class="input">
                    </div>
                </div>
            @elseif ($passo === 2)
                @include('livewire.buyer.partials.media')
                <hr class="my-5 border-slate-200">
                @include('livewire.buyer.partials.prezzi')
            @elseif ($passo === 3)
                @include('livewire.buyer.partials.disponibilita')
            @else
                @include('livewire.buyer.partials.destinatari')

                <hr class="my-5 border-slate-200">
                <h3 class="text-sm font-bold uppercase tracking-wide text-slate-500">Riepilogo</h3>
                <dl class="mt-3 grid gap-3 text-sm sm:grid-cols-3">
                    <div><dt class="text-slate-500">Articolo</dt><dd class="font-semibold">{{ $article_code }} — {{ $description }}</dd></div>
                    <div><dt class="text-slate-500">Kg per collo</dt><dd class="font-semibold">{{ $kg_per_package ?: '—' }}</dd></div>
                    <div><dt class="text-slate-500">Disponibilità</dt><dd class="font-semibold">{{ $availability_type === 'LIMITATA' ? $total_packages.' colli' : 'Illimitata' }}</dd></div>
                    <div><dt class="text-slate-500">Scadenza</dt><dd class="font-semibold">{{ $closes_at }}</dd></div>
                    <div><dt class="text-slate-500">Consegna</dt><dd class="font-semibold">{{ $delivery_date }}</dd></div>
                    <div><dt class="text-slate-500">Destinatari</dt><dd class="font-semibold">{{ count($store_ids) }} punti vendita</dd></div>
                </dl>
            @endif
        </div>

        <div class="flex justify-between">
            <button type="button" wire:click="vaiAlPasso({{ $passo - 1 }})" @disabled($passo === 1) class="btn-ghost">Indietro</button>
            <button type="button" wire:click="vaiAlPasso({{ $passo + 1 }})" @disabled($passo === 4) class="btn-ghost">Avanti</button>
        </div>
    @endif

    {{-- Problemi che impediscono la pubblicazione --}}
    @if ($problemiPubblicazione)
        <div class="rounded-lg border border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-900" role="status">
            <p class="font-semibold"><span aria-hidden="true">⚠</span> Prima dell'invio in verifica</p>
            <ul class="mt-1 list-inside list-disc">
                @foreach ($problemiPubblicazione as $problema)
                    <li>{{ $problema }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Azioni --}}
    <div class="sticky bottom-0 -mx-4 flex flex-wrap gap-3 border-t border-slate-200 bg-white px-4 py-3 sm:mx-0 sm:rounded-xl sm:border sm:px-5">
        <button type="button" wire:click="salvaBozza"
                @class(['flex-1 sm:flex-none', 'btn-primary' => $this->pubblicata, 'btn-ghost' => ! $this->pubblicata])>
            <span wire:loading.remove wire:target="salvaBozza">{{ $this->pubblicata ? 'Salva e ripubblica' : 'Salva bozza' }}</span>
            <span wire:loading wire:target="salvaBozza">Salvataggio…</span>
        </button>

        <button type="button" wire:click="$toggle('anteprimaAperta')" class="btn-ghost flex-1 sm:flex-none">
            {{ $anteprimaAperta ? 'Chiudi anteprima' : 'Anteprima CR' }}
        </button>

        @unless ($this->pubblicata)
            <button type="button" wire:click="inviaInVerifica" class="btn-primary flex-1 sm:ml-auto sm:flex-none">
                <span wire:loading.remove wire:target="inviaInVerifica">Invia in verifica</span>
                <span wire:loading wire:target="inviaInVerifica">Invio…</span>
            </button>
        @else
            <a href="{{ route('opportunita.show', $opportunity) }}" class="btn-ghost flex-1 sm:ml-auto sm:flex-none">
                Torna alla scheda
            </a>
        @endunless
    </div>

    {{-- Anteprima identica a ciò che vedrà il punto vendita --}}
    @if ($anteprimaAperta)
        <section class="card p-5" aria-label="Anteprima della scheda come la vedrà il punto vendita">
            <h3 class="text-sm font-bold uppercase tracking-wide text-slate-500">Anteprima vista punto vendita</h3>
            <div class="mt-4 grid gap-5 lg:grid-cols-2">
                <div class="rounded-xl bg-slate-100 p-8 text-center text-sm text-slate-500">
                    {{ $opportunity?->media?->count() ? $opportunity->media->count().' contenuti multimediali' : 'Nessun media caricato' }}
                </div>
                <div>
                    <h4 class="text-xl font-bold text-slate-900">{{ $title ?: 'Titolo non impostato' }}</h4>
                    <p class="mt-1 text-sm text-slate-600">{{ $commercial_description }}</p>
                    <dl class="mt-4 grid grid-cols-2 gap-3 text-sm">
                        <div><dt class="text-xs uppercase text-slate-500">Codice</dt><dd class="font-semibold">{{ $article_code ?: '—' }}</dd></div>
                        <div><dt class="text-xs uppercase text-slate-500">PLU</dt><dd class="font-semibold">{{ $plu ?: '—' }}</dd></div>
                        <div><dt class="text-xs uppercase text-slate-500">Kg per collo</dt><dd class="font-semibold">{{ $kg_per_package ?: '—' }}</dd></div>
                        <div><dt class="text-xs uppercase text-slate-500">Vendita</dt><dd class="font-semibold">{{ \App\Support\Format::money($sale_price_gross) }}/kg</dd></div>
                        <div><dt class="text-xs uppercase text-slate-500">Disponibilità</dt><dd class="font-semibold">{{ $availability_type === 'LIMITATA' ? $total_packages.' colli' : 'Colli illimitati' }}</dd></div>
                        <div><dt class="text-xs uppercase text-slate-500">Consegna</dt><dd class="font-semibold">{{ $delivery_date }}</dd></div>
                    </dl>
                    <div class="mt-4 grid grid-cols-3 gap-2 sm:grid-cols-6">
                        @foreach (config('pescheria.quick_quantities') as $q)
                            <span class="btn border border-slate-300 bg-white text-slate-700">{{ $q }}</span>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>
    @endif
</div>
