{{-- Struk check-out (bisa dicetak). --}}
@extends('layouts.app')

@section('title', 'Struk ' . $struk['kode'] . ' - Markir.In')

@push('styles')
<style>
    /* Saat dicetak: hanya struk yang tampil. */
    @media print {
        body > header, nav[aria-label="Navigasi bawah"], .jangan-cetak { display: none !important; }
        body { background: var(--color-surface); }
    }
</style>
@endpush

@section('content')
<div class="mx-auto max-w-md px-4 pt-6 md:px-6 md:pt-8">

    <a href="{{ route('check-out.berhasil') }}" class="jangan-cetak mb-4 inline-flex items-center gap-1 text-sm font-semibold hover:opacity-80 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
        Kembali
    </a>

    <article aria-labelledby="judul-struk" class="rounded-3xl border border-line bg-surface p-5 shadow-sm">
        <header class="mb-4 flex items-center justify-between gap-3 border-b border-dashed border-line pb-4">
            <x-logo :height="32" />
            <div class="text-right">
                <h1 id="judul-struk" class="text-base font-bold">Struk Parkir</h1>
                <span class="rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-700">{{ $struk['status'] }}</span>
            </div>
        </header>

        <x-ringkasan-checkout :struk="$struk" :lengkap="true" />

        <p class="mt-4 text-center text-xs text-slate-500">Biaya parkir dibayar sebelum check in. Terima kasih telah memakai Markir.In.</p>
    </article>

    <div class="jangan-cetak mt-6 flex flex-col gap-2 sm:flex-row">
        <button type="button" onclick="window.print()"
                class="flex-1 rounded-xl border border-line px-4 py-3 text-center text-sm font-medium text-primary hover:bg-surface-alt focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus">
            Cetak
        </button>
        <a href="{{ route('home') }}"
           class="flex-1 rounded-xl bg-primary px-4 py-3 text-center text-sm font-semibold text-on-primary hover:bg-primary-hover focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus">
            Selesai
        </a>
    </div>
</div>
@endsection
