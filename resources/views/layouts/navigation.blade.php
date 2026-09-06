@php
    $user = Auth::user();
    $links = [];

    if ($user->hasRole(\App\Support\Role::SuperAdmin)) {
        $links = [
            ['label' => 'Beranda', 'route' => 'dashboard', 'match' => 'dashboard'],
            ['label' => 'Akun Ketua', 'route' => 'super-admin.ketua.index', 'match' => 'super-admin.ketua.*'],
        ];
    } elseif ($user->hasRole(\App\Support\Role::Ketua)) {
        $links = [
            ['label' => 'Beranda', 'route' => 'dashboard', 'match' => 'dashboard'],
            ['label' => 'Absensi', 'route' => 'ops.attendance.index', 'match' => 'ops.attendance.*'],
            ['label' => 'Setoran', 'route' => 'ops.setoran.index', 'match' => 'ops.setoran.*'],
            ['label' => 'Progress', 'route' => 'laporan.progress.index', 'match' => 'laporan.progress.*'],
            ['label' => 'Rekap', 'route' => 'laporan.attendance.index', 'match' => 'laporan.attendance.*'],
            ['label' => 'Tahun', 'route' => 'ketua.academic-years.index', 'match' => 'ketua.academic-years.*'],
            ['label' => 'Lokasi', 'route' => 'ketua.locations.index', 'match' => 'ketua.locations.*'],
            ['label' => 'Ustaz', 'route' => 'ketua.ustaz.index', 'match' => 'ketua.ustaz.*'],
            ['label' => 'Santri', 'route' => 'ketua.santri.index', 'match' => 'ketua.santri.*'],
            ['label' => 'Ortu', 'route' => 'ketua.orang-tua.index', 'match' => 'ketua.orang-tua.*'],
            ['label' => 'Halaqah', 'route' => 'ketua.halaqah.index', 'match' => 'ketua.halaqah.*'],
        ];
    } elseif ($user->hasRole(\App\Support\Role::Ustaz)) {
        $links = [
            ['label' => 'Beranda', 'route' => 'dashboard', 'match' => 'dashboard'],
            ['label' => 'Absensi', 'route' => 'ops.attendance.index', 'match' => 'ops.attendance.*'],
            ['label' => 'Setoran', 'route' => 'ops.setoran.index', 'match' => 'ops.setoran.*'],
            ['label' => 'Progress', 'route' => 'laporan.progress.index', 'match' => 'laporan.progress.*'],
            ['label' => 'Rekap', 'route' => 'laporan.attendance.index', 'match' => 'laporan.attendance.*'],
        ];
    } elseif ($user->hasRole(\App\Support\Role::Santri) || $user->hasRole(\App\Support\Role::OrangTua)) {
        $links = [
            ['label' => 'Beranda', 'route' => 'portal.home', 'match' => 'portal.*'],
        ];
    } else {
        $links = [
            ['label' => 'Beranda', 'route' => 'dashboard', 'match' => 'dashboard'],
        ];
    }

    $roleName = $user->getRoleNames()->first();
@endphp

<aside class="hidden lg:flex lg:flex-col lg:w-64 lg:shrink-0 bg-teal-800 text-teal-50">
    <div class="px-6 py-5 border-b border-teal-700">
        <p class="text-xs uppercase tracking-wide text-teal-200">KP SDS</p>
        <p class="font-semibold text-white">Monitoring Hafalan</p>
    </div>

    <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
        @foreach ($links as $link)
            <x-sidebar-link :href="route($link['route'])" :active="request()->routeIs($link['match'])">
                {{ $link['label'] }}
            </x-sidebar-link>
        @endforeach
    </nav>

    <div class="px-4 py-4 border-t border-teal-700">
        <p class="text-sm font-medium text-white truncate">{{ $user->name }}</p>
        <p class="text-xs text-teal-200">{{ $roleName ? \App\Support\Role::label($roleName) : '' }}</p>
        <div class="mt-3 space-y-1">
            <a href="{{ route('profile.edit') }}" class="block text-sm text-teal-100 hover:text-white">Profil</a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="text-sm text-teal-100 hover:text-white">Keluar</button>
            </form>
        </div>
    </div>
</aside>

<div class="lg:hidden sticky top-0 z-20 bg-teal-800 text-white px-4 py-3 flex items-center justify-between">
    <div>
        <p class="text-sm font-semibold">Monitoring Hafalan</p>
        <p class="text-xs text-teal-100">{{ $user->name }}</p>
    </div>
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="text-sm text-teal-100">Keluar</button>
    </form>
</div>

<nav class="lg:hidden fixed bottom-0 inset-x-0 z-20 bg-white border-t border-slate-200 overflow-x-auto">
    <div class="flex min-w-max">
        @foreach ($links as $link)
            <a href="{{ route($link['route']) }}"
               class="min-w-[4.5rem] flex flex-col items-center justify-center py-3 px-2 text-xs {{ request()->routeIs($link['match']) ? 'text-teal-700 font-semibold' : 'text-slate-500' }}">
                {{ $link['label'] }}
            </a>
        @endforeach
        <a href="{{ route('profile.edit') }}"
           class="min-w-[4.5rem] flex flex-col items-center justify-center py-3 px-2 text-xs {{ request()->routeIs('profile.*') ? 'text-teal-700 font-semibold' : 'text-slate-500' }}">
            Profil
        </a>
    </div>
</nav>
