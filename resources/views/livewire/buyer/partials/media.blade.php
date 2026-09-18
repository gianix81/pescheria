<div class="space-y-3">
    <div>
        <label for="nuoviMedia" class="label">Foto e video del prodotto</label>
        {{-- capture="environment" apre direttamente la fotocamera posteriore su smartphone --}}
        <input id="nuoviMedia" type="file" wire:model="nuoviMedia" multiple accept="image/*,video/*" capture="environment"
               class="block w-full rounded-lg border border-slate-300 bg-white p-2 text-sm">
        <p class="help">
            Immagini fino a {{ config('pescheria.media.max_image_mb') }} MB, video fino a {{ config('pescheria.media.max_video_mb') }} MB.
            È sufficiente un solo video verticale del prodotto.
        </p>
        @error('nuoviMedia') <p class="error"><span aria-hidden="true">⚠</span>{{ $message }}</p> @enderror
        @error('nuoviMedia.*') <p class="error"><span aria-hidden="true">⚠</span>{{ $message }}</p> @enderror
    </div>

    {{-- Barra di avanzamento dell'upload --}}
    <div x-data="{ progresso: 0, attivo: false }"
         x-on:livewire-upload-start="attivo = true"
         x-on:livewire-upload-finish="attivo = false; progresso = 0"
         x-on:livewire-upload-error="attivo = false"
         x-on:livewire-upload-progress="progresso = $event.detail.progress">
        <div x-show="attivo" x-cloak class="h-2 w-full overflow-hidden rounded bg-slate-200">
            <div class="h-full bg-laguna-500 transition-all" :style="`width: ${progresso}%`"></div>
        </div>
        <p x-show="attivo" x-cloak class="help" aria-live="polite">Caricamento in corso: <span x-text="progresso"></span>%</p>
    </div>

    @if (count($nuoviMedia))
        <button type="button" wire:click="caricaMedia" class="btn-secondary w-full sm:w-auto">
            Allega {{ count($nuoviMedia) }} file
        </button>
    @endif

    @if ($opportunity?->media?->isNotEmpty())
        <ul class="grid grid-cols-2 gap-3 sm:grid-cols-4">
            @foreach ($opportunity->media as $file)
                <li class="card overflow-hidden">
                    <div class="flex h-24 items-center justify-center bg-slate-100">
                        @if ($file->isVideo())
                            <span class="text-2xl" aria-hidden="true">▶</span>
                        @else
                            <img src="{{ $file->temporaryUrl() }}" alt="" class="h-24 w-full object-cover">
                        @endif
                    </div>
                    <div class="flex items-center justify-between gap-2 p-2">
                        <span class="truncate text-xs text-slate-600">{{ $file->type->label() }}</span>
                        <button type="button" wire:click="eliminaMedia({{ $file->id }})"
                                wire:confirm="Eliminare questo file?"
                                class="text-xs font-semibold text-rose-700 underline">Elimina</button>
                    </div>
                </li>
            @endforeach
        </ul>
    @endif
</div>
