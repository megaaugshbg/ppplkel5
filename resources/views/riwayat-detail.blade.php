{{-- Detail satu riwayat booking (selesai / dibatalkan). --}}
@extends('layouts.app')

@use('App\Models\Booking')

@section('title', 'Riwayat ' . $booking->kode . ' - Markir.In')

@php $selesai = $booking->status === Booking::SELESAI; @endphp

@push('styles')
<style>
    /* Saat dicetak: hanya rincian yang tampil (sama seperti struk check-out). */
    @media print {
        body > header, nav[aria-label="Navigasi bawah"], .jangan-cetak { display: none !important; }
        body { background: var(--color-surface); }
    }
</style>
@endpush

@section('content')
<div class="mx-auto max-w-md space-y-5 px-4 pt-5 md:px-6 md:pt-8">

    <a href="{{ route('riwayat') }}" class="jangan-cetak inline-flex items-center gap-1 text-sm font-semibold hover:opacity-80 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
        Kembali ke riwayat
    </a>

    <article aria-labelledby="judul-detail" class="rounded-3xl border border-line bg-surface p-5 shadow-sm">
        <header class="mb-4 flex items-center justify-between gap-3 border-b border-dashed border-line pb-4">
            <x-logo :height="32" />
            <div class="text-right">
                <h1 id="judul-detail" class="text-base font-bold">{{ $selesai ? 'Struk Parkir' : 'Booking Dibatalkan' }}</h1>
                <x-badge-status :status="$booking->status" :label="$struk['status']" />
            </div>
        </header>

        @if ($selesai)
            {{-- Sama dengan struk check-out: termasuk catatan melebihi durasi & info charger EV. --}}
            <x-ringkasan-checkout :struk="$struk" :lengkap="true" />
            <p class="mt-4 text-center text-xs text-slate-500">Biaya parkir dibayar sebelum check in.</p>
        @else
            <dl class="divide-y divide-line overflow-hidden rounded-2xl border border-line text-sm">
                @foreach ([
                    'Kode booking'  => $struk['kode'],
                    'Lokasi'        => $struk['lokasi'],
                    'Alamat'        => $struk['alamat'],
                    'Slot'          => $struk['slot'],
                    'Tipe parkir'   => $struk['tipe'],
                    'Waktu dipesan' => $dipesan . ' WIB',
                ] as $label => $isi)
                    <div class="flex justify-between gap-4 px-4 py-3">
                        <dt class="text-slate-500">{{ $label }}</dt>
                        <dd class="text-right font-medium">{{ $isi }}</dd>
                    </div>
                @endforeach
                <div class="flex items-center justify-between gap-4 bg-surface-alt px-4 py-3">
                    <dt class="text-slate-500">Status</dt>
                    <dd><x-badge-status :status="$booking->status" :label="$struk['status']" /></dd>
                </div>
            </dl>
            <p class="mt-4 text-center text-xs text-slate-500">Pesanan ini dibatalkan sebelum dibayar, jadi tidak ada biaya.</p>
        @endif
    </article>

    <div class="jangan-cetak flex flex-col gap-2 sm:flex-row">
        @if ($selesai)
            <button type="button" onclick="window.print()"
                    class="flex-1 rounded-xl border border-line px-4 py-3 text-center text-sm font-medium text-primary hover:bg-surface-alt focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus">
                Cetak
            </button>
        @endif
        <a href="{{ route('riwayat') }}"
           class="flex-1 rounded-xl bg-primary px-4 py-3 text-center text-sm font-semibold text-on-primary hover:bg-primary-hover focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus">
            Kembali ke daftar
        </a>
    </div>
</div>
@endsection
