@php
    $compressione = config('pescheria.media.compressione');
@endphp

<div class="space-y-3" x-data="caricatoreMedia(@js($compressione))">
    <div>
        <label for="sceltaMedia" class="label">Foto e video del prodotto</label>

        {{-- L'utente sceglie qui: i file vengono compressi nel browser e solo
             dopo passati all'input collegato a Livewire.
             capture="environment" apre la fotocamera posteriore su smartphone. --}}
        <input id="sceltaMedia" type="file" multiple accept="image/*,video/*" capture="environment"
               x-on:change="seleziona($event)" x-bind:disabled="elaborazione"
               class="block w-full rounded-lg border border-slate-300 bg-white p-2 text-sm">

        {{-- Input reale usato da Livewire: nascosto, riceve i file già compressi. --}}
        <input type="file" wire:model="nuoviMedia" multiple x-ref="inputLivewire" class="sr-only" tabindex="-1" aria-hidden="true">

        <p class="help">
            Le foto vengono ridotte a {{ $compressione['immagini']['latoMassimo'] }} px e i video a
            {{ $compressione['video']['latoMassimo'] }} px direttamente sul tuo dispositivo: l'invio è molto più rapido.
            Limiti dopo la compressione: immagini {{ config('pescheria.media.max_image_mb') }} MB,
            video {{ config('pescheria.media.max_video_mb') }} MB.
        </p>

        <p class="help" x-show="!supportoVideo" x-cloak>
            <span aria-hidden="true">ⓘ</span>
            Questo browser non sa comprimere i video: verranno inviati alla dimensione originale.
            Su un video lungo l'invio può richiedere qualche minuto.
        </p>

        @error('nuoviMedia') <p class="error"><span aria-hidden="true">⚠</span>{{ $message }}</p> @enderror
        @error('nuoviMedia.*') <p class="error"><span aria-hidden="true">⚠</span>{{ $message }}</p> @enderror
    </div>

    {{-- Avanzamento della compressione, file per file --}}
    <template x-if="elementi.length">
        <ul class="space-y-2" aria-live="polite">
            <template x-for="elemento in elementi" :key="elemento.nome">
                <li class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm">
                    <div class="flex items-center justify-between gap-2">
                        <span class="truncate font-medium text-slate-800" x-text="elemento.nome"></span>
                        <span class="shrink-0 text-xs font-semibold text-slate-600" x-text="elemento.stato"></span>
                    </div>
                    <div class="mt-1 h-1.5 w-full overflow-hidden rounded bg-slate-200">
                        <div class="h-full bg-laguna-500 transition-all" :style="`width: ${elemento.percentuale}%`"></div>
                    </div>
                    <p class="mt-1 text-xs text-slate-600" x-text="elemento.dettaglio"></p>
                </li>
            </template>
        </ul>
    </template>

    {{-- Barra di avanzamento dell'upload verso il server --}}
    <div x-data="{ progresso: 0, attivo: false }"
         x-on:livewire-upload-start="attivo = true"
         x-on:livewire-upload-finish="attivo = false; progresso = 0"
         x-on:livewire-upload-error="attivo = false"
         x-on:livewire-upload-progress="progresso = $event.detail.progress">
        <div x-show="attivo" x-cloak class="h-2 w-full overflow-hidden rounded bg-slate-200">
            <div class="h-full bg-mare-700 transition-all" :style="`width: ${progresso}%`"></div>
        </div>
        <p x-show="attivo" x-cloak class="help" aria-live="polite">Invio in corso: <span x-text="progresso"></span>%</p>
    </div>

    @if (count($nuoviMedia))
        <button type="button" wire:click="caricaMedia" x-on:click="pulisci()" class="btn-secondary w-full sm:w-auto">
            Allega {{ count($nuoviMedia) }} file
        </button>
    @endif

    @if ($opportunity?->media?->isNotEmpty())
        <ul class="grid grid-cols-2 gap-3 sm:grid-cols-4">
            @foreach ($opportunity->media as $file)
                <li class="card overflow-hidden">
                    <div class="flex h-24 items-center justify-center bg-slate-100">
                        @if (! $file->esiste())
                            <span class="px-2 text-center text-[11px] text-rose-700">file mancante</span>
                        @elseif ($file->isVideo())
                            <span class="text-2xl" aria-hidden="true">▶</span>
                        @else
                            <img src="{{ $file->temporaryUrl() }}" alt="" class="h-24 w-full object-cover">
                        @endif
                    </div>
                    <div class="flex items-center justify-between gap-2 p-2">
                        <span class="truncate text-xs text-slate-600">
                            {{ $file->type->label() }} · {{ number_format($file->size / 1024 / 1024, 1, ',', '.') }} MB
                        </span>
                        <button type="button" wire:click="eliminaMedia({{ $file->id }})"
                                wire:confirm="Eliminare questo file?"
                                class="text-xs font-semibold text-rose-700 underline">Elimina</button>
                    </div>
                </li>
            @endforeach
        </ul>
    @endif
</div>
