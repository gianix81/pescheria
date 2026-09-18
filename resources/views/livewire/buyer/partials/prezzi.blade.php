<div class="space-y-4">
    <div class="grid gap-4 sm:grid-cols-3">
        <div>
            <label for="purchase_price" class="label">Acquisto EUR/kg *</label>
            <input id="purchase_price" type="number" step="0.01" min="0" wire:model.live.debounce.400ms="purchase_price"
                   class="input @error('purchase_price') input-error @enderror">
            @error('purchase_price') <p class="error"><span aria-hidden="true">⚠</span>{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="sale_price_gross" class="label">Vendita EUR/kg (IVA inclusa) *</label>
            <input id="sale_price_gross" type="number" step="0.01" min="0" wire:model.live.debounce.400ms="sale_price_gross"
                   class="input @error('sale_price_gross') input-error @enderror">
            @error('sale_price_gross') <p class="error"><span aria-hidden="true">⚠</span>{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="vat_rate" class="label">Aliquota IVA % *</label>
            <input id="vat_rate" type="number" step="0.01" min="0" wire:model.live.debounce.400ms="vat_rate" class="input">
        </div>
    </div>

    {{-- Ricarico e margine sono calcolati, non digitati --}}
    <div class="grid gap-3 rounded-lg bg-mare-50 p-4 sm:grid-cols-3" aria-live="polite">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wide text-mare-700">Vendita netto IVA</p>
            <p class="text-lg font-bold text-mare-800">{{ \App\Support\Format::money($prezzi['net']) }}</p>
        </div>
        <div>
            <p class="text-xs font-semibold uppercase tracking-wide text-mare-700" title="Utile diviso il prezzo di acquisto. È il valore che oggi nel gruppo WhatsApp viene chiamato impropriamente margine.">
                Ricarico ⓘ
            </p>
            <p class="text-lg font-bold text-mare-800">{{ \App\Support\Format::percent($prezzi['markup']) }}</p>
        </div>
        <div>
            <p class="text-xs font-semibold uppercase tracking-wide text-mare-700" title="Utile diviso il prezzo di vendita netto IVA. È il margine commerciale.">
                Margine ⓘ
            </p>
            <p class="text-lg font-bold text-mare-800">{{ \App\Support\Format::percent($prezzi['margin']) }}</p>
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-3">
        <div>
            <label for="kg_per_package" class="label">Kg per collo *</label>
            <input id="kg_per_package" type="number" step="0.001" min="0.001" wire:model="kg_per_package"
                   class="input @error('kg_per_package') input-error @enderror">
            @error('kg_per_package') <p class="error"><span aria-hidden="true">⚠</span>{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="min_lot" class="label">Lotto minimo (colli)</label>
            <input id="min_lot" type="number" min="1" wire:model="min_lot" class="input">
        </div>
        <div>
            <label for="order_multiple" class="label">Multiplo di ordinazione</label>
            <input id="order_multiple" type="number" min="1" wire:model="order_multiple" class="input">
        </div>
    </div>
</div>
