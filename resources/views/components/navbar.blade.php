{{--
    Navbar Penyewa, selebar layar.
    Desktop : logo | kotak cari | HOME / RIWAYAT / PROFIL (huruf kapital lewat CSS) | nama akun + Keluar
    HP      : logo + ikon Keluar, kotak cari di baris kedua (menu pindah ke <x-nav-bawah>)
    Kotak cari mencari mal di beranda (GET /home?q=...), kota mengikuti pilihan di session.

    transparan=true (hanya beranda): navbar fixed di atas hero, transparan dengan teks & logo
    cream. Setelah halaman digulir, resources/js/navbar-transparan.js mengubah data-solid
    menjadi "true" sehingga navbar putih solid. Halaman lain: selalu solid (sticky).
    Menu (desktop) di posisi biasa (sebelah kotak cari) saat solid; saat transparan (paling atas)
    digeser ke tengah navbar sejauh --geser, yang dihitung navbar-transparan.js.
    Gaya kedua mode memakai varian group-data-[solid=false]/nav.
--}}
@props(['transparan' => false])

@php
    $menu = [
        ['label' => 'Home',       'url' => route('home'), 'aktif' => request()->routeIs('home')],
        ['label' => 'Riwayat',    'url' => route('riwayat'), 'aktif' => request()->routeIs('riwayat', 'riwayat.*')],
        ['label' => 'Profil',     'url' => route('profil'), 'aktif' => request()->routeIs('profil')],
    ];

    // Saat transparan (di atas foto hero): teks, ikon, dan cincin fokus berwarna cream.
    $fokus = 'focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus group-data-[solid=false]/nav:focus-visible:outline-on-primary';
@endphp

<header data-navbar
        @if ($transparan) data-transparan @endif
        data-solid="{{ $transparan ? 'false' : 'true' }}"
        class="group/nav z-30 border-b border-line bg-surface/95 backdrop-blur transition-[background-color,border-color,box-shadow,color] duration-250
               {{ $transparan
                   ? 'fixed inset-x-0 top-0 data-[solid=true]:shadow-sm data-[solid=false]:border-transparent data-[solid=false]:bg-transparent data-[solid=false]:backdrop-blur-none'
                   : 'sticky top-0' }}">
    <div class="flex w-full flex-wrap items-center gap-x-6 gap-y-3 px-4 py-3 md:h-20 md:flex-nowrap md:px-8 md:py-0 lg:px-12">
        <a href="{{ route('home') }}" class="shrink-0 rounded {{ $fokus }}">
            {{-- Tinggi 60px (desktop) / 42px (HP). Logo cream hanya dipakai saat transparan. --}}
            <span class="block {{ $transparan ? 'group-data-[solid=false]/nav:hidden' : '' }}">
                <x-logo :height="60" class="max-md:!h-[42px]" />
            </span>
            @if ($transparan)
                <span class="hidden group-data-[solid=false]/nav:block">
                    <x-logo :height="60" tone="on-primary" class="max-md:!h-[42px]" />
                </span>
            @endif
        </a>

        {{-- Keluar (HP) --}}
        <form method="POST" action="{{ route('logout') }}" class="ml-auto md:hidden">
            @csrf
            <button aria-label="Keluar"
                    class="grid h-10 w-10 place-items-center rounded-full text-primary hover:bg-surface-alt group-data-[solid=false]/nav:text-on-primary group-data-[solid=false]/nav:hover:bg-on-primary/15 {{ $fokus }}">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4M10 17l-5-5 5-5M5 12h12"/></svg>
            </button>
        </form>

        {{-- Kotak cari. Saat transparan: disembunyikan (HP) / tak terlihat & tak bisa difokus (desktop). --}}
        <form action="{{ route('home') }}" method="GET" role="search"
              class="order-last w-full md:order-none md:max-w-sm md:flex-1 max-md:group-data-[solid=false]/nav:hidden md:group-data-[solid=false]/nav:invisible">
            @if (request()->routeIs('home'))
                @foreach (['tipe', 'urut'] as $kunci)
                    @if (request($kunci))
                        <input type="hidden" name="{{ $kunci }}" value="{{ request($kunci) }}">
                    @endif
                @endforeach
            @endif
            <label for="cari-mal" class="sr-only">Cari mal atau lokasi parkir</label>
            <div class="flex items-center gap-2 rounded-full border border-line bg-page px-4 focus-within:border-focus focus-within:ring-2 focus-within:ring-focus/20">
                <svg class="h-4 w-4 shrink-0 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
                <input id="cari-mal" type="search" name="q" maxlength="100"
                       value="{{ request()->routeIs('home') ? request('q') : '' }}"
                       placeholder="Cari mal atau lokasi parkir…"
                       class="w-full bg-transparent py-2 text-sm text-primary outline-none placeholder:text-slate-400">
            </div>
        </form>

        {{-- Menu (desktop) --}}
        {{-- Huruf kapital lewat CSS (uppercase); teks asli tetap "Home" dsb. untuk pembaca layar.
             Semua menu berwarna primary (cream saat transparan); menu aktif diberi garis bawah. --}}
        <nav aria-label="Menu utama" data-menu-utama
             class="hidden items-center gap-6 text-sm font-bold uppercase tracking-wide md:flex md:transition-[translate] md:duration-500 md:ease-[cubic-bezier(.22,.61,.36,1)] md:group-data-[solid=false]/nav:translate-x-[var(--geser,0px)]">
            @foreach ($menu as $m)
                <a href="{{ $m['url'] }}" @if ($m['aktif']) aria-current="page" @endif
                   class="rounded-sm border-b-2 pb-0.5 text-primary group-data-[solid=false]/nav:text-on-primary {{ $fokus }} {{ $m['aktif']
                       ? 'border-primary group-data-[solid=false]/nav:border-on-primary'
                       : 'border-transparent hover:border-primary/30 group-data-[solid=false]/nav:hover:border-on-primary/50' }}">{{ $m['label'] }}</a>
            @endforeach
        </nav>

        {{-- Akun (desktop) --}}
        <div class="ml-auto hidden shrink-0 items-center gap-3 text-sm md:flex">
            <span class="max-w-[10rem] truncate text-slate-500 group-data-[solid=false]/nav:text-on-primary/85">{{ auth()->user()->name }}</span>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="rounded-lg border border-line px-4 py-1.5 font-medium hover:bg-surface-alt group-data-[solid=false]/nav:border-on-primary/50 group-data-[solid=false]/nav:text-on-primary group-data-[solid=false]/nav:hover:bg-on-primary/15 {{ $fokus }}">Keluar</button>
            </form>
        </div>
    </div>
</header>
