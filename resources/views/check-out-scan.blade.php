{{-- Halaman Check-out: tampilkan QR booking & konfirmasi di gerbang keluar (use case Lakukan Check-out, langkah 2). --}}
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
        <p class="text-sm text-slate-500">Tunjukkan QR booking di gerbang keluar untuk menyelesaikan parkir.</p>
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
            <p class="text-xs text-slate-500">{{ $pesanan['tipe'] }}</p>
        </div>
    </section>

    {{-- QR Code booking untuk check-out --}}
    <section class="space-y-2 rounded-2xl border border-line bg-surface p-4 text-center">
        <h2 class="text-base font-semibold">QR Code check-out</h2>
        <x-qr-booking :kode="$pesanan['kode']" />
        <p class="text-xs text-slate-500">Arahkan QR ini ke pemindai di gerbang keluar.</p>
    </section>

    {{-- Konfirmasi check-out (kode booking dikirim otomatis) --}}
    <form method="POST" action="{{ route('check-out.proses') }}" class="rounded-2xl border border-line bg-surface p-4 sm:p-5">
        @csrf
        <input type="hidden" name="barcode" value="{{ $pesanan['kode'] }}">

        <button type="submit"
                class="flex w-full items-center justify-center gap-2 rounded-xl bg-primary px-4 py-3 text-sm font-semibold text-on-primary hover:bg-primary-hover focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7V5a1 1 0 0 1 1-1h2M17 4h2a1 1 0 0 1 1 1v2M20 17v2a1 1 0 0 1-1 1h-2M7 20H5a1 1 0 0 1-1-1v-2M7 8v8M10 8v8M13 8v8M17 8v8"/></svg>
            {{ session('bisa_ulang') ? 'Coba lagi' : 'Konfirmasi' }}
        </button>
    </form>
</div>
@endsection
