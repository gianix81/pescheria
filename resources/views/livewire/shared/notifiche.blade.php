<div class="mx-auto max-w-3xl space-y-4">
    <div class="flex flex-wrap items-center justify-between gap-2">
        <label class="flex items-center gap-2 text-sm text-slate-700">
            <input type="checkbox" wire:model.live="soloNonLette" class="rounded border-slate-300 text-mare-700 focus:ring-laguna-500">
            Mostra solo non lette
        </label>
        <button type="button" wire:click="segnaTutteLette" class="btn-ghost">Segna tutte come lette</button>
    </div>

    @if ($notifiche->isEmpty())
        <x-vuoto titolo="Nessuna notifica" descrizione="Qui arrivano approvazioni, aperture, solleciti e riepiloghi." icona="🔔" />
    @else
        <ul class="space-y-2">
            @foreach ($notifiche as $n)
                <li @class(['card p-4', 'border-l-4 border-l-laguna-500' => ! $n->read_at])>
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="font-semibold text-slate-900">{{ $n->title }}</p>
                            @if ($n->body)<p class="mt-1 text-sm text-slate-600">{{ $n->body }}</p>@endif
                            <p class="mt-1 text-xs text-slate-500">
                                {{ $n->type->label() }} · {{ \App\Support\Format::dateTime($n->created_at) }}
                            </p>
                        </div>
                        <div class="flex shrink-0 flex-col items-end gap-1">
                            @if ($n->url)
                                <a href="{{ route('notifiche.open', $n) }}" class="text-sm font-semibold text-laguna-600 underline">Apri</a>
                            @endif
                            @if (! $n->read_at)
                                <button type="button" wire:click="segnaLetta({{ $n->id }})" class="text-xs text-slate-500 underline">Segna letta</button>
                            @endif
                        </div>
                    </div>
                </li>
            @endforeach
        </ul>

        <div>{{ $notifiche->links() }}</div>
    @endif
</div>
