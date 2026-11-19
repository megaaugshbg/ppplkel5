@extends('layouts.app')

@section('title', 'Woman Parking - Markir.In')

@section('content')
    {{-- HEADER --}}
    <section class="bg-primary">
        <div class="mx-auto flex max-w-6xl flex-col gap-6 px-6 pb-20 pt-10 md:flex-row md:items-end md:justify-between md:pb-24 md:pt-14">
            <div class="max-w-xl">
                <span class="inline-flex items-center gap-2 rounded-full bg-on-primary/15 px-3 py-1 text-xs font-semibold text-on-primary">
                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2a5 5 0 1 0 0 10 5 5 0 0 0 0-10Zm-1 11v3H8v2h3v4h2v-4h3v-2h-3v-3h-2Z"/></svg>
                    Khusus perempuan
                </span>
                <h1 class="mt-4 text-3xl font-bold text-on-primary md:text-5xl">Woman Parking</h1>
                <p class="mt-3 text-on-primary/85">
                    Slot parkir di area yang dekat pintu masuk, terang, dan lebih mudah diawasi.
                </p>
            </div>

            <a href="/woman-parking/create"
               class="inline-flex items-center justify-center gap-2 rounded-lg bg-surface px-6 py-3 text-sm font-semibold text-primary shadow transition hover:bg-surface-alt focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-on-primary">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
                Tambah slot
            </a>
        </div>
    </section>

    <section class="relative z-10 mx-auto -mt-12 max-w-6xl px-4 md:-mt-14 md:px-6">

        @if (session('sukses'))
            <div role="status" class="mb-4 flex items-center gap-3 rounded-2xl bg-surface px-5 py-4 text-sm font-medium text-emerald-700 shadow-lg">
                <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path d="m5 12 5 5 9-10"/></svg>
                {{ session('sukses') }}
            </div>
        @endif

        {{-- Ringkasan --}}
        <div class="grid grid-cols-3 gap-3 md:gap-4">
            <div class="rounded-3xl bg-surface p-4 shadow-lg md:p-6">
                <p class="text-xs text-slate-500 md:text-sm">Total slot</p>
                <p class="mt-1 text-3xl font-bold md:text-4xl">{{ $slots->count() }}</p>
            </div>
            <div class="rounded-3xl bg-surface p-4 shadow-lg md:p-6">
                <p class="text-xs text-slate-500 md:text-sm">Tersedia</p>
                <p class="mt-1 text-3xl font-bold text-emerald-600 md:text-4xl">{{ $slots->where('status', 'tersedia')->count() }}</p>
            </div>
            <div class="rounded-3xl bg-surface p-4 shadow-lg md:p-6">
                <p class="text-xs text-slate-500 md:text-sm">Terisi</p>
                <p class="mt-1 text-3xl font-bold text-rose-600 md:text-4xl">{{ $slots->where('status', 'terisi')->count() }}</p>
            </div>
        </div>

        {{-- Daftar slot --}}
        @if ($slots->isEmpty())
            <div class="mt-6 rounded-3xl border-2 border-dashed border-line bg-surface px-6 py-14 text-center">
                <p class="text-4xl font-black text-primary/20">P</p>
                <h2 class="mt-3 text-base font-semibold">Belum ada slot parkir khusus perempuan</h2>
                <p class="mt-1 text-sm text-slate-500">Tambahkan slot pertama untuk mulai.</p>
                <a href="/woman-parking/create"
                   class="mt-5 inline-block rounded-lg bg-primary px-6 py-3 text-sm font-semibold text-on-primary transition hover:bg-primary-hover">
                    Tambah slot
                </a>
            </div>
        @else
            <ul class="mt-6 grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($slots as $slot)
                    <li class="flex flex-col rounded-3xl bg-surface p-5 shadow-lg">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-primary text-lg font-black text-on-primary">
                                    {{ $slot->kode_slot }}
                                </span>
                                <div class="leading-tight">
                                    <h2 class="text-sm font-semibold">{{ $slot->lokasi }}</h2>
                                    <p class="mt-0.5 text-xs text-slate-500">Rp {{ number_format($slot->tarif, 0, ',', '.') }} / jam</p>
                                </div>
                            </div>

                            @if ($slot->status === 'tersedia')
                                <span class="shrink-0 rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">Tersedia</span>
                            @else
                                <span class="shrink-0 rounded-full bg-rose-100 px-3 py-1 text-xs font-semibold text-rose-700">Terisi</span>
                            @endif
                        </div>

                        @if ($slot->catatan)
                            <p class="mt-4 rounded-xl bg-surface-alt px-3 py-2 text-xs text-slate-600">{{ $slot->catatan }}</p>
                        @endif
                    </li>
                @endforeach
            </ul>
        @endif
    </section>
@endsection
