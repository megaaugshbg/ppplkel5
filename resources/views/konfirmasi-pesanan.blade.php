{{-- Konfirmasi pesanan. Menampilkan detail charger bila pesanan bertipe EV. --}}
@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-md px-4 pb-28 pt-8 md:px-6 md:pb-12">

    <div class="mb-6 text-center">
        <div class="mx-auto mb-3 grid h-14 w-14 place-items-center rounded-full bg-emerald-100 text-emerald-600">
            <svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path d="m5 12 5 5 9-10"/></svg>
        </div>
        <h1 class="text-xl font-semibold text-primary">Pesanan berhasil</h1>
        <p class="text-sm text-slate-500">Kode pesanan <strong class="text-primary">{{ $pesanan['kode'] }}</strong></p>
    </div>

    <dl class="divide-y divide-line rounded-2xl border border-line bg-surface text-sm">
        <div class="flex justify-between gap-4 p-3">
            <dt class="text-slate-500">Lokasi</dt>
            <dd class="text-right font-medium text-primary">{{ $pesanan['lokasi'] }}</dd>
        </div>
        <div class="flex justify-between gap-4 p-3">
            <dt class="text-slate-500">Alamat</dt>
            <dd class="text-right">{{ $pesanan['alamat'] }}</dd>
        </div>
        <div class="flex justify-between gap-4 p-3">
            <dt class="text-slate-500">Tipe slot</dt>
            <dd class="text-right">{{ $pesanan['tipe'] }}</dd>
        </div>
        <div class="flex justify-between gap-4 p-3">
            <dt class="text-slate-500">Slot</dt>
            <dd class="text-right font-medium text-primary">{{ $pesanan['slot'] }}</dd>
        </div>

        @if (! empty($pesanan['konektor']))
            <div class="flex justify-between gap-4 p-3">
                <dt class="text-slate-500">Charger</dt>
                <dd class="text-right">⚡ {{ $pesanan['konektor'] }} · {{ $pesanan['daya'] }} kW</dd>
            </div>
        @endif

        <div class="flex justify-between gap-4 p-3">
            <dt class="text-slate-500">Tarif parkir</dt>
            <dd class="text-right">Rp {{ number_format($pesanan['tarif'], 0, ',', '.') }}/jam</dd>
        </div>

        @if (! empty($pesanan['tarif_ev_kwh']))
            <div class="flex justify-between gap-4 p-3">
                <dt class="text-slate-500">Tarif charging</dt>
                <dd class="text-right">Rp {{ number_format($pesanan['tarif_ev_kwh'], 0, ',', '.') }}/kWh</dd>
            </div>
        @endif

        <div class="flex justify-between gap-4 p-3">
            <dt class="text-slate-500">Status</dt>
            <dd class="text-right"><span class="rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-medium text-emerald-700">{{ $pesanan['status'] }}</span></dd>
        </div>
    </dl>

    @if (! empty($pesanan['tarif_ev_kwh']))
        <p class="mt-3 rounded-xl bg-emerald-50 p-3 text-xs text-emerald-800">
            Biaya charging dihitung sesuai pemakaian listrik (kWh) dan ditagihkan terpisah dari tarif parkir.
        </p>
    @endif

    <div class="mt-6 flex flex-col gap-2 sm:flex-row">
        <a href="{{ route('pembayaran') }}"
           class="flex-1 rounded-xl bg-primary px-4 py-3 text-center text-sm font-semibold text-white hover:opacity-90 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus">
            Lanjut ke pembayaran
        </a>
        <a href="{{ route('home') }}"
           class="flex-1 rounded-xl border border-line px-4 py-3 text-center text-sm font-medium text-primary hover:bg-surface-alt focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus">
            Kembali ke beranda
        </a>
    </div>
</div>

<x-nav-bawah />
@endsection
