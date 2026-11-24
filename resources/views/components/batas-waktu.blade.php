{{-- Batas waktu parkir: waktu check in + durasi yang dibayar, lengkap dengan hitung mundur. --}}
@props(['pesanan'])

@php
    $batas = null;
    try {
        if (! empty($pesanan['waktu_checkin'])) {
            $masuk = \Carbon\Carbon::createFromFormat('d-m-Y H:i', $pesanan['waktu_checkin'], 'Asia/Jakarta');
            $batas = $masuk->copy()->addHours(max(1, (int) ($pesanan['durasi'] ?? 1)));
        }
    } catch (\Throwable $e) {
        $batas = null;
    }
@endphp

@if ($batas)
    <section class="rounded-2xl border border-primary bg-primary-soft p-4 text-center" aria-labelledby="judul-batas-waktu">
        <h2 id="judul-batas-waktu" class="text-sm font-semibold text-primary">Batas waktu parkir</h2>
        <p class="mt-1 text-2xl font-bold tabular-nums text-primary"
           data-batas-waktu="{{ $batas->timestamp * 1000 }}"
           aria-live="off">--:--:--</p>
        <p class="mt-1 text-xs text-slate-600">
            Berlaku sampai <strong>{{ $batas->format('d-m-Y H:i') }} WIB</strong>
            ({{ (int) $pesanan['durasi'] }} jam sejak check in)
        </p>
        <p data-batas-pesan class="mt-1 text-xs font-medium text-amber-700"></p>
    </section>

    @once
        @push('scripts')
        <script>
        (function () {
            const el = document.querySelector('[data-batas-waktu]');
            if (!el) return;
            const pesan = document.querySelector('[data-batas-pesan]');
            const batas = parseInt(el.dataset.batasWaktu, 10);
            const dua = (n) => String(n).padStart(2, '0');

            function tampil() {
                const sisa = batas - Date.now();
                if (sisa <= 0) {
                    el.textContent = '00:00:00';
                    pesan.textContent = 'Waktu parkir sudah habis. Segera lakukan check-out.';
                    return false;
                }
                const detik = Math.floor(sisa / 1000);
                el.textContent = dua(Math.floor(detik / 3600)) + ':' + dua(Math.floor(detik % 3600 / 60)) + ':' + dua(detik % 60);
                pesan.textContent = sisa <= 10 * 60 * 1000 ? 'Waktu hampir habis (kurang dari 10 menit).' : '';
                return true;
            }

            if (tampil()) {
                const timer = setInterval(function () { if (!tampil()) clearInterval(timer); }, 1000);
            }
        })();
        </script>
        @endpush
    @endonce
@endif
