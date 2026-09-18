<x-mail::message>
# {{ $notifica->title }}

@if ($notifica->body)
{{ $notifica->body }}
@endif

@if ($notifica->url)
<x-mail::button :url="$notifica->url">
Apri la scheda nell'app
</x-mail::button>

L'ordine è valido solo dopo la conferma nell'applicazione.
@endif

Grazie,<br>
{{ config('app.name') }}
</x-mail::message>
