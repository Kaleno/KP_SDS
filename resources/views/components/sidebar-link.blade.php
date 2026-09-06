@props(['active' => false])

@php
$classes = $active
    ? 'nav-item nav-item-active'
    : 'nav-item nav-item-idle';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
