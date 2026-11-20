{{-- Halaman Riwayat Booking: booking selesai / dibatalkan milik Penyewa. --}}
@extends('layouts.app')

@section('title', 'Riwayat Booking - Markir.In')

@use('App\Http\Controllers\RiwayatController')
@use('App\Models\Booking')

@php
    $chips = ['semua' => 'Semua', Booking::SELESAI => 'Selesai', Booking::DIBATALKAN => 'Dibatalkan'];
    $tombol = 'rounded-xl px-5 py-2.5 text-sm font-semibold focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus';
@endphp

@section('content')
<div class="mx-auto max-w-xl space-y-5 px-4 pt-5 md:px-6 md:pt-8">

    <x-tab-booking aktif="riwayat" />

    <div>
        <h1 class="text-xl font-bold md:text-2xl">Riwayat Booking</h1>
        <p class="text-sm text-slate-500">Booking yang sudah selesai atau dibatalkan.</p>
    </div>

    {{-- Filter status --}}
    <nav aria-label="Filter status" class="flex gap-2 overflow-x-auto pb-1 [scrollbar-width:none]">
        @foreach ($chips as $nilai => $label)
            <a href="{{ route('riwayat', $nilai === 'semua' ? [] : ['status' => $nilai]) }}" @if ($status === $nilai) aria-current="true" @endif
               class="inline-flex shrink-0 items-center rounded-full border px-4 py-1.5 text-sm font-medium transition focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus {{ $status === $nilai ? 'border-primary bg-primary text-on-primary' : 'border-line bg-surface text-primary hover:bg-surface-alt' }}">
                {{ $label }}
            </a>
        @endforeach
    </nav>

    @if ($riwayat->isEmpty())
        {{-- Keadaan kosong --}}
        <div class="rounded-3xl border-2 border-dashed border-line bg-surface px-6 py-10 text-center">
            <span class="mx-auto mb-3 grid h-12 w-12 place-items-center rounded-full bg-surface-alt text-primary">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path d="M3 12a9 9 0 1 0 3-6.7L3 8"/><path d="M3 3v5h5M12 7v5l3 2"/></svg>
            </span>
            <p class="text-base font-semibold">
                {{ match ($status) {
                    'selesai'    => 'Belum ada booking yang selesai',
                    'dibatalkan' => 'Belum ada booking yang dibatalkan',
                    default      => 'Belum ada riwayat booking',
                } }}
            </p>
            <p class="mt-1 text-sm text-slate-500">Booking yang sudah check-out atau dibatalkan akan muncul di sini.</p>
            <div class="mt-5 flex flex-col justify-center gap-2 sm:flex-row">
                @if ($status !== 'semua')
                    <a href="{{ route('riwayat') }}" class="{{ $tombol }} border border-line text-primary hover:bg-surface-alt">Lihat semua riwayat</a>
                @endif
                <a href="{{ route('home') }}" class="{{ $tombol }} bg-primary text-on-primary hover:bg-primary-hover">Cari parkir</a>
            </div>
        </div>
    @else
        <ul class="space-y-3" aria-label="Daftar riwayat booking">
            @foreach ($riwayat as $b)
                <li>
                    <a href="{{ route('riwayat.detail', $b->kode) }}"
                       class="flex items-center gap-4 rounded-2xl border border-line bg-surface p-4 transition hover:border-primary focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus">
                        <div class="min-w-0 flex-1">
                            <div class="flex items-start justify-between gap-2">
                                <p class="truncate font-semibold">{{ $b->nama_lokasi }}</p>
                                <x-badge-status :status="$b->status" :label="Booking::LABEL[$b->status]" />
                            </div>
                            <p class="mt-0.5 text-xs text-slate-500">{{ $b->label_tipe }} &middot; Slot {{ $b->kode_slot }} &middot; {{ $b->kode }}</p>
                            <div class="mt-2 flex items-end justify-between gap-2 text-sm">
                                <span class="text-slate-600">
                                    <span class="sr-only">{{ $b->status === Booking::SELESAI ? 'Waktu keluar' : 'Waktu dipesan' }}:</span>
                                    {{ RiwayatController::tanggal($b) }} WIB
                                </span>
                                @if ($b->status === Booking::SELESAI)
                                    <strong>Rp {{ number_format((int) $b->total_biaya, 0, ',', '.') }}</strong>
                                @else
                                    <span class="text-xs font-semibold text-slate-500">Dibatalkan</span>
                                @endif
                            </div>
                        </div>
                        <svg class="h-5 w-5 shrink-0 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path d="m9 6 6 6-6 6"/></svg>
                    </a>
                </li>
            @endforeach
        </ul>

        {{ $riwayat->links('components.paginasi') }}
    @endif
</div>
@endsection
