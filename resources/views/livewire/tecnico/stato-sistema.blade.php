@php
    $ok = fn (bool $condizione) => $condizione
        ? ['✓', 'text-emerald-800', 'bg-emerald-50 border-emerald-200']
        : ['⚠', 'text-rose-800', 'bg-rose-50 border-rose-200'];

    $schedulerAttivo = $scheduler !== null && $schedulerMinuti !== null && $schedulerMinuti <= 5;
    $mediaOk = $media['persistente'] === true;
@endphp

<div class="mx-auto max-w-3xl space-y-4">

    <p class="text-sm text-slate-600">
        Questa pagina dice in che stato si trova l'ambiente in questo momento. Quando qualcosa non
        torna, si guarda prima qui.
    </p>

    {{-- Pubblicazione --}}
    <section class="card p-4">
        <h2 class="text-xs font-bold uppercase tracking-wide text-slate-500">Pubblicazione</h2>
        <dl class="mt-2 grid gap-2 text-sm sm:grid-cols-2">
            <div class="flex justify-between gap-3">
                <dt class="text-slate-600">Versione in linea</dt>
                <dd class="font-mono font-semibold text-slate-900">{{ $versione }}</dd>
            </div>
            <div class="flex justify-between gap-3">
                <dt class="text-slate-600">Ambiente</dt>
                <dd class="font-semibold text-slate-900">{{ $ambiente }}</dd>
            </div>
        </dl>
        <p class="help">Confronta la versione con l'ultimo commit del repository: se differiscono, la pubblicazione non è arrivata.</p>
    </section>

    {{-- Database --}}
    @php [$icona, $testo, $sfondo] = $ok($databaseOk && $migrazioni === 0 && ! $demo); @endphp
    <section class="card border {{ $sfondo }} p-4">
        <h2 class="text-xs font-bold uppercase tracking-wide text-slate-500">Database</h2>
        <p class="mt-1 text-sm font-semibold {{ $testo }}">
            <span aria-hidden="true">{{ $icona }}</span>
            @if ($demo)
                Modalità dimostrativa attiva: i dati sono temporanei
            @elseif (! $databaseOk)
                Database non raggiungibile
            @elseif ($migrazioni > 0)
                {{ $migrazioni }} migrazioni non applicate
            @else
                Collegato e allineato
            @endif
        </p>

        @if ($migrazioni > 0)
            <p class="help">Esegui <code class="rounded bg-white px-1">php artisan migrate --force</code> e imposta il pre-deploy step, altrimenti resterà indietro a ogni pubblicazione.</p>
        @endif

        <p class="mt-3 text-[11px] font-semibold uppercase tracking-wide text-slate-500">Utenti attivi</p>
        <dl class="mt-1 grid gap-1.5 text-sm sm:grid-cols-2">
            @foreach ($utenti as $ruolo => $quanti)
                <div class="flex justify-between gap-2">
                    <dt class="truncate text-slate-600">{{ $ruolo }}</dt>
                    <dd class="font-semibold text-slate-900">{{ $quanti }}</dd>
                </div>
            @endforeach
        </dl>

        <p class="mt-3 text-[11px] font-semibold uppercase tracking-wide text-slate-500">Anagrafiche</p>
        <dl class="mt-1 grid gap-1.5 text-sm sm:grid-cols-2">
            <div class="flex justify-between gap-2">
                <dt class="text-slate-600">Negozi registrati</dt>
                <dd class="font-semibold text-slate-900">{{ $puntiVendita }}</dd>
            </div>
            <div class="flex justify-between gap-2">
                <dt class="text-slate-600">Opportunità</dt>
                <dd class="font-semibold text-slate-900">{{ $opportunita }}</dd>
            </div>
        </dl>
    </section>

    {{-- Media: tre stati, perché «non ancora determinabile» non è un problema --}}
    @php
        [$icona, $testo, $sfondo] = match (true) {
            $media['persistente'] === true && $media['mancanti'] === 0 => ['✓', 'text-emerald-800', 'bg-emerald-50 border-emerald-200'],
            $media['persistente'] === false || $media['mancanti'] > 0 => ['⚠', 'text-rose-800', 'bg-rose-50 border-rose-200'],
            default => ['ⓘ', 'text-slate-700', 'bg-white border-slate-200'],
        };
    @endphp
    <section class="card border {{ $sfondo }} p-4">
        <h2 class="text-xs font-bold uppercase tracking-wide text-slate-500">Foto e video</h2>
        <p class="mt-1 text-sm font-semibold {{ $testo }}">
            <span aria-hidden="true">{{ $icona }}</span>
            Disco persistente: {{ $media['descrizione'] }}
        </p>

        <dl class="mt-2 grid gap-1.5 text-sm sm:grid-cols-2">
            <div class="flex justify-between gap-3">
                <dt class="text-slate-600">File registrati</dt>
                <dd class="font-semibold text-slate-900">{{ $media['registrati'] }}</dd>
            </div>
            <div class="flex justify-between gap-3">
                <dt class="text-slate-600">File non trovati</dt>
                <dd class="font-semibold {{ $media['mancanti'] > 0 ? 'text-rose-800' : 'text-slate-900' }}">{{ $media['mancanti'] }}</dd>
            </div>
            <div class="sm:col-span-2">
                <dt class="text-slate-600">Percorso</dt>
                <dd class="break-all font-mono text-xs text-slate-700">{{ $media['percorso'] }}</dd>
            </div>
        </dl>

        @if ($media['persistente'] === false)
            <p class="help">
                Serve un volume sul servizio dell'applicazione. Se lo monti su un percorso dedicato,
                indicalo con <code class="rounded bg-slate-100 px-1">MEDIA_ROOT</code>; il percorso qui
                sopra deve corrispondere.
            </p>
        @elseif ($media['persistente'] === null)
            <p class="help">
                Il contrassegno di questa pubblicazione è stato appena scritto e non c'è ancora nulla
                con cui confrontarlo. Dopo il prossimo rilascio questa riga dirà «sì» se il disco regge.
            </p>
        @endif
    </section>

    {{-- Automatismi --}}
    @php [$icona, $testo, $sfondo] = $ok($schedulerAttivo); @endphp
    <section class="card border {{ $sfondo }} p-4">
        <h2 class="text-xs font-bold uppercase tracking-wide text-slate-500">Automatismi</h2>
        <p class="mt-1 text-sm font-semibold {{ $testo }}">
            <span aria-hidden="true">{{ $icona }}</span>
            @if ($scheduler === null)
                Lo scheduler non è mai stato eseguito
            @elseif ($schedulerAttivo)
                Attivo — ultima esecuzione {{ $schedulerMinuti === 0 ? 'meno di un minuto fa' : $schedulerMinuti.' minuti fa' }}
            @else
                Fermo da {{ $schedulerMinuti }} minuti (ultima: {{ \App\Support\Format::dateTime($scheduler) }})
            @endif
        </p>

        @unless ($schedulerAttivo)
            <p class="help">
                Senza, le opportunità programmate non si aprono, quelle scadute restano aperte e non
                partono solleciti né riepiloghi. Serve un servizio con
                <code class="rounded bg-white px-1">php artisan schedule:work</code>, oppure un cron
                esterno che chiami <code class="rounded bg-white px-1">/cron/esegui</code> ogni minuto.
            </p>
        @endunless
    </section>
</div>
