<div class="grid gap-5 lg:grid-cols-3">
    <div class="lg:col-span-2">
        <div class="card mb-4 p-4">
            <label for="ricerca" class="label">Cerca punto vendita</label>
            <input id="ricerca" type="search" wire:model.live.debounce.400ms="ricerca" class="input py-2 text-sm">
        </div>

        <div class="card overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th scope="col" class="th">Codice</th>
                        <th scope="col" class="th">Portale</th>
                        <th scope="col" class="th">Nome</th>
                        <th scope="col" class="th">Città</th>
                        <th scope="col" class="th">Stato</th>
                        <th scope="col" class="th"><span class="sr-only">Azioni</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($elenco as $pv)
                        <tr class="hover:bg-slate-50">
                            <td class="td font-semibold">{{ $pv->code }}</td>
                            <td class="td">
                                @if ($pv->portal_code)
                                    {{ $pv->portal_code }}
                                @else
                                    <span class="text-xs text-rose-700">manca</span>
                                @endif
                            </td>
                            <td class="td">{{ $pv->name }}</td>
                            <td class="td">{{ $pv->city }} ({{ $pv->province }})</td>
                            <td class="td">
                                <span class="badge {{ $pv->is_active ? 'bg-emerald-50 text-emerald-800 ring-emerald-300' : 'bg-slate-200 text-slate-700 ring-slate-400' }}">
                                    {{ $pv->is_active ? 'Attivo' : 'Disattivo' }}
                                </span>
                            </td>
                            <td class="td text-right">
                                <button type="button" wire:click="modifica({{ $pv->id }})" class="font-semibold text-laguna-600 underline">Modifica</button>
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
                {{ $modificaId ? 'Modifica punto vendita' : 'Nuovo punto vendita' }}
            </h3>
            @if ($modificaId)
                <button type="button" wire:click="nuovo" class="text-xs font-semibold text-laguna-600 underline">Nuovo</button>
            @endif
        </div>

        <div class="mt-4 space-y-3">
            <div>
                <label for="code" class="label">Codice *</label>
                <input id="code" wire:model="form.code" class="input @error('form.code') input-error @enderror">
                @error('form.code') <p class="error"><span aria-hidden="true">⚠</span>{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="portal_code" class="label">Codice cliente portale</label>
                <input id="portal_code" wire:model="form.portal_code" class="input" inputmode="numeric">
                <p class="help">Numero cliente usato dal portale del fornitore, es. 566518.</p>
                @error('form.portal_code') <p class="error"><span aria-hidden="true">⚠</span>{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="name" class="label">Ragione sociale / nome *</label>
                <input id="name" wire:model="form.name" class="input @error('form.name') input-error @enderror">
                @error('form.name') <p class="error"><span aria-hidden="true">⚠</span>{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="address" class="label">Indirizzo</label>
                <input id="address" wire:model="form.address" class="input">
            </div>
            <div class="grid grid-cols-3 gap-2">
                <div class="col-span-2">
                    <label for="city" class="label">Città</label>
                    <input id="city" wire:model="form.city" class="input">
                </div>
                <div>
                    <label for="province" class="label">Prov.</label>
                    <input id="province" maxlength="2" wire:model="form.province" class="input">
                </div>
            </div>
            <div>
                <label for="email" class="label">Email</label>
                <input id="email" type="email" wire:model="form.email" class="input">
            </div>
            <div>
                <label for="phone" class="label">Telefono</label>
                <input id="phone" wire:model="form.phone" class="input">
            </div>
            <label class="flex items-center gap-2 text-sm text-slate-700">
                <input type="checkbox" wire:model="form.is_active" class="rounded border-slate-300 text-mare-700 focus:ring-laguna-500">
                Attivo
            </label>

            <button type="button" wire:click="salva" class="btn-primary w-full">Salva</button>
        </div>
    </aside>
</div>
