@props(['active' => false])

@php
$classes = $active
    ? 'flex items-center rounded-lg px-3 py-2 text-sm font-semibold bg-teal-700 text-white'
    : 'flex items-center rounded-lg px-3 py-2 text-sm font-medium text-teal-100 hover:bg-teal-700 hover:text-white';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
