{{-- Halaman Check In (use case Lakukan Check In). --}}
@extends('layouts.app')

@section('title', 'Check In - Markir.In')

@section('content')
<div class="mx-auto max-w-xl space-y-5 px-4 pt-5 md:px-6 md:pt-8">

    <a href="{{ route('pembayaran.status') }}" class="inline-flex items-center gap-1 text-sm font-semibold hover:opacity-80 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
        Kembali ke status pembayaran
    </a>

    <div>
        <h1 class="text-xl font-bold md:text-2xl">Check In</h1>
        <p class="text-sm text-slate-500">Konfirmasi check in saat kamu sudah sampai di lokasi parkir.</p>
    </div>

    @if (! empty($pesanan['contoh']))
        <div role="status" class="rounded-2xl border border-line bg-surface-alt px-4 py-3 text-sm">
            Ini <strong>data contoh</strong>, jadi status slot di database tidak ikut diubah.
        </div>
    @endif

    @if (session('error'))
        <div role="alert" class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-medium text-amber-800">
            {{ session('error') }}
        </div>
    @endif

    {{-- Slot tujuan --}}
    <section class="flex items-center gap-4 rounded-2xl border border-line bg-surface p-4">
        <span class="grid h-14 w-14 shrink-0 place-items-center rounded-2xl bg-primary text-lg font-bold text-on-primary">
            {{ $pesanan['slot'] }}
        </span>
        <div class="min-w-0">
            <p class="text-xs text-slate-500">Slot kamu</p>
            <p class="truncate text-base font-semibold">{{ $pesanan['lokasi'] }}</p>
            <p class="text-xs text-slate-500">{{ $pesanan['tipe'] }} &middot; dibayar untuk {{ $pesanan['durasi'] }} jam</p>
        </div>
    </section>

    <section aria-labelledby="judul-booking" class="space-y-3">
        <h2 id="judul-booking" class="text-base font-semibold">Detail booking</h2>
        <x-rincian-pesanan :pesanan="$pesanan" />
    </section>

    <form method="POST" action="{{ route('check-in.proses') }}" class="space-y-4">
        @csrf
        <label class="flex items-center gap-2 text-xs text-slate-500">
            <input type="checkbox" name="gagal" value="1" class="accent-[var(--color-primary)]">
            Simulasikan check in gagal
        </label>

        <button type="submit"
                class="w-full rounded-xl bg-primary px-4 py-3 text-sm font-semibold text-on-primary hover:bg-primary-hover focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus">
            Konfirmasi Check In
        </button>
    </form>

    <form method="POST" action="{{ route('check-in.batal') }}">
        @csrf
        <button type="submit"
                class="w-full rounded-xl border border-line px-4 py-3 text-sm font-medium text-primary hover:bg-surface-alt focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus">
            Batalkan Check In
        </button>
    </form>
</div>
@endsection
