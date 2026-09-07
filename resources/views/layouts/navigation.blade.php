@php
    $user = Auth::user();
    $links = [];

    if ($user->hasRole(\App\Support\Role::SuperAdmin)) {
        $links = [
            ['label' => 'Beranda', 'route' => 'dashboard', 'match' => 'dashboard', 'icon' => 'home', 'group' => 'Utama', 'primary' => true],
            ['label' => 'Akun Ketua', 'route' => 'super-admin.ketua.index', 'match' => 'super-admin.ketua.*', 'icon' => 'shield', 'group' => 'Sistem', 'primary' => true],
        ];
    } elseif ($user->hasRole(\App\Support\Role::Ketua)) {
        $links = [
            ['label' => 'Beranda', 'route' => 'dashboard', 'match' => 'dashboard', 'icon' => 'home', 'group' => 'Utama', 'primary' => true],
            ['label' => 'Absensi', 'route' => 'ops.attendance.index', 'match' => 'ops.attendance.*', 'icon' => 'check-circle', 'group' => 'Operasional', 'primary' => true],
            ['label' => 'Setoran', 'route' => 'ops.setoran.index', 'match' => 'ops.setoran.*', 'icon' => 'book', 'group' => 'Operasional', 'primary' => true],
            ['label' => 'Progress', 'route' => 'laporan.progress.index', 'match' => 'laporan.progress.*', 'icon' => 'chart', 'group' => 'Laporan', 'primary' => false],
            ['label' => 'Rekap', 'route' => 'laporan.attendance.index', 'match' => 'laporan.attendance.*', 'icon' => 'clipboard', 'group' => 'Laporan', 'primary' => false],
            ['label' => 'Siapkan', 'route' => 'ketua.setup.create', 'match' => 'ketua.setup.*', 'icon' => 'spark', 'group' => 'Master data', 'primary' => false],
            ['label' => 'Tahun', 'route' => 'ketua.academic-years.index', 'match' => 'ketua.academic-years.*', 'icon' => 'calendar', 'group' => 'Master data', 'primary' => false],
            ['label' => 'Lokasi', 'route' => 'ketua.locations.index', 'match' => 'ketua.locations.*', 'icon' => 'map', 'group' => 'Master data', 'primary' => false],
            ['label' => 'Ustaz', 'route' => 'ketua.ustaz.index', 'match' => 'ketua.ustaz.*', 'icon' => 'academic', 'group' => 'Master data', 'primary' => false],
            ['label' => 'Santri', 'route' => 'ketua.santri.index', 'match' => 'ketua.santri.*', 'icon' => 'users', 'group' => 'Master data', 'primary' => false],
            ['label' => 'Ortu', 'route' => 'ketua.orang-tua.index', 'match' => 'ketua.orang-tua.*', 'icon' => 'user', 'group' => 'Master data', 'primary' => false],
            ['label' => 'Halaqah', 'route' => 'ketua.halaqah.index', 'match' => 'ketua.halaqah.*', 'icon' => 'layers', 'group' => 'Master data', 'primary' => false],
        ];
    } elseif ($user->hasRole(\App\Support\Role::Ustaz)) {
        $links = [
            ['label' => 'Beranda', 'route' => 'dashboard', 'match' => 'dashboard', 'icon' => 'home', 'group' => 'Utama', 'primary' => true],
            ['label' => 'Absensi', 'route' => 'ops.attendance.index', 'match' => 'ops.attendance.*', 'icon' => 'check-circle', 'group' => 'Operasional', 'primary' => true],
            ['label' => 'Setoran', 'route' => 'ops.setoran.index', 'match' => 'ops.setoran.*', 'icon' => 'book', 'group' => 'Operasional', 'primary' => true],
            ['label' => 'Progress', 'route' => 'laporan.progress.index', 'match' => 'laporan.progress.*', 'icon' => 'chart', 'group' => 'Laporan', 'primary' => false],
            ['label' => 'Rekap', 'route' => 'laporan.attendance.index', 'match' => 'laporan.attendance.*', 'icon' => 'clipboard', 'group' => 'Laporan', 'primary' => false],
        ];
    } elseif ($user->hasRole(\App\Support\Role::Santri) || $user->hasRole(\App\Support\Role::OrangTua)) {
        $links = [
            ['label' => 'Beranda', 'route' => 'portal.home', 'match' => 'portal.*', 'icon' => 'home', 'group' => 'Utama', 'primary' => true],
        ];
    } else {
        $links = [
            ['label' => 'Beranda', 'route' => 'dashboard', 'match' => 'dashboard', 'icon' => 'home', 'group' => 'Utama', 'primary' => true],
        ];
    }

    $roleName = $user->getRoleNames()->first();
    $grouped = collect($links)->groupBy('group');
    $primaryLinks = collect($links)->where('primary', true)->values();
    $needsMore = collect($links)->contains(fn ($link) => ! $link['primary']);
    $mobileCols = $needsMore ? 4 : min(4, $primaryLinks->count() + 1);
    $moreActive = collect($links)->contains(fn ($link) => ! $link['primary'] && request()->routeIs($link['match']))
        || request()->routeIs('profile.*');
    $initials = collect(explode(' ', $user->name))->map(fn ($part) => mb_substr($part, 0, 1))->take(2)->implode('');
@endphp

<aside class="hidden lg:flex lg:flex-col lg:w-72 lg:shrink-0 lg:sticky lg:top-0 lg:h-screen bg-teal-950 text-teal-50">
    <div class="px-6 py-6 flex items-center gap-3">
        <x-application-logo class="h-11 w-11 shrink-0" />
        <div class="min-w-0">
            <p class="text-[11px] uppercase tracking-[0.2em] text-gold-300">KP SDS</p>
            <p class="font-display text-lg leading-tight text-white truncate">Monitoring Hafalan</p>
        </div>
    </div>

    <nav class="flex-1 px-3 pb-4 space-y-5 overflow-y-auto">
        @foreach ($grouped as $group => $items)
            <div>
                <p class="px-3 mb-2 text-[10px] font-semibold uppercase tracking-[0.18em] text-teal-200/50">{{ $group }}</p>
                <div class="space-y-1">
                    @foreach ($items as $link)
                        <x-sidebar-link :href="route($link['route'])" :active="request()->routeIs($link['match'])">
                            <x-icon :name="$link['icon']" class="h-5 w-5 shrink-0 opacity-80" />
                            <span>{{ $link['label'] }}</span>
                        </x-sidebar-link>
                    @endforeach
                </div>
            </div>
        @endforeach
    </nav>

    <div class="mx-3 mb-4 rounded-2xl bg-white/5 p-4">
        <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gold-300 text-sm font-bold text-teal-950">{{ $initials }}</div>
            <div class="min-w-0">
                <p class="text-sm font-semibold text-white truncate">{{ $user->name }}</p>
                <p class="text-xs text-teal-200/80">{{ $roleName ? \App\Support\Role::label($roleName) : '' }}</p>
            </div>
        </div>
        <div class="mt-4 grid grid-cols-2 gap-2">
            <a href="{{ route('profile.edit') }}" class="btn-secondary min-h-10 text-xs bg-white/10 border-white/10 text-white hover:bg-white/15">Profil</a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn-secondary min-h-10 w-full text-xs bg-white/10 border-white/10 text-white hover:bg-white/15">Keluar</button>
            </form>
        </div>
    </div>
</aside>

<div class="lg:hidden fixed top-0 inset-x-0 z-30 px-3 pt-3">
    <div class="flex items-center justify-between rounded-2xl bg-teal-950/95 px-3 py-2.5 text-white shadow-lift backdrop-blur">
        <div class="flex items-center gap-2.5 min-w-0">
            <x-application-logo class="h-9 w-9 shrink-0" />
            <div class="min-w-0">
                <p class="text-sm font-semibold truncate">Monitoring Hafalan</p>
                <p class="text-[11px] text-teal-100/80 truncate">{{ $user->name }}</p>
            </div>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="rounded-xl px-3 py-2 text-xs font-semibold text-teal-100 hover:bg-white/10">Keluar</button>
        </form>
    </div>
</div>

<nav class="lg:hidden fixed bottom-0 inset-x-0 z-30 border-t border-teal-950/5 bg-white/95 backdrop-blur-md" style="padding-bottom: env(safe-area-inset-bottom)">
    <div class="grid {{ 'grid-cols-'.$mobileCols }}">
        @foreach ($primaryLinks->take(3) as $link)
            <a href="{{ route($link['route']) }}"
               class="flex flex-col items-center justify-center gap-1 py-2.5 text-[11px] {{ request()->routeIs($link['match']) ? 'text-teal-800 font-semibold' : 'text-slate-500' }}">
                <x-icon :name="$link['icon']" class="h-5 w-5" />
                {{ $link['label'] }}
            </a>
        @endforeach
        @if ($needsMore)
            <button type="button" @click="menuOpen = true"
                    class="flex flex-col items-center justify-center gap-1 py-2.5 text-[11px] {{ $moreActive ? 'text-teal-800 font-semibold' : 'text-slate-500' }}">
                <x-icon name="dots" class="h-5 w-5" />
                Menu
            </button>
        @else
            <a href="{{ route('profile.edit') }}"
               class="flex flex-col items-center justify-center gap-1 py-2.5 text-[11px] {{ request()->routeIs('profile.*') ? 'text-teal-800 font-semibold' : 'text-slate-500' }}">
                <x-icon name="user" class="h-5 w-5" />
                Profil
            </a>
        @endif
    </div>
</nav>

<div x-show="menuOpen" x-cloak class="lg:hidden fixed inset-0 z-40" style="display: none;">
    <div class="absolute inset-0 bg-teal-950/40" @click="menuOpen = false"></div>
    <div class="absolute inset-x-0 bottom-0 rounded-t-3xl bg-cream-50 p-5 shadow-lift"
         x-show="menuOpen"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="translate-y-full"
         x-transition:enter-end="translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="translate-y-0"
         x-transition:leave-end="translate-y-full">
        <div class="mx-auto mb-4 h-1.5 w-12 rounded-full bg-slate-300"></div>
        <p class="font-display text-lg text-teal-950">Menu</p>
        <p class="text-sm text-slate-500 mb-4">{{ $roleName ? \App\Support\Role::label($roleName) : '' }}</p>
        <div class="grid grid-cols-2 gap-2">
            @foreach ($links as $link)
                <a href="{{ route($link['route']) }}"
                   class="flex items-center gap-2 rounded-2xl border px-3 py-3 text-sm font-medium {{ request()->routeIs($link['match']) ? 'border-teal-700 bg-teal-800 text-white' : 'border-slate-200 bg-white text-slate-700' }}">
                    <x-icon :name="$link['icon']" class="h-4 w-4 shrink-0" />
                    {{ $link['label'] }}
                </a>
            @endforeach
            <a href="{{ route('profile.edit') }}"
               class="flex items-center gap-2 rounded-2xl border px-3 py-3 text-sm font-medium {{ request()->routeIs('profile.*') ? 'border-teal-700 bg-teal-800 text-white' : 'border-slate-200 bg-white text-slate-700' }}">
                <x-icon name="user" class="h-4 w-4 shrink-0" />
                Profil
            </a>
        </div>
        <button type="button" class="btn-secondary btn-block mt-4" @click="menuOpen = false">Tutup</button>
    </div>
</div>
