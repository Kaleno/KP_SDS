<x-guest-layout>
    <p class="mb-4 text-sm text-slate-600">
        Terima kasih sudah mendaftar. Silakan verifikasi email lewat tautan yang kami kirim. Jika belum sampai, kami bisa mengirim ulang.
    </p>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-4 rounded-xl bg-teal-50 px-4 py-3 text-sm font-medium text-teal-800">
            Tautan verifikasi baru sudah dikirim ke email Anda.
        </div>
    @endif

    <div class="mt-4 flex items-center justify-between gap-3">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <x-primary-button>
                Kirim ulang email
            </x-primary-button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="text-sm font-semibold text-slate-500 hover:text-teal-800">
                Keluar
            </button>
        </form>
    </div>
</x-guest-layout>
