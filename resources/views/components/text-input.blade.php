@props(['disabled' => false])

@php
    $isPassword = $attributes->get('type') === 'password';
@endphp

@if ($isPassword)
    <div
        x-data="{ show: false }"
        {{ $attributes->only('class')->merge(['class' => 'ui-password']) }}
    >
        <input
            @disabled($disabled)
            {{ $attributes->except('class')->merge(['class' => 'ui-input']) }}
            :type="show ? 'text' : 'password'"
        >
        <button
            type="button"
            class="ui-password-toggle text-slate-400 hover:text-teal-800 focus:text-teal-800 focus:outline-none"
            x-on:click="show = ! show"
            :aria-label="show ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'"
            :aria-pressed="show ? 'true' : 'false'"
        >
            <x-icon name="eye" class="h-5 w-5" x-show="! show" />
            <x-icon name="eye-slash" class="h-5 w-5" x-show="show" x-cloak />
        </button>
    </div>
@elseif ($attributes->get('type') === 'date')
    <input
        @disabled($disabled)
        lang="id"
        {{ $attributes->merge(['class' => 'ui-input']) }}
    >
@else
    <input @disabled($disabled) {{ $attributes->merge(['class' => 'ui-input']) }}>
@endif
