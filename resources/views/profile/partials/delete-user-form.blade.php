<section class="space-y-5">
    <header>
        <h2 class="font-display text-lg font-semibold text-rose-800">Hapus akun</h2>
        <p class="mt-1 text-sm text-slate-500">
            Setelah akun dihapus, seluruh datanya hilang permanen. Pastikan Anda sudah menyimpan informasi yang diperlukan.
        </p>
    </header>

    <x-danger-button
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
    >Hapus akun</x-danger-button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
            @csrf
            @method('delete')

            <h2 class="font-display text-lg font-semibold text-teal-950">
                Yakin ingin menghapus akun?
            </h2>

            <p class="mt-2 text-sm text-slate-500">
                Masukkan kata sandi untuk mengonfirmasi penghapusan permanen.
            </p>

            <div class="mt-6">
                <x-input-label for="password" value="Kata sandi" class="sr-only" />

                <x-text-input
                    id="password"
                    name="password"
                    type="password"
                    class="mt-1 block w-full sm:w-3/4"
                    placeholder="Kata sandi"
                />

                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
            </div>

            <div class="mt-6 flex justify-end gap-2">
                <x-secondary-button x-on:click="$dispatch('close')">
                    Batal
                </x-secondary-button>

                <x-danger-button>
                    Hapus akun
                </x-danger-button>
            </div>
        </form>
    </x-modal>
</section>
