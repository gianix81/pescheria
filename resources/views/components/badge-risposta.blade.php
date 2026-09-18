@props(['stato', 'compatto' => false])
<span {{ $attributes->merge(['class' => 'badge '.$stato->badgeClasses()]) }} title="{{ $stato->label() }}">
    <span aria-hidden="true">{{ $stato->icon() }}</span>
    <span @class(['sr-only' => $compatto])>{{ $compatto ? $stato->label() : $stato->shortLabel() }}</span>
</span>
