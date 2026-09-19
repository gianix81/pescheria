@props(['opportunita'])
@php $media = $opportunita->media; @endphp

<div x-data="{ attivo: 0 }" class="space-y-3">
    @if ($media->isEmpty())
        <div class="flex aspect-[4/3] items-center justify-center rounded-xl bg-slate-100 text-sm text-slate-500">
            Nessun contenuto multimediale
        </div>
    @else
        @foreach ($media as $indice => $file)
            <div x-show="attivo === {{ $indice }}" @if($indice > 0) x-cloak @endif>
                @if (! $file->esiste())
                    {{-- La riga c'è, il file no: succede se il disco non è persistente. --}}
                    <div class="flex aspect-[4/3] flex-col items-center justify-center gap-2 rounded-xl border border-dashed border-slate-300 bg-slate-50 px-6 text-center">
                        <span class="text-3xl" aria-hidden="true">🖼</span>
                        <p class="text-sm font-semibold text-slate-700">
                            {{ $file->type->label() }} non più disponibile
                        </p>
                        <p class="max-w-sm text-xs text-slate-500">
                            Il file è stato caricato il {{ \App\Support\Format::date($file->created_at) }}
                            ma non si trova più sul disco. Ricaricalo dalla modifica dell'opportunità.
                        </p>
                    </div>
                @elseif ($file->isVideo())
                    {{-- Nessun autoplay: il video parte solo su azione dell'utente --}}
                    <video
                        class="mx-auto max-h-[60vh] w-full rounded-xl bg-black object-contain"
                        controls preload="metadata" playsinline
                        @if ($file->posterUrl()) poster="{{ $file->posterUrl() }}" @endif
                    >
                        <source src="{{ $file->temporaryUrl() }}" type="{{ $file->mime }}">
                        Il tuo browser non supporta la riproduzione video.
                    </video>
                @else
                    <img src="{{ $file->temporaryUrl() }}" alt="{{ $opportunita->description }}"
                         class="mx-auto max-h-[60vh] w-full rounded-xl object-contain bg-slate-50">
                @endif
            </div>
        @endforeach

        @if ($media->count() > 1)
            <div class="flex gap-2 overflow-x-auto pb-1" role="tablist" aria-label="Contenuti multimediali">
                @foreach ($media as $indice => $file)
                    <button type="button" @click="attivo = {{ $indice }}" role="tab"
                            :aria-selected="attivo === {{ $indice }}"
                            class="flex size-16 shrink-0 items-center justify-center rounded-lg border-2 bg-slate-100 text-xs font-semibold"
                            :class="attivo === {{ $indice }} ? 'border-laguna-500' : 'border-transparent'">
                        {{ $file->isVideo() ? '▶ Video' : 'Foto '.($indice + 1) }}
                    </button>
                @endforeach
            </div>
        @endif
    @endif
</div>
