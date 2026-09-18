<div class="grid gap-5 lg:grid-cols-3">
    <div class="lg:col-span-2">
        <div class="card mb-4 p-4">
            <label for="ricerca" class="label">Cerca utente</label>
            <input id="ricerca" type="search" wire:model.live.debounce.400ms="ricerca" class="input py-2 text-sm">
        </div>

        <div class="card overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th scope="col" class="th">Nome</th>
                        <th scope="col" class="th">Email</th>
                        <th scope="col" class="th">Ruolo</th>
                        <th scope="col" class="th">Punto vendita</th>
                        <th scope="col" class="th">Stato</th>
                        <th scope="col" class="th"><span class="sr-only">Azioni</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($elenco as $u)
                        <tr class="hover:bg-slate-50">
                            <td class="td font-semibold">{{ $u->full_name }}</td>
                            <td class="td">{{ $u->email }}</td>
                            <td class="td">{{ $u->role->label() }}</td>
                            <td class="td">{{ $u->store?->code ?? '—' }}</td>
                            <td class="td">
                                <span class="badge {{ $u->is_active ? 'bg-emerald-50 text-emerald-800 ring-emerald-300' : 'bg-slate-200 text-slate-700 ring-slate-400' }}">
                                    {{ $u->is_active ? 'Attivo' : 'Disattivo' }}
                                </span>
                            </td>
                            <td class="td text-right">
                                <button type="button" wire:click="modifica({{ $u->id }})" class="font-semibold text-laguna-600 underline">Modifica</button>
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
                {{ $modificaId ? 'Modifica utente' : 'Nuovo utente' }}
            </h3>
            @if ($modificaId)
                <button type="button" wire:click="nuovo" class="text-xs font-semibold text-laguna-600 underline">Nuovo</button>
            @endif
        </div>

        <div class="mt-4 space-y-3">
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label for="first_name" class="label">Nome *</label>
                    <input id="first_name" wire:model="form.first_name" class="input">
                    @error('form.first_name') <p class="error"><span aria-hidden="true">⚠</span>{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="last_name" class="label">Cognome *</label>
                    <input id="last_name" wire:model="form.last_name" class="input">
                    @error('form.last_name') <p class="error"><span aria-hidden="true">⚠</span>{{ $message }}</p> @enderror
                </div>
            </div>
            <div>
                <label for="email" class="label">Email *</label>
                <input id="email" type="email" wire:model="form.email" class="input @error('form.email') input-error @enderror">
                @error('form.email') <p class="error"><span aria-hidden="true">⚠</span>{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="phone" class="label">Telefono</label>
                <input id="phone" wire:model="form.phone" class="input">
                <p class="help">Serve per l'eventuale notifica WhatsApp.</p>
            </div>
            <div>
                <label for="role" class="label">Ruolo *</label>
                <select id="role" wire:model.live="form.role" class="input">
                    @foreach ($ruoli as $r)
                        <option value="{{ $r->value }}">{{ $r->label() }}</option>
                    @endforeach
                </select>
            </div>
            @if ($form['role'] === 'CAPO_REPARTO')
                <div>
                    <label for="store_id" class="label">Punto vendita *</label>
                    <select id="store_id" wire:model="form.store_id" class="input @error('form.store_id') input-error @enderror">
                        <option value="">Seleziona…</option>
                        @foreach ($puntiVendita as $pv)
                            <option value="{{ $pv->id }}">{{ $pv->code }} — {{ $pv->name }}</option>
                        @endforeach
                    </select>
                    @error('form.store_id') <p class="error"><span aria-hidden="true">⚠</span>{{ $message }}</p> @enderror
                </div>
            @endif
            <div>
                <label for="password" class="label">{{ $modificaId ? 'Nuova password (facoltativa)' : 'Password iniziale *' }}</label>
                <input id="password" type="password" wire:model="password" class="input" autocomplete="new-password">
                <p class="help">Almeno 10 caratteri con lettere e numeri. L'utente dovrà cambiarla al primo accesso.</p>
                @error('password') <p class="error"><span aria-hidden="true">⚠</span>{{ $message }}</p> @enderror
            </div>
            <label class="flex items-center gap-2 text-sm text-slate-700">
                <input type="checkbox" wire:model="form.is_active" class="rounded border-slate-300 text-mare-700 focus:ring-laguna-500">
                Attivo
            </label>

            <button type="button" wire:click="salva" class="btn-primary w-full">Salva</button>
        </div>
    </aside>
</div>
