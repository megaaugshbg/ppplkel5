{{-- Halaman Konfirmasi Check In. --}}
@extends('layouts.app')

@section('title', 'Check In Berhasil - Markir.In')

@section('content')
<div class="mx-auto max-w-md px-4 pt-8 md:px-6">

    <div class="mb-6 text-center">
        <div class="mx-auto mb-3 grid h-14 w-14 place-items-center rounded-full bg-emerald-100 text-emerald-600">
            <svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path d="m5 12 5 5 9-10"/></svg>
        </div>
        <h1 class="text-xl font-semibold text-primary">Check in berhasil</h1>
        <p class="text-sm text-slate-500">Silakan parkir di slot <strong class="text-primary">{{ $pesanan['slot'] }}</strong></p>
    </div>

    {{-- QR Code untuk check-out --}}
    <div class="mb-4 rounded-2xl border border-line bg-surface p-4 text-center">
        <x-qr-booking :kode="$pesanan['kode']" />
        <p class="mt-2 text-xs text-slate-500">Simpan QR ini, dipakai untuk check-out saat keluar.</p>
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
            <dt class="text-slate-500">Waktu masuk</dt>
            <dd class="text-right font-medium">{{ $pesanan['waktu_checkin'] }} WIB</dd>
        </div>
        <div class="flex justify-between gap-4 p-3">
            <dt class="text-slate-500">Status slot</dt>
            <dd class="text-right"><span class="rounded-full bg-primary-soft px-2.5 py-0.5 text-xs font-semibold text-primary">Terisi</span></dd>
        </div>
        <div class="flex justify-between gap-4 p-3">
            <dt class="text-slate-500">Status booking</dt>
            <dd class="text-right"><span class="rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-700">{{ $pesanan['status'] }}</span></dd>
        </div>
    </dl>

    <p class="mt-3 rounded-xl bg-surface-alt p-3 text-xs text-slate-600">
        Saat keluar, lakukan check-out dengan scan barcode booking.
    </p>

    <div class="mt-6 flex flex-col gap-2 sm:flex-row">
        <a href="{{ route('check-out') }}"
           class="flex-1 rounded-xl bg-primary px-4 py-3 text-center text-sm font-semibold text-on-primary hover:bg-primary-hover focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus">
            Ke halaman check-out
        </a>
        <a href="{{ route('home') }}"
           class="flex-1 rounded-xl border border-line px-4 py-3 text-center text-sm font-medium text-primary hover:bg-surface-alt focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus">
            Kembali ke beranda
        </a>
    </div>
</div>
@endsection
