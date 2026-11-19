@extends('layouts.app')

@section('title', 'Home - Markir.In')

@section('content')
    {{-- HERO: foto parkiran + pencarian --}}
    <section class="relative isolate overflow-hidden bg-brand-900">
        {{-- FOTO: taruh di public/images/hero-parkir.jpg --}}
        <img src="{{ asset('images/hero-parkir.jpg') }}" alt="" class="absolute inset-0 -z-10 h-full w-full object-cover opacity-60" onerror="this.remove()">
        <div class="absolute inset-0 -z-10 bg-gradient-to-b from-brand-900/30 to-brand-900/80"></div>

        <div class="mx-auto flex max-w-6xl flex-col items-center px-6 pb-20 pt-8 text-center md:pb-28 md:pt-16">
            <form action="{{ url('/cari-parkir') }}" method="GET" class="flex w-full max-w-xl items-center gap-2 rounded-full bg-white px-4 py-2 shadow-lg">
                <svg class="h-5 w-5 shrink-0 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
                <input type="search" name="q" placeholder="Cari tempat parkir..." aria-label="Cari tempat parkir"
                       class="w-full bg-transparent py-1.5 text-sm outline-none placeholder:text-slate-400">
            </form>

            <h1 class="mt-10 text-3xl font-bold text-white md:text-5xl">Parkir tanpa muter-muter.</h1>
            <p class="mt-3 max-w-md text-white/85">Pilih mal, pilih lantai, bayar. Slot kamu sudah menunggu.</p>

            <a href="{{ url('/cari-parkir') }}"
               class="mt-8 inline-flex items-center gap-2 rounded-full bg-white px-7 py-3 text-sm font-semibold text-brand-900 shadow-lg transition hover:bg-brand-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white">
                <svg class="h-5 w-5 text-red-600" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2a7 7 0 0 0-7 7c0 5.2 7 13 7 13s7-7.8 7-13a7 7 0 0 0-7-7Zm0 9.5A2.5 2.5 0 1 1 12 6.500a2.500 2.500 0 0 1 0 5Z"/></svg>
                Cari Lokasi Parkir
            </a>
        </div>
    </section>

    {{-- PANEL PEMESANAN --}}
    <section class="relative z-10 mx-auto -mt-12 max-w-6xl px-4 md:-mt-16 md:px-6">
        <div class="grid gap-6 lg:grid-cols-3">

            <form action="#" method="GET" class="space-y-6 rounded-3xl bg-white p-6 shadow-lg md:p-8 lg:col-span-2">

                {{-- Plat nomor --}}
                <div>
                    <label for="plat" class="mb-1 block text-sm font-semibold">Plat nomor mobil</label>
                    <input id="plat" name="plat" placeholder="B 1233 AA" autocapitalize="characters"
                           class="w-full rounded-lg border border-slate-200 bg-brand-50 px-4 py-3 text-sm uppercase outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-200 md:max-w-xs">
                </div>

                {{-- Layanan --}}
                <fieldset>
                    <legend class="mb-3 text-sm font-semibold">Pilih layanan buat kamu!</legend>
                    <div class="grid grid-cols-2 gap-3 md:max-w-lg">
                        @foreach ([['inap', 'Parkir Inap'], ['mall', 'Parkir Mall']] as [$val, $label])
                            <label class="cursor-pointer">
                                <input type="radio" name="layanan" value="{{ $val }}" class="peer sr-only" @checked($val === 'mall')>
                                <span class="flex items-center gap-3 rounded-2xl border-2 border-brand-100 bg-brand-50 p-3 transition peer-checked:border-brand-500 peer-checked:bg-brand-100 peer-focus-visible:outline-2 peer-focus-visible:outline-brand-900">
                                    {{-- IKON: ganti dengan gambar di public/images/ikon-{{ $val }}.png jika ada --}}
                                    <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-brand-900 text-2xl font-black text-white">P</span>
                                    <span class="text-sm font-semibold leading-tight">{{ $label }}</span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                </fieldset>

                {{-- Destinasi --}}
                <fieldset>
                    <legend class="mb-3 text-sm font-semibold">Pilih destinasi kamu!</legend>
                    <div class="grid gap-3 sm:grid-cols-3">
                        @foreach ($destinasi as $i => $d)
                            <label class="cursor-pointer">
                                <input type="radio" name="destinasi" value="{{ $d['nama'] }}" class="peer sr-only" @checked($i === 1)>
                                <span class="flex items-center gap-3 rounded-xl border-2 border-brand-100 bg-white p-3 transition peer-checked:border-brand-500 peer-checked:bg-brand-50 peer-focus-visible:outline-2 peer-focus-visible:outline-brand-900">
                                    <svg class="h-7 w-7 shrink-0 text-red-600" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2a7 7 0 0 0-7 7c0 5.2 7 13 7 13s7-7.8 7-13a7 7 0 0 0-7-7Zm0 9.5A2.5 2.5 0 1 1 12 6.500a2.500 2.500 0 0 1 0 5Z"/></svg>
                                    <span class="text-sm leading-tight">
                                        <strong class="block font-semibold">{{ $d['nama'] }}</strong>
                                        <span class="text-xs text-slate-500">{{ $d['kota'] }}</span>
                                    </span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                </fieldset>

                <button type="submit"
                        class="w-full rounded-lg bg-brand-500 px-6 py-3 text-sm font-semibold text-white transition hover:bg-brand-600 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-900 md:w-auto">
                    Pilih layanan
                </button>
            </form>

            {{-- Riwayat terakhir --}}
            <aside class="rounded-3xl bg-white p-6 shadow-lg md:p-8">
                <div class="flex items-center gap-3">
                    <svg class="h-9 w-9 text-brand-900" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M3 12a9 9 0 1 0 3-6.700L3 8"/><path d="M3 3v5h5M12 7v5l3 2"/></svg>
                    <h2 class="text-base font-semibold">Riwayat terakhir</h2>
                </div>

                {{-- Nanti diganti data dari database --}}
                <div class="mt-5 rounded-2xl border border-brand-100 bg-brand-50 p-4">
                    <p class="text-xs text-slate-500">Selasa, September 2021</p>
                    <p class="mt-1 text-4xl font-bold">F3</p>
                    <p class="mt-1 text-xs text-slate-500">AEON Mall Bogor</p>
                </div>

                <a href="#" class="mt-4 inline-block text-sm font-semibold underline underline-offset-4">Lihat semua riwayat</a>
            </aside>
        </div>
    </section>
@endsection
