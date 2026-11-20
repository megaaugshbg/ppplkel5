{{-- Halaman Status Pembayaran. --}}
@extends('layouts.app')

@section('title', 'Status Pembayaran - Markir.In')

@section('content')
<div class="mx-auto max-w-md px-4 pt-8 md:px-6">

    <div class="mb-6 text-center">
        <div class="mx-auto mb-3 grid h-14 w-14 place-items-center rounded-full bg-emerald-100 text-emerald-600">
            <svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path d="m5 12 5 5 9-10"/></svg>
        </div>
        <h1 class="text-xl font-semibold text-primary">Pembayaran berhasil</h1>
        <p class="text-sm text-slate-500">No. transaksi <strong class="text-primary">{{ $pesanan['no_transaksi'] }}</strong></p>
    </div>

    <dl class="divide-y divide-line rounded-2xl border border-line bg-surface text-sm">
        <div class="flex justify-between gap-4 p-3">
            <dt class="text-slate-500">Kode pesanan</dt>
            <dd class="text-right font-semibold text-primary">{{ $pesanan['kode'] }}</dd>
        </div>
        <div class="flex justify-between gap-4 p-3">
            <dt class="text-slate-500">Lokasi</dt>
            <dd class="text-right">{{ $pesanan['lokasi'] }}</dd>
        </div>
        <div class="flex justify-between gap-4 p-3">
            <dt class="text-slate-500">Slot</dt>
            <dd class="text-right">{{ $pesanan['slot'] }} ({{ $pesanan['tipe'] }})</dd>
        </div>
        <div class="flex justify-between gap-4 p-3">
            <dt class="text-slate-500">Lama parkir</dt>
            <dd class="text-right">{{ $pesanan['durasi'] }} jam</dd>
        </div>
        <div class="flex justify-between gap-4 p-3">
            <dt class="text-slate-500">Metode</dt>
            <dd class="text-right">{{ $pesanan['metode'] }}</dd>
        </div>
        <div class="flex justify-between gap-4 p-3">
            <dt class="text-slate-500">Waktu bayar</dt>
            <dd class="text-right">{{ $pesanan['waktu_bayar'] }} WIB</dd>
        </div>
        <div class="flex justify-between gap-4 p-3">
            <dt class="text-slate-500">Total dibayar</dt>
            <dd class="text-right text-base font-bold text-primary">Rp {{ number_format($pesanan['total'], 0, ',', '.') }}</dd>
        </div>
        <div class="flex justify-between gap-4 p-3">
            <dt class="text-slate-500">Status</dt>
            <dd class="text-right"><span class="rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-700">{{ $pesanan['status'] }}</span></dd>
        </div>
    </dl>

    <div class="mt-6 flex flex-col gap-2 sm:flex-row">
        @if ($pesanan['status'] === \App\Support\PesananSesi::SUDAH_BAYAR)
            <a href="{{ route('check-in') }}"
               class="flex-1 rounded-xl bg-primary px-4 py-3 text-center text-sm font-semibold text-on-primary hover:bg-primary-hover focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus">
                Lanjut ke check in
            </a>
        @endif
        <a href="{{ route('home') }}"
           class="flex-1 rounded-xl border border-line px-4 py-3 text-center text-sm font-medium text-primary hover:bg-surface-alt focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus">
            Kembali ke beranda
        </a>
    </div>
</div>
@endsection
