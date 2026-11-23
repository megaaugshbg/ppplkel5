@extends('layouts.app')

@section('title', 'Beranda - Markir.In')

@section('content')
    <div class="mx-auto max-w-6xl space-y-6 px-4 pt-5 md:space-y-8 md:px-6 md:pt-8">

        @if (session('pesan'))
            <div role="status" class="rounded-2xl border border-line bg-surface-alt px-5 py-3 text-sm font-medium">{{ session('pesan') }}</div>
        @endif

        {{-- Sapaan + pemilih kota --}}
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <p class="text-sm text-slate-500">Halo, {{ \Illuminate\Support\Str::of(auth()->user()->name)->before(' ') }}! Mau parkir di mana?</p>
                <div class="mt-1">
                    <x-beranda.pemilih-kota :daftar-kota="$daftarKota" :kota="$kota" />
                </div>
            </div>
            <p id="status-lokasi" role="status" aria-live="polite" class="text-xs text-slate-500"></p>
        </div>

        {{-- Mal di kota terpilih --}}
        <section aria-labelledby="judul-mal" class="space-y-4">
            <div class="flex flex-wrap items-baseline justify-between gap-x-4 gap-y-1">
                <h1 id="judul-mal" class="text-xl font-bold md:text-2xl">
                    {{ $kota ? 'Mal di ' . $kota : 'Mal' }}
                </h1>
                @if ($q !== '')
                    <p class="text-sm text-slate-500">
                        Hasil untuk “<span class="font-semibold text-primary">{{ $q }}</span>”
                        &middot; <a href="{{ route('home', array_filter(['tipe' => $tipe, 'urut' => $urut])) }}" class="font-semibold text-primary underline underline-offset-4">Hapus pencarian</a>
                    </p>
                @elseif ($tabelSiap && $mal->isNotEmpty())
                    <p class="text-sm text-slate-500">{{ $mal->count() }} lokasi</p>
                @endif
            </div>

            <x-beranda.chip-filter :tipe="$tipe" :urut="$urut" :q="$q" :ada-posisi="$adaPosisi" :label-tipe="$labelTipe" />

            @if (! $tabelSiap || ! $kota)
                <div class="rounded-3xl border-2 border-dashed border-line bg-surface px-6 py-12 text-center">
                    <p class="text-base font-semibold">Data lahan parkir belum tersedia</p>
                    <p class="mt-1 text-sm text-slate-500">Silakan coba lagi nanti.</p>
                </div>
            @elseif ($mal->isEmpty())
                <div class="rounded-3xl border-2 border-dashed border-line bg-surface px-6 py-12 text-center">
                    <p class="text-base font-semibold">Tidak ada mal yang cocok</p>
                    <p class="mt-1 text-sm text-slate-500">
                        @if ($tipe)
                            Belum ada slot {{ $labelTipe[$tipe] }} yang kosong di {{ $kota }}.
                        @else
                            Coba kata kunci lain atau pilih kota lain.
                        @endif
                    </p>
                    <a href="{{ route('home') }}" class="mt-4 inline-block rounded-lg bg-primary px-5 py-2.5 text-sm font-semibold text-on-primary hover:bg-primary-hover focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus">Tampilkan semua mal</a>
                </div>
            @else
                {{-- HP: geser horizontal. Desktop: grid 3-4 kolom. --}}
                <ul class="-mx-4 flex snap-x snap-mandatory gap-4 overflow-x-auto px-4 pb-2 [scrollbar-width:none] md:mx-0 md:grid md:grid-cols-3 md:overflow-visible md:px-0 lg:grid-cols-4" aria-label="Daftar mal">
                    @foreach ($mal as $m)
                        <li class="flex snap-start">
                            <x-beranda.kartu-mal :mal="$m" :label-tipe="$labelTipe" :tipe="$tipe" />
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

        {{-- Riwayat --}}
        <div class="md:max-w-xl">
            <x-beranda.riwayat-terakhir :items="$riwayat" :url-semua="route('riwayat')" />
        </div>
    </div>
@endsection

@push('scripts')
<script>
    // GPS: minta izin otomatis SEKALI per sesi tab. Jika ditolak, tidak ditanya lagi di sesi itu.
    // Tombol "Gunakan lokasi saya" ([data-gps-ulang]) tetap bisa meminta ulang.
    (function () {
        var KUNCI = 'markirin.gps';
        var adaPosisi = @json($adaPosisi);
        var tabelSiap = @json($tabelSiap);
        var status = document.getElementById('status-lokasi');

        function baca() { try { return sessionStorage.getItem(KUNCI); } catch (e) { return 'tidak-bisa'; } }
        function tulis(v) { try { sessionStorage.setItem(KUNCI, v); } catch (e) {} }
        function info(teks) { if (status) status.textContent = teks; }

        // ulang: tombol "Gunakan lokasi saya" (kota ikut GPS). urutJarak: chip "Terdekat" (urut jarak).
        function minta(ulang, urutJarak) {
            var tampil = ulang || urutJarak;
            if (!('geolocation' in navigator)) { info('Browser tidak mendukung lokasi.'); return; }
            if (tampil) info('Mencari lokasimu…');
            navigator.geolocation.getCurrentPosition(function (p) {
                tulis('diizinkan');
                var u = new URL(@json(route('home')), window.location.origin);
                new URLSearchParams(window.location.search).forEach(function (v, k) { u.searchParams.set(k, v); });
                u.searchParams.set('lat', p.coords.latitude.toFixed(5));
                u.searchParams.set('lng', p.coords.longitude.toFixed(5));
                if (ulang) u.searchParams.set('gps_ulang', '1');
                if (urutJarak) u.searchParams.set('urut', 'jarak');
                window.location.assign(u.toString());
            }, function (err) {
                tulis('ditolak');
                if (tampil) {
                    info(err && err.code === 1
                        ? 'Izin lokasi ditolak. Aktifkan di pengaturan browser untuk melihat jarak.'
                        : 'Lokasimu belum bisa dibaca. Coba lagi beberapa saat.');
                }
            }, { timeout: 10000, maximumAge: 600000 });
        }

        if (tabelSiap && !adaPosisi && !baca()) {
            tulis('diminta'); // ditandai sebelum bertanya, supaya tidak bertanya lagi di sesi ini
            minta(false);
        }

        document.querySelectorAll('[data-terdekat]').forEach(function (b) {
            b.addEventListener('click', function () { minta(false, true); });
        });

        document.querySelectorAll('[data-gps-ulang]').forEach(function (b) {
            b.addEventListener('click', function () {
                var d = b.closest('details'); if (d) d.open = false;
                minta(true);
            });
        });

        // Tutup dropdown kota saat klik di luar.
        document.addEventListener('click', function (e) {
            document.querySelectorAll('[data-pemilih-kota][open]').forEach(function (d) {
                if (!d.contains(e.target)) d.open = false;
            });
        });
    })();
</script>
@endpush
