{{--
    Navbar Penyewa.
    Desktop : logo | kotak cari | Home / Riwayat / Pengaturan | nama akun + Keluar
    HP      : logo + ikon Keluar, kotak cari di baris kedua (menu pindah ke <x-nav-bawah>)
    Kotak cari mencari mal di beranda (GET /home?q=...), kota mengikuti pilihan di session.
--}}
@php
    $menu = [
        ['label' => 'Home',       'url' => route('home'), 'aktif' => request()->routeIs('home')],
        ['label' => 'Riwayat',    'url' => route('riwayat'), 'aktif' => request()->routeIs('riwayat', 'riwayat.*')],
        ['label' => 'Pengaturan', 'url' => route('profil'), 'aktif' => request()->routeIs('profil')],
    ];
@endphp

<header class="sticky top-0 z-30 border-b border-line bg-surface/95 backdrop-blur">
    <div class="mx-auto flex max-w-6xl flex-wrap items-center gap-x-6 gap-y-3 px-4 py-3 md:h-16 md:flex-nowrap md:px-6 md:py-0">
        <a href="{{ route('home') }}" class="shrink-0 rounded focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-focus">
            <x-logo :height="40" />
        </a>

        {{-- Keluar (HP) --}}
        <form method="POST" action="{{ route('logout') }}" class="ml-auto md:hidden">
            @csrf
            <button aria-label="Keluar" class="grid h-10 w-10 place-items-center rounded-full text-primary hover:bg-surface-alt focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4M10 17l-5-5 5-5M5 12h12"/></svg>
            </button>
        </form>

        {{-- Kotak cari --}}
        <form action="{{ route('home') }}" method="GET" role="search" class="order-last w-full md:order-none md:max-w-sm md:flex-1">
            @if (request()->routeIs('home') && request('tipe'))
                <input type="hidden" name="tipe" value="{{ request('tipe') }}">
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
        <nav aria-label="Menu utama" class="hidden items-center gap-6 text-sm font-medium md:flex">
            @foreach ($menu as $m)
                <a href="{{ $m['url'] }}" @if ($m['aktif']) aria-current="page" @endif
                   class="{{ $m['aktif'] ? 'border-b-2 border-primary pb-0.5 text-primary' : 'text-slate-500 hover:text-primary' }}">{{ $m['label'] }}</a>
            @endforeach
        </nav>

        {{-- Akun (desktop) --}}
        <div class="ml-auto hidden shrink-0 items-center gap-3 text-sm md:flex">
            <span class="max-w-[10rem] truncate text-slate-500">{{ auth()->user()->name }}</span>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="rounded-lg border border-line px-4 py-1.5 font-medium hover:bg-surface-alt focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus">Keluar</button>
            </form>
        </div>
    </div>
</header>
