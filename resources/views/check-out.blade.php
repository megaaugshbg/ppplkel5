{{-- Halaman Booking Aktif (use case Lakukan Check-out, langkah 1). --}}
@extends('layouts.app')

@section('title', 'Booking Aktif - Markir.In')

@section('content')
<div class="mx-auto max-w-xl space-y-5 px-4 pt-5 md:px-6 md:pt-8">

    <div>
        <h1 class="text-xl font-bold md:text-2xl">Booking Aktif</h1>
        <p class="text-sm text-slate-500">Booking yang sedang berjalan.</p>
    </div>

    @if (session('error'))
        <div role="alert" class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-medium text-amber-800">{{ session('error') }}</div>
    @endif

    @if (! $pesanan)
        {{-- Tidak ada booking aktif --}}
        <div class="rounded-3xl border-2 border-dashed border-line bg-surface px-6 py-10 text-center">
            <p class="text-base font-semibold">Tidak ada booking aktif</p>
            <p class="mt-1 text-sm text-slate-500">Pesan slot parkir dari beranda untuk memulai.</p>
            <div class="mt-5 flex flex-col justify-center gap-2 sm:flex-row">
                <a href="{{ route('home') }}"
                   class="rounded-xl bg-primary px-5 py-2.5 text-sm font-semibold text-on-primary hover:bg-primary-hover focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus">
                    Cari parkir
                </a>
                @if ($struk)
                    <a href="{{ route('check-out.struk') }}"
                       class="rounded-xl border border-line px-5 py-2.5 text-sm font-medium text-primary hover:bg-surface-alt focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus">
                        Lihat struk terakhir ({{ $struk['kode'] }})
                    </a>
                @endif
            </div>
        </div>
    @else
        @if (! empty($pesanan['contoh']))
            <div role="status" class="rounded-2xl border border-line bg-surface-alt px-4 py-3 text-sm">
                Ini <strong>data contoh</strong>, jadi status slot di database tidak ikut diubah.
            </div>
        @endif

        {{-- Kartu booking yang sedang parkir --}}
        <section class="flex items-center gap-4 rounded-2xl border border-line bg-surface p-4">
            <span class="grid h-14 w-14 shrink-0 place-items-center rounded-2xl bg-primary text-lg font-bold text-on-primary">{{ $pesanan['slot'] }}</span>
            <div class="min-w-0 flex-1">
                <p class="text-xs text-slate-500">{{ $pesanan['kode'] }}</p>
                <p class="truncate text-base font-semibold">{{ $pesanan['lokasi'] }}</p>
                <p class="text-xs text-slate-500">{{ $pesanan['tipe'] }} &middot; masuk {{ $pesanan['waktu_checkin'] ?? '-' }} WIB</p>
            </div>
            <span class="shrink-0 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">Sedang parkir</span>
        </section>

        <x-rincian-pesanan :pesanan="$pesanan">
            <div class="flex justify-between gap-4 p-3">
                <dt class="text-slate-500">Waktu masuk</dt>
                <dd class="text-right">{{ $pesanan['waktu_checkin'] ?? '-' }} WIB</dd>
            </div>
            <div class="flex justify-between gap-4 p-3">
                <dt class="text-slate-500">Dibayar</dt>
                <dd class="text-right">Rp {{ number_format($pesanan['total'] ?? 0, 0, ',', '.') }} ({{ $pesanan['durasi'] ?? '-' }} jam)</dd>
            </div>
        </x-rincian-pesanan>

        <a href="{{ route('check-out.scan') }}"
           class="block w-full rounded-xl bg-primary px-4 py-3 text-center text-sm font-semibold text-on-primary hover:bg-primary-hover focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus">
            Parkir Berlangsung
        </a>
        <p class="text-center text-xs text-slate-500">Pilih saat kamu akan keluar dari lokasi parkir untuk melakukan check-out.</p>
    @endif
</div>
@endsection
