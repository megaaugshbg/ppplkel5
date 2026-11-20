{{-- Halaman Detail Booking (ditampilkan saat pembayaran / check in dibatalkan). --}}
@extends('layouts.app')

@section('title', 'Detail Booking - Markir.In')

@section('content')
<div class="mx-auto max-w-xl space-y-5 px-4 pt-5 md:px-6 md:pt-8">

    <div>
        <h1 class="text-xl font-bold md:text-2xl">Detail Booking</h1>
        <p class="text-sm text-slate-500">Data booking kamu tidak berubah.</p>
    </div>

    @if (session('pesan'))
        <div role="status" class="rounded-2xl border border-line bg-surface-alt px-4 py-3 text-sm font-medium">
            {{ session('pesan') }}
        </div>
    @endif

    <x-rincian-pesanan :pesanan="$pesanan">
        @if ($sudahBayar)
            <div class="flex justify-between gap-4 p-3">
                <dt class="text-slate-500">Dibayar</dt>
                <dd class="text-right">Rp {{ number_format($pesanan['total'], 0, ',', '.') }} ({{ $pesanan['durasi'] }} jam)</dd>
            </div>
        @endif
    </x-rincian-pesanan>

    <div class="flex flex-col gap-2 sm:flex-row">
        @if (! $sudahBayar)
            <a href="{{ route('pembayaran') }}"
               class="flex-1 rounded-xl bg-primary px-4 py-3 text-center text-sm font-semibold text-on-primary hover:bg-primary-hover focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus">
                Bayar sekarang
            </a>
        @elseif (! $sudahCheckIn)
            <a href="{{ route('check-in') }}"
               class="flex-1 rounded-xl bg-primary px-4 py-3 text-center text-sm font-semibold text-on-primary hover:bg-primary-hover focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus">
                Check in sekarang
            </a>
        @endif
        <a href="{{ route('home') }}"
           class="flex-1 rounded-xl border border-line px-4 py-3 text-center text-sm font-medium text-primary hover:bg-surface-alt focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus">
            Kembali ke beranda
        </a>
    </div>
</div>
@endsection
