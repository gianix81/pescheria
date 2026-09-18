<div class="grid gap-5 lg:grid-cols-3">
    <div class="lg:col-span-2">
        <div class="card mb-4 p-4">
            <label for="ricerca" class="label">Cerca per codice, PLU o descrizione</label>
            <input id="ricerca" type="search" wire:model.live.debounce.400ms="ricerca" class="input py-2 text-sm">
        </div>

        <div class="card overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th scope="col" class="th">Codice</th>
                        <th scope="col" class="th">PLU</th>
                        <th scope="col" class="th">Descrizione</th>
                        <th scope="col" class="th">Categoria</th>
                        <th scope="col" class="th">IVA</th>
                        <th scope="col" class="th"><span class="sr-only">Azioni</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($elenco as $p)
                        <tr class="hover:bg-slate-50">
                            <td class="td font-semibold">{{ $p->article_code }}</td>
                            <td class="td">{{ $p->plu }}</td>
                            <td class="td">{{ $p->description }}</td>
                            <td class="td">{{ $p->category }}</td>
                            <td class="td">{{ \App\Support\Format::percent($p->vat_rate, 0) }}</td>
                            <td class="td text-right">
                                <button type="button" wire:click="modifica({{ $p->id }})" class="font-semibold text-laguna-600 underline">Modifica</button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $elenco->links() }}</div>
    </div>

    <aside class="card h-fit p-5 lg:sticky lg:top-20">
        <div class="flex items-center justify-between">
            <h3 class="text-sm font-bold uppercase tracking-wide text-slate-500">
                {{ $modificaId ? 'Modifica prodotto' : 'Nuovo prodotto' }}
            </h3>
            @if ($modificaId)
                <button type="button" wire:click="nuovo" class="text-xs font-semibold text-laguna-600 underline">Nuovo</button>
            @endif
        </div>

        <div class="mt-4 space-y-3">
            <div>
                <label for="article_code" class="label">Codice articolo *</label>
                <input id="article_code" wire:model="form.article_code" class="input @error('form.article_code') input-error @enderror">
                @error('form.article_code') <p class="error"><span aria-hidden="true">⚠</span>{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="plu" class="label">PLU</label>
                <input id="plu" wire:model="form.plu" class="input">
            </div>
            <div>
                <label for="description" class="label">Descrizione *</label>
                <input id="description" wire:model="form.description" class="input @error('form.description') input-error @enderror">
                @error('form.description') <p class="error"><span aria-hidden="true">⚠</span>{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="long_description" class="label">Descrizione estesa</label>
                <textarea id="long_description" rows="2" wire:model="form.long_description" class="input py-2"></textarea>
            </div>
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label for="category" class="label">Categoria</label>
                    <input id="category" wire:model="form.category" class="input">
                </div>
                <div>
                    <label for="origin" class="label">Origine</label>
                    <input id="origin" wire:model="form.origin" class="input">
                </div>
                <div>
                    <label for="fao_zone" class="label">Zona FAO</label>
                    <input id="fao_zone" wire:model="form.fao_zone" class="input">
                </div>
                <div>
                    <label for="production_method" class="label">Metodo</label>
                    <input id="production_method" wire:model="form.production_method" class="input">
                </div>
                <div>
                    <label for="caliber" class="label">Calibro</label>
                    <input id="caliber" wire:model="form.caliber" class="input">
                </div>
                <div>
                    <label for="unit_of_measure" class="label">Unità</label>
                    <input id="unit_of_measure" wire:model="form.unit_of_measure" class="input">
                </div>
                <div>
                    <label for="vat_rate" class="label">IVA %</label>
                    <input id="vat_rate" type="number" step="0.01" wire:model="form.vat_rate" class="input">
                </div>
                <div>
                    <label for="default_kg_per_package" class="label">Kg/collo std</label>
                    <input id="default_kg_per_package" type="number" step="0.001" wire:model="form.default_kg_per_package" class="input">
                </div>
            </div>
            <label class="flex items-center gap-2 text-sm text-slate-700">
                <input type="checkbox" wire:model="form.is_active" class="rounded border-slate-300 text-mare-700 focus:ring-laguna-500">
                Attivo
            </label>

            <button type="button" wire:click="salva" class="btn-primary w-full">Salva</button>
        </div>
    </aside>
</div>
