<x-app-layout>
    <x-slot name="header">
        <h1 class="text-lg font-semibold text-slate-800">Orang tua: {{ $parent->name }}</h1>
    </x-slot>

    <div class="max-w-3xl space-y-6">
        <form method="POST" action="{{ route('ketua.orang-tua.update', $parent) }}" class="bg-white rounded-xl border border-slate-200 p-5 grid gap-4">
            @csrf
            @method('PUT')
            <div>
                <x-input-label for="name" value="Nama" />
                <x-text-input id="name" name="name" class="mt-1 block w-full" :value="old('name', $parent->name)" required />
            </div>
            <div>
                <x-input-label for="username" value="Username" />
                <x-text-input id="username" name="username" class="mt-1 block w-full" :value="old('username', $parent->username)" required />
            </div>
            <div>
                <x-input-label for="email" value="Email" />
                <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $parent->email)" />
            </div>
            <div>
                <x-input-label for="phone" value="Telepon" />
                <x-text-input id="phone" name="phone" class="mt-1 block w-full" :value="old('phone', $parent->phone)" />
            </div>
            <div>
                <x-input-label for="password" value="Kata sandi baru (opsional)" />
                <x-text-input id="password" name="password" type="password" class="mt-1 block w-full" />
            </div>
            <div>
                <x-input-label for="password_confirmation" value="Ulangi kata sandi" />
                <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full" />
            </div>
            <div>
                <x-primary-button>Simpan akun</x-primary-button>
            </div>
        </form>

        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <h2 class="font-semibold">Tautkan anak (NIS)</h2>
            <form method="POST" action="{{ route('ketua.orang-tua.attach-child', $parent) }}" class="mt-4 flex flex-col sm:flex-row gap-3">
                @csrf
                <x-text-input name="nis" class="block w-full" placeholder="Masukkan NIS" required />
                <x-primary-button>Tautkan</x-primary-button>
            </form>
            <x-input-error class="mt-2" :messages="$errors->get('nis')" />

            <ul class="mt-4 divide-y divide-slate-100">
                @forelse ($parent->children as $child)
                    <li class="py-3 flex items-center justify-between gap-3">
                        <div>
                            <p class="font-medium">{{ $child->user->name }}</p>
                            <p class="text-sm text-slate-500">NIS {{ $child->nis }}</p>
                        </div>
                        <form method="POST" action="{{ route('ketua.orang-tua.detach-child', [$parent, $child]) }}">
                            @csrf
                            @method('DELETE')
                            <button class="text-sm text-rose-700 hover:underline">Lepas</button>
                        </form>
                    </li>
                @empty
                    <li class="py-3 text-sm text-slate-500">Belum ada anak tertaut.</li>
                @endforelse
            </ul>
        </div>
    </div>
</x-app-layout>
