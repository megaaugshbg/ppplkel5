@extends('layouts.app')

@section('title', 'Beranda - Markir.In')

@section('navbar-transparan', '1')

@section('content')
    {{-- ============================== HERO ============================== --}}
    @php
        // Foto latar mengikuti kota aktif: public/images/kota/{slug}.jpg (mis. surabaya.jpg).
        // Tidak ada filenya / kota kosong -> foto cadangan CITO.jpg.
        $fotoHero = 'images/mal/CITO.jpg';
        if ($kota) {
            $calon = 'images/kota/' . \Illuminate\Support\Str::slug($kota) . '.jpg';
            if (is_file(public_path($calon))) {
                $fotoHero = $calon;
            }
        }
    @endphp
    <section aria-labelledby="sapaan">
        {{-- Foto latar selebar layar, tinggi wadah tetap (tanpa layout shift).
             Latar primary = cadangan bila foto gagal dimuat. --}}
        <div class="relative isolate h-[360px] overflow-hidden bg-primary md:h-[500px]">
            {{-- Muncul halus (±400ms) setelah foto selesai dimuat; reduced-motion diatur global di transisi.css. --}}
            <img src="{{ asset($fotoHero) }}" alt=""
                 loading="eager" fetchpriority="high" decoding="async" data-foto-hero
                 onload="this.dataset.termuat = ''"
                 class="absolute inset-0 -z-10 h-full w-full object-cover opacity-0 transition-opacity duration-400 ease-out data-termuat:opacity-100">
            <noscript><style>[data-foto-hero] { opacity: 1 !important; }</style></noscript>
            {{-- Lapisan warna primary agar teks cream terbaca di foto kota mana pun.
                 Kontras diukur (WCAG, piksel latar terang persentil 95) pada CITO.jpg dan
                 semua foto di images/kota: judul >= 4.2:1, sub-teks >= 4.7:1, menu >= 6.7:1.
                 Foto baru/diganti = ukur ulang kontrasnya. --}}
            <div aria-hidden="true">
                {{-- 1. Rata di seluruh hero. --}}
                <div class="absolute inset-0 bg-primary/55"></div>
                {{-- 2. Gradasi tipis di atas: kontras menu navbar transparan. --}}
                <div class="absolute inset-x-0 top-0 h-32 bg-gradient-to-b from-primary/45 to-transparent"></div>
                {{-- 3. Makin pekat dari 40% tinggi ke bawah, menuju kartu pencarian. --}}
                <div class="absolute inset-0 bg-gradient-to-b from-transparent from-40% to-primary/40"></div>
            </div>

            <div class="relative flex h-full flex-col items-center justify-center px-4 pb-24 pt-16 text-center md:pb-32 md:pt-20">
                <h1 id="sapaan" class="text-4xl font-bold tracking-tight text-on-primary drop-shadow-md md:text-6xl">
                    Hey, {{ \Illuminate\Support\Str::of(auth()->user()->name)->before(' ') }}
                </h1>
                <p class="mt-3 text-base font-medium text-on-primary drop-shadow md:text-xl">Mau parkir di mana hari ini?</p>
            </div>
        </div>

        {{-- Kartu pencarian: menumpang di tepi bawah hero. --}}
        <div class="relative z-20 -mt-20 px-4 md:-mt-24 md:px-8 lg:px-12">
            <div class="mx-auto max-w-3xl rounded-3xl border border-line bg-surface p-4 shadow-xl shadow-[var(--color-shadow)] md:p-5">
                <div class="flex flex-col gap-3 md:flex-row md:items-center">
                    <div class="md:shrink-0 md:border-r md:border-line md:pr-4">
                        <x-beranda.pemilih-kota :daftar-kota="$daftarKota" :kota="$kota" />
                    </div>

                    {{-- Sama dengan kotak cari di navbar: GET ke beranda, filter tipe/urut dipertahankan. --}}
                    <form action="{{ route('home') }}" method="GET" role="search" class="flex flex-1 flex-col gap-3 sm:flex-row sm:items-center">
                        @foreach (['tipe' => $tipe, 'urut' => $urut] as $kunci => $nilai)
                            @if ($nilai)
                                <input type="hidden" name="{{ $kunci }}" value="{{ $nilai }}">
                            @endif
                        @endforeach
                        <label for="cari-mal-hero" class="sr-only">Cari mal atau lokasi parkir</label>
                        <div class="flex flex-1 items-center gap-2 rounded-2xl border border-line bg-page px-4 focus-within:border-focus focus-within:ring-2 focus-within:ring-focus/20">
                            <svg class="h-5 w-5 shrink-0 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
                            <input id="cari-mal-hero" type="search" name="q" maxlength="100" value="{{ $q }}"
                                   placeholder="Cari mal atau lokasi parkir…"
                                   class="w-full bg-transparent py-3 text-sm text-primary outline-none placeholder:text-slate-400">
                        </div>
                        <button type="submit"
                                class="rounded-2xl bg-primary px-7 py-3 text-sm font-semibold text-on-primary hover:bg-primary-hover focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus">
                            Cari
                        </button>
                    </form>
                </div>

                <div class="mt-4 border-t border-line pt-4">
                    <x-beranda.chip-filter :tipe="$tipe" :urut="$urut" :q="$q" :ada-posisi="$adaPosisi" :label-tipe="$labelTipe" />
                    <p id="status-lokasi" role="status" aria-live="polite" class="mt-2 text-xs text-slate-500 empty:hidden"></p>
                </div>
            </div>
        </div>
    </section>

    {{-- ============================== ISI ============================== --}}
    <div class="space-y-6 px-4 pt-8 md:space-y-8 md:px-8 md:pt-10 lg:px-12">

        @if (session('pesan'))
            <div role="status" class="mx-auto max-w-3xl rounded-2xl border border-line bg-surface-alt px-5 py-3 text-sm font-medium">{{ session('pesan') }}</div>
        @endif

        {{-- Mal di kota terpilih --}}
        <section aria-labelledby="judul-mal" class="space-y-4">
            <div class="flex flex-wrap items-baseline justify-between gap-x-4 gap-y-1">
                <h2 id="judul-mal" class="text-xl font-bold md:text-2xl">
                    {{ $kota ? 'Mal di ' . $kota : 'Mal' }}
                </h2>
                @if ($q !== '')
                    <p class="text-sm text-slate-500">
                        Hasil untuk “<span class="font-semibold text-primary">{{ $q }}</span>”
                        &middot; <a href="{{ route('home', array_filter(['tipe' => $tipe, 'urut' => $urut])) }}" class="font-semibold text-primary underline underline-offset-4">Hapus pencarian</a>
                    </p>
                @elseif ($tabelSiap && $mal->isNotEmpty())
                    <p class="text-sm text-slate-500">{{ $mal->count() }} lokasi</p>
                @endif
            </div>

            @if (! $tabelSiap || ! $kota)
                <div class="max-w-3xl rounded-3xl border-2 border-dashed border-line bg-surface px-6 py-12 text-center">
                    <p class="text-base font-semibold">Data lahan parkir belum tersedia</p>
                    <p class="mt-1 text-sm text-slate-500">Silakan coba lagi nanti.</p>
                </div>
            @elseif ($mal->isEmpty())
                <div class="max-w-3xl rounded-3xl border-2 border-dashed border-line bg-surface px-6 py-12 text-center">
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
                {{-- Carousel di semua ukuran layar (resources/js/carousel.js). Track selebar layar
                     supaya kartu terakhir tampak terpotong di kanan sebagai petunjuk bisa digeser.
                     Bila kartu cukup banyak, JS menggandakan daftar (klon aria-hidden) sehingga
                     carousel berputar tanpa ujung. --}}
                <div class="relative" data-carousel>
                    <ul data-carousel-track tabindex="0" aria-label="Daftar mal"
                        class="-mx-4 flex snap-x snap-mandatory scroll-px-4 gap-4 overflow-x-auto px-4 pb-4 pt-1 outline-none [scrollbar-width:none] focus-visible:ring-2 focus-visible:ring-focus/40 md:-mx-8 md:scroll-px-8 md:px-8 lg:-mx-12 lg:scroll-px-12 lg:px-12 [&::-webkit-scrollbar]:hidden">
                        @foreach ($mal as $m)
                            {{-- *:!w-full: kartu mengikuti lebar <li> (kartu sendiri mengunci 16.5rem di HP). --}}
                            <li style="--urutan: {{ min($loop->index, 4) }}" class="flex w-[20rem] max-w-[calc(100vw-3rem)] shrink-0 snap-start *:!w-full md:w-[22rem] lg:w-[24rem]">
                                <x-beranda.kartu-mal :mal="$m" :label-tipe="$labelTipe" :tipe="$tipe" />
                            </li>
                        @endforeach
                    </ul>

                    {{-- Panah (desktop). Mode berputar: selalu tampil & aktif. Tanpa putaran
                         (kartu sedikit): disembunyikan di ujung carousel. --}}
                    @php
                        $panah = 'absolute top-1/2 z-10 hidden h-11 w-11 -translate-y-1/2 place-items-center rounded-full bg-surface text-primary shadow-lg ring-1 ring-line hover:bg-surface-alt focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus disabled:pointer-events-none disabled:opacity-0 md:grid';
                    @endphp
                    <button type="button" data-carousel-prev aria-label="Geser ke kiri" class="{{ $panah }} -left-2 lg:-left-4">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
                    </button>
                    <button type="button" data-carousel-next aria-label="Geser ke kanan" class="{{ $panah }} -right-2 lg:-right-4">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" aria-hidden="true"><path d="m9 6 6 6-6 6"/></svg>
                    </button>
                </div>
            @endif
        </section>

        {{-- Riwayat (lebar nyaman) --}}
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
