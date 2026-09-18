<div class="space-y-4">
    <div>
        <label for="ricercaArticolo" class="label">Cerca articolo (codice, PLU o descrizione)</label>
        <input id="ricercaArticolo" type="search" wire:model.live.debounce.300ms="ricercaArticolo"
               class="input" placeholder="Es. ART10001, 2101, orata" autocomplete="off">
        @if ($risultatiRicerca->isNotEmpty())
            <ul class="mt-2 max-h-56 overflow-y-auto rounded-lg border border-slate-200 bg-white text-sm shadow-sm">
                @foreach ($risultatiRicerca as $prodotto)
                    <li>
                        <button type="button" wire:click="selezionaProdotto({{ $prodotto->id }})"
                                class="flex w-full items-center justify-between gap-3 px-3 py-2.5 text-left hover:bg-slate-50">
                            <span>
                                <span class="font-semibold text-slate-900">{{ $prodotto->description }}</span>
                                <span class="block text-xs text-slate-500">{{ $prodotto->article_code }} · PLU {{ $prodotto->plu }}</span>
                            </span>
                            <span class="text-xs text-slate-500">{{ $prodotto->category }}</span>
                        </button>
                    </li>
                @endforeach
            </ul>
        @endif
        <p class="help">Selezionando l'articolo si compilano automaticamente i dati anagrafici.</p>
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <label for="article_code" class="label">Codice articolo *</label>
            <input id="article_code" wire:model="article_code" class="input @error('article_code') input-error @enderror">
            @error('article_code') <p class="error"><span aria-hidden="true">⚠</span>{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="plu" class="label">PLU</label>
            <input id="plu" wire:model="plu" class="input">
        </div>
    </div>

    <div>
        <label for="description" class="label">Descrizione articolo *</label>
        <input id="description" wire:model="description" class="input @error('description') input-error @enderror">
        @error('description') <p class="error"><span aria-hidden="true">⚠</span>{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="title" class="label">Titolo breve *</label>
        <input id="title" wire:model="title" class="input @error('title') input-error @enderror">
        @error('title') <p class="error"><span aria-hidden="true">⚠</span>{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="commercial_description" class="label">Descrizione commerciale</label>
        <textarea id="commercial_description" rows="3" wire:model="commercial_description" class="input py-2"></textarea>
    </div>
</div>
