<div class="space-y-3">
    <div class="flex items-center justify-between">
        <p class="label">Punti vendita destinatari *</p>
        <div class="flex gap-2">
            <button type="button" class="text-xs font-semibold text-laguna-600 underline"
                    wire:click="$set('store_ids', {{ json_encode($puntiVendita->pluck('id')) }})">Seleziona tutti</button>
            <button type="button" class="text-xs font-semibold text-laguna-600 underline"
                    wire:click="$set('store_ids', [])">Deseleziona</button>
        </div>
    </div>

    <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($puntiVendita as $pv)
            <label class="flex cursor-pointer items-center gap-3 rounded-lg border border-slate-300 p-3 text-sm has-checked:border-mare-700 has-checked:bg-mare-50">
                <input type="checkbox" value="{{ $pv->id }}" wire:model="store_ids"
                       class="rounded border-slate-300 text-mare-700 focus:ring-laguna-500">
                <span>
                    <span class="block font-semibold text-slate-900">{{ $pv->code }}</span>
                    <span class="block text-xs text-slate-600">{{ $pv->name }} · {{ $pv->city }}</span>
                </span>
            </label>
        @endforeach
    </div>
    @error('store_ids') <p class="error"><span aria-hidden="true">⚠</span>{{ $message }}</p> @enderror
</div>
