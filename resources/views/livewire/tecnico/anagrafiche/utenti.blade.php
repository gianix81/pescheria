<div class="grid gap-5 lg:grid-cols-3">
    <div class="lg:col-span-2">

        {{-- Riepilogo per ruolo --}}
        <div class="mb-4 grid grid-cols-2 gap-3 sm:grid-cols-4">
            @foreach ($ruoli as $r)
                <div class="card px-3 py-2">
                    <p class="truncate text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $r->label() }}</p>
                    <p class="text-2xl font-bold text-slate-900">{{ $conteggiRuolo[$r->value] ?? 0 }}</p>
                </div>
            @endforeach
        </div>

        <div class="card mb-4 flex flex-wrap items-end gap-3 p-4">
            <div class="min-w-48 flex-1">
                <label for="ricerca" class="label">Cerca utente</label>
                <input id="ricerca" type="search" wire:model.live.debounce.400ms="ricerca" class="input py-2 text-sm"
                       placeholder="Nome, cognome o email">
            </div>
            <div>
                <label for="filtroRuolo" class="label">Ruolo</label>
                <select id="filtroRuolo" wire:model.live="filtroRuolo" class="input py-2 text-sm">
                    <option value="">Tutti</option>
                    @foreach ($ruoli as $r)
                        <option value="{{ $r->value }}">{{ $r->label() }}</option>
                    @endforeach
                </select>
            </div>
            @if ($puoGestireAccount)
                <label class="flex items-center gap-2 pb-2 text-sm text-slate-700">
                    <input type="checkbox" wire:model.live="mostraEliminati" class="rounded border-slate-300 text-mare-700 focus:ring-laguna-500">
                    Mostra eliminati
                </label>
            @endif
        </div>

        @error('eliminazione')
            <p class="error mb-3" role="alert"><span aria-hidden="true">⚠</span>{{ $message }}</p>
        @enderror

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
                    @forelse ($elenco as $u)
                        <tr class="hover:bg-slate-50">
                            <td class="td font-semibold">
                                {{ $u->full_name }}
                                @if ($u->id === auth()->id())
                                    <span class="ml-1 rounded bg-mare-700 px-1.5 py-0.5 text-[10px] font-bold uppercase text-white">Tu</span>
                                @endif
                            </td>
                            <td class="td">{{ $u->email }}</td>
                            <td class="td">
                                <span @class([
                                    'badge',
                                    'bg-mare-50 text-mare-800 ring-mare-300' => $u->role === \App\Enums\Role::ADMIN,
                                    'bg-slate-100 text-slate-700 ring-slate-300' => $u->role !== \App\Enums\Role::ADMIN,
                                ])>{{ $u->role->label() }}</span>
                            </td>
                            <td class="td">{{ $u->store?->code ?? '—' }}</td>
                            <td class="td">
                                @if ($u->trashed())
                                    <span class="badge bg-rose-50 text-rose-800 ring-rose-300">Eliminato</span>
                                @else
                                    <span class="badge {{ $u->is_active ? 'bg-emerald-50 text-emerald-800 ring-emerald-300' : 'bg-slate-200 text-slate-700 ring-slate-400' }}">
                                        {{ $u->is_active ? 'Attivo' : 'Disattivo' }}
                                    </span>
                                @endif
                            </td>
                            <td class="td whitespace-nowrap text-right">
                                @if ($u->trashed())
                                    @if ($puoGestireAccount)
                                        <button type="button" wire:click="ripristina({{ $u->id }})"
                                                class="font-semibold text-laguna-600 underline">Ripristina</button>
                                    @endif
                                @else
                                    <button type="button" wire:click="modifica({{ $u->id }})"
                                            class="font-semibold text-laguna-600 underline">Modifica</button>
                                    <button type="button" wire:click="attivaDisattiva({{ $u->id }})"
                                            class="ml-2 font-semibold text-slate-600 underline">
                                        {{ $u->is_active ? 'Disattiva' : 'Attiva' }}
                                    </button>
                                    @if ($puoGestireAccount)
                                        <button type="button" wire:click="reimpostaPassword({{ $u->id }})"
                                                wire:confirm="Generare una nuova password per {{ $u->full_name }}?"
                                                class="ml-2 font-semibold text-slate-600 underline">Password</button>
                                        <button type="button" wire:click="chiediEliminazione({{ $u->id }})"
                                                class="ml-2 font-semibold text-rose-700 underline">Elimina</button>
                                    @endif
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="td text-center text-slate-500">Nessun profilo con questi filtri.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $elenco->links() }}</div>
    </div>

    <aside class="card h-fit p-5 lg:sticky lg:top-20">
        <div class="flex items-center justify-between">
            <h3 class="text-sm font-bold uppercase tracking-wide text-slate-500">
                {{ $modificaId ? 'Modifica profilo' : 'Nuovo profilo' }}
            </h3>
            @if ($modificaId)
                <button type="button" wire:click="nuovo" class="text-xs font-semibold text-laguna-600 underline">Nuovo</button>
            @endif
        </div>

        @if ($passwordGenerata)
            <div class="mt-3 rounded-lg border border-amber-300 bg-amber-50 px-3 py-2 text-sm text-amber-900" role="status">
                <p class="font-semibold">Nuova password</p>
                <p class="mt-1 select-all break-all font-mono text-base">{{ $passwordGenerata }}</p>
                <p class="mt-1 text-xs">Consegnala all'utente: non sarà più visibile. Al primo accesso dovrà cambiarla.</p>
            </div>
        @endif

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
                <select id="role" wire:model.live="form.role" class="input @error('form.role') input-error @enderror">
                    @foreach ($ruoli as $r)
                        <option value="{{ $r->value }}">{{ $r->label() }}</option>
                    @endforeach
                </select>
                <p class="help">{{ \App\Enums\Role::from($form['role'])->description() }}</p>
                @error('form.role') <p class="error"><span aria-hidden="true">⚠</span>{{ $message }}</p> @enderror
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

            <button type="button" wire:click="salva" class="btn-primary w-full">Salva profilo</button>
        </div>
    </aside>

    {{-- Conferma eliminazione --}}
    @if ($confermaEliminazione)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4" role="dialog" aria-modal="true" aria-labelledby="titolo-elimina">
            <div class="card w-full max-w-md p-5">
                <h3 id="titolo-elimina" class="text-lg font-bold text-slate-900">Eliminare questo profilo?</h3>
                <p class="mt-2 text-sm text-slate-600">
                    L'utente non potrà più accedere. Lo storico dei suoi ordini resta consultabile e il profilo
                    può essere ripristinato dalla vista «Mostra eliminati».
                </p>
                @error('eliminazione') <p class="error"><span aria-hidden="true">⚠</span>{{ $message }}</p> @enderror
                <div class="mt-5 flex gap-3">
                    <button type="button" wire:click="$set('confermaEliminazione', null)" class="btn-ghost flex-1">Annulla</button>
                    <button type="button" wire:click="elimina" class="btn-danger flex-1">Elimina</button>
                </div>
            </div>
        </div>
    @endif
</div>
