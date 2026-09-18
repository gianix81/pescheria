<div class="space-y-5">
    <div class="card flex flex-wrap items-end gap-3 p-4">
        <div class="min-w-48 flex-1">
            <label for="ricerca" class="label">Cerca</label>
            <input id="ricerca" type="search" wire:model.live.debounce.400ms="ricerca" class="input py-2 text-sm">
        </div>
        <div>
            <label for="azione" class="label">Azione</label>
            <select id="azione" wire:model.live="azione" class="input py-2 text-sm">
                <option value="">Tutte</option>
                @foreach ($azioni as $a)
                    <option value="{{ $a }}">{{ $a }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <p class="text-xs text-slate-500">Il log è immutabile: le voci non possono essere modificate né eliminate.</p>

    <div class="card overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
                <tr>
                    <th scope="col" class="th">Data/ora</th>
                    <th scope="col" class="th">Utente</th>
                    <th scope="col" class="th">Azione</th>
                    <th scope="col" class="th">Oggetto</th>
                    <th scope="col" class="th">Dettagli</th>
                    <th scope="col" class="th">IP</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach ($voci as $voce)
                    <tr class="hover:bg-slate-50">
                        <td class="td whitespace-nowrap">{{ \App\Support\Format::dateTime($voce->created_at, 'd/m/Y H:i:s') }}</td>
                        <td class="td">{{ $voce->user?->full_name ?? 'sistema' }}</td>
                        <td class="td font-mono text-xs">{{ $voce->action }}</td>
                        <td class="td text-xs">{{ class_basename($voce->auditable_type ?? '') }} {{ $voce->auditable_id }}</td>
                        <td class="td text-xs text-slate-600">
                            @foreach (($voce->payload ?? []) as $chiave => $valore)
                                <span class="mr-2"><strong>{{ $chiave }}:</strong> {{ is_array($valore) ? json_encode($valore, JSON_UNESCAPED_UNICODE) : $valore }}</span>
                            @endforeach
                        </td>
                        <td class="td text-xs">{{ $voce->ip_address }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div>{{ $voci->links() }}</div>
</div>
