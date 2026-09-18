<div class="space-y-5">
    <div class="card flex flex-wrap items-end gap-3 p-4">
        <div>
            <label for="stato" class="label">Stato risposta</label>
            <select id="stato" wire:model.live="stato" class="input py-2 text-sm">
                <option value="">Tutti</option>
                @foreach ($stati as $s)
                    <option value="{{ $s->value }}">{{ $s->label() }}</option>
                @endforeach
            </select>
        </div>
        <div class="min-w-64">
            <label for="opportunita" class="label">Opportunità</label>
            <select id="opportunita" wire:model.live="opportunita" class="input py-2 text-sm">
                <option value="">Tutte</option>
                @foreach ($opportunitaElenco as $o)
                    <option value="{{ $o->id }}">{{ $o->reference }} — {{ $o->description }}</option>
                @endforeach
            </select>
        </div>
    </div>

    @error('correzione') <p class="error" role="alert"><span aria-hidden="true">⚠</span>{{ $message }}</p> @enderror

    @if ($risposte->isEmpty())
        <x-vuoto titolo="Nessuna risposta" descrizione="Le risposte compaiono qui appena i punti vendita iniziano a rispondere." />
    @else
        <div class="card overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th scope="col" class="th">Opportunità</th>
                        <th scope="col" class="th">Punto vendita</th>
                        <th scope="col" class="th">Stato</th>
                        <th scope="col" class="th">Colli</th>
                        <th scope="col" class="th">Kg</th>
                        <th scope="col" class="th">Inviata</th>
                        <th scope="col" class="th"><span class="sr-only">Azioni</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($risposte as $r)
                        <tr class="hover:bg-slate-50">
                            <td class="td">
                                <a href="{{ route('opportunita.show', $r->opportunity) }}" class="font-semibold text-mare-700 underline">
                                    {{ $r->opportunity->reference }}
                                </a>
                                <span class="block text-xs text-slate-500">{{ $r->opportunity->description }}</span>
                            </td>
                            <td class="td">{{ $r->store->code }}</td>
                            <td class="td"><x-badge-risposta :stato="$r->status" /></td>
                            <td class="td font-semibold">{{ $r->packages }}</td>
                            <td class="td">{{ \App\Support\Format::decimal($r->kg, 2) }}</td>
                            <td class="td">{{ $r->submitted_at ? \App\Support\Format::dateTime($r->submitted_at) : '—' }}</td>
                            <td class="td text-right">
                                <button type="button" wire:click="apriCorrezione({{ $r->id }})"
                                        class="font-semibold text-laguna-600 underline">Correggi</button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div>{{ $risposte->links() }}</div>
    @endif

    @if ($correzioneId)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4" role="dialog" aria-modal="true" aria-labelledby="titolo-correzione">
            <div class="card w-full max-w-md p-5">
                <h3 id="titolo-correzione" class="text-lg font-bold text-slate-900">Correzione eccezionale</h3>
                <p class="mt-1 text-sm text-slate-600">
                    Modifica la quantità dopo la scadenza. L'intervento resta tracciato come correzione del Buyer.
                </p>

                <div class="mt-3">
                    <label for="correzioneColli" class="label">Colli</label>
                    <input id="correzioneColli" type="number" min="0" wire:model="correzioneColli" class="input">
                </div>

                <div class="mt-3">
                    <label for="correzioneMotivo" class="label">Motivazione *</label>
                    <textarea id="correzioneMotivo" rows="3" wire:model="correzioneMotivo" class="input py-2"></textarea>
                </div>

                <div class="mt-5 flex gap-3">
                    <button type="button" wire:click="$set('correzioneId', null)" class="btn-ghost flex-1">Annulla</button>
                    <button type="button" wire:click="salvaCorrezione" class="btn-primary flex-1">Salva correzione</button>
                </div>
            </div>
        </div>
    @endif
</div>
