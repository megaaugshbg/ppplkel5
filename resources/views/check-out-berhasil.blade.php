{{-- Halaman Check-out Berhasil (use case Lakukan Check-out, langkah 3). --}}
@extends('layouts.app')

@section('title', 'Check-out Berhasil - Markir.In')

@section('content')
<div class="mx-auto max-w-md px-4 pt-8 md:px-6">

    <div class="mb-6 text-center">
        <div class="mx-auto mb-3 grid h-14 w-14 place-items-center rounded-full bg-emerald-100 text-emerald-600">
            <svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path d="m5 12 5 5 9-10"/></svg>
        </div>
        <h1 class="text-xl font-semibold text-primary">Check-out berhasil</h1>
        <p class="text-sm text-slate-500">Terima kasih, booking <strong class="text-primary">{{ $struk['kode'] }}</strong> sudah selesai.</p>
    </div>

    {{-- Ringkasan utama --}}
    <div class="mb-4 text-center">
        <div class="mx-auto rounded-2xl border border-line bg-surface p-3">
            <p class="text-[11px] text-slate-500">Total biaya</p>
            <p class="mt-0.5 text-sm font-bold">Rp {{ number_format($struk['total_biaya'], 0, ',', '.') }}</p>
        </div>
    </div>

    <x-ringkasan-checkout :struk="$struk" />

    <p class="mt-3 rounded-xl bg-surface-alt p-3 text-xs text-slate-600">
        Biaya parkir sudah dibayar sebelum check in, jadi tidak ada pembayaran tambahan saat check-out.
    </p>

    <div class="mt-6 flex flex-col gap-2 sm:flex-row">
        <a href="{{ route('check-out.struk') }}"
           class="flex-1 rounded-xl border border-line px-4 py-3 text-center text-sm font-medium text-primary hover:bg-surface-alt focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus">
            Lihat Struk
        </a>
        <a href="{{ route('home') }}"
           class="flex-1 rounded-xl bg-primary px-4 py-3 text-center text-sm font-semibold text-on-primary hover:bg-primary-hover focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus">
            Selesai
        </a>
    </div>
</div>
@endsection
