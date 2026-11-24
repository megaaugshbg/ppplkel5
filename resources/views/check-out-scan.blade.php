{{-- Halaman Check-out: scan QR/barcode booking (use case Lakukan Check-out, langkah 2). --}}
@extends('layouts.app')

@section('title', 'Check-out - Markir.In')

@section('content')
<div class="mx-auto max-w-xl space-y-5 px-4 pt-5 md:px-6 md:pt-8">

    <a href="{{ route('check-out') }}" class="inline-flex items-center gap-1 text-sm font-semibold hover:opacity-80 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
        Kembali ke booking aktif
    </a>

    <div>
        <h1 class="text-xl font-bold md:text-2xl">Check-out</h1>
        <p class="text-sm text-slate-500">Scan QR booking di gerbang keluar untuk menyelesaikan parkir.</p>
    </div>

    @if (session('error'))
        <div role="alert" class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-medium text-amber-800">
            {{ session('error') }}
            @if (session('error') === 'Booking ini sudah check-out.')
                <a href="{{ route('check-out.struk') }}" class="ml-1 font-semibold underline underline-offset-4">Lihat struk</a>
            @endif
        </div>
    @endif

    {{-- Slot yang sedang dipakai --}}
    <section class="flex items-center gap-4 rounded-2xl border border-line bg-surface p-4">
        <span class="grid h-14 w-14 shrink-0 place-items-center rounded-2xl bg-primary text-lg font-bold text-on-primary">{{ $pesanan['slot'] }}</span>
        <div class="min-w-0">
            <p class="truncate text-base font-semibold">{{ $pesanan['lokasi'] }}</p>
            <p class="text-xs text-slate-500">{{ $pesanan['tipe'] }} &middot; masuk {{ $pesanan['waktu_checkin'] ?? '-' }} WIB</p>
            <p class="mt-1 text-xs text-slate-500">Kode booking: <strong class="tracking-wide text-primary">{{ $pesanan['kode'] }}</strong></p>
        </div>
    </section>

    <form id="form-checkout" method="POST" action="{{ route('check-out.proses') }}" class="space-y-4 rounded-2xl border border-line bg-surface p-4 sm:p-5">
        @csrf

        {{-- Scanner kamera --}}
        <div>
            <button type="button" id="btn-kamera"
                    class="flex w-full items-center justify-center gap-2 rounded-xl border border-line px-4 py-3 text-sm font-semibold text-primary hover:bg-surface-alt focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus">
                Buka kamera
            </button>
            <div id="reader" class="mt-3 hidden overflow-hidden rounded-xl"></div>
            <p id="pesan-kamera" role="status" class="mt-2 text-xs text-amber-700"></p>
        </div>

        <div>
            <label for="barcode" class="mb-1 block text-sm font-medium">Kode barcode</label>
            <input id="barcode" name="barcode" value="{{ old('barcode') }}" autocomplete="off" maxlength="20"
                   placeholder="Contoh: MKR-000123" aria-describedby="ket-barcode"
                   class="w-full rounded-lg border border-line bg-page px-4 py-3 text-sm uppercase tracking-wide outline-none transition focus:border-focus focus:ring-2 focus:ring-focus/20">
            <p id="ket-barcode" class="mt-1 text-xs text-slate-500">Scan QR dengan kamera, atau ketik kode booking secara manual (format MKR- diikuti 6 angka, mis. MKR-000123).</p>
        </div>

        <label class="flex items-center gap-2 text-xs text-slate-500">
            <input type="checkbox" name="koneksi_putus" value="1" class="accent-[var(--color-primary)]">
            Simulasikan koneksi terputus
        </label>

        <button type="submit"
                class="flex w-full items-center justify-center gap-2 rounded-xl bg-primary px-4 py-3 text-sm font-semibold text-on-primary hover:bg-primary-hover focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7V5a1 1 0 0 1 1-1h2M17 4h2a1 1 0 0 1 1 1v2M20 17v2a1 1 0 0 1-1 1h-2M7 20H5a1 1 0 0 1-1-1v-2M7 8v8M10 8v8M13 8v8M17 8v8"/></svg>
            {{ session('bisa_ulang') ? 'Coba lagi' : 'Check-out' }}
        </button>
    </form>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
(function () {
    const btn = document.getElementById('btn-kamera');
    const reader = document.getElementById('reader');
    const pesan = document.getElementById('pesan-kamera');
    const input = document.getElementById('barcode');
    const form = document.getElementById('form-checkout');
    const FORMAT = /^MKR-\d{6}$/;
    let scanner = null;
    let sedangProses = false;

    async function berhenti() {
        if (scanner) {
            try { await scanner.stop(); scanner.clear(); } catch (e) {}
            scanner = null;
        }
        reader.classList.add('hidden');
        btn.textContent = 'Buka kamera';
    }

    btn.addEventListener('click', async function () {
        if (scanner) { await berhenti(); return; }

        if (typeof Html5Qrcode === 'undefined') {
            pesan.textContent = 'Scanner gagal dimuat. Gunakan input kode manual di bawah.';
            return;
        }

        pesan.textContent = '';
        sedangProses = false;
        reader.classList.remove('hidden');
        scanner = new Html5Qrcode('reader');

        try {
            await scanner.start(
                { facingMode: 'environment' },
                { fps: 10, qrbox: { width: 220, height: 220 } },
                async function (teks) {
                    if (sedangProses) return;
                    const kode = teks.trim().toUpperCase();

                    if (!FORMAT.test(kode)) {
                        pesan.textContent = 'QR tidak valid. Arahkan ke QR booking (MKR-xxxxxx).';
                        return;
                    }

                    sedangProses = true;
                    input.value = kode;
                    await berhenti();
                    form.requestSubmit();
                },
                function () { /* frame tanpa QR, abaikan */ }
            );
            btn.textContent = 'Tutup kamera';
        } catch (e) {
            scanner = null;
            reader.classList.add('hidden');
            pesan.textContent = 'Kamera tidak bisa dibuka. Izinkan akses kamera, atau ketik kode manual di bawah.';
        }
    });
})();
</script>
@endpush
