@props(['stato'])
{{-- Stato sempre con testo, mai solo colore (WCAG 1.4.1) --}}
<span {{ $attributes->merge(['class' => 'badge '.$stato->badgeClasses()]) }}>
    {{ $stato->label() }}
</span>
