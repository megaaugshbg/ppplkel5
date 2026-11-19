@extends('layouts.app')

@section('title', 'Pilih Tipe Slot - ' . $lokasi->nama . ' - Markir.In')

@section('content')
    <div class="mx-auto max-w-5xl space-y-6 px-4 pt-5 md:px-6 md:pt-8">
        @if ($bannerTutup)
            <div role="status" class="flex items-start gap-3 rounded-2xl border border-line bg-primary-soft px-4 py-3 text-sm font-medium text-primary">
                <svg class="mt-0.5 h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
                <p>{{ $bannerTutup }}</p>
            </div>
        @endif

        <a href="{{ route('home') }}" class="inline-flex items-center gap-1 text-sm font-semibold hover:opacity-80 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
            Kembali ke beranda
        </a>

        {{-- Info mal --}}
        <section class="flex flex-col gap-4 overflow-hidden rounded-3xl border border-line bg-surface sm:flex-row">
            <img src="{{ $lokasi->foto_url }}" alt="" class="aspect-[16/9] w-full object-cover sm:aspect-auto sm:w-56">
            <div class="flex-1 space-y-1.5 p-5 sm:pl-1">
                <h1 class="text-xl font-bold md:text-2xl">{{ $lokasi->nama }}</h1>
                <p class="text-sm text-slate-500">
                    {{ collect([$lokasi->area, $lokasi->jam_operasional])->filter()->implode(' · ') }}
                </p>
                <p class="text-sm text-slate-600">{{ $lokasi->alamat }}</p>
                <p class="pt-1 text-sm">Tarif <strong>Rp {{ number_format($lokasi->tarif, 0, ',', '.') }}</strong> / jam</p>
            </div>
        </section>

        {{-- Tipe slot --}}
        <section aria-labelledby="judul-tipe" class="space-y-4">
            <h2 id="judul-tipe" class="text-lg font-bold">Pilih tipe slot</h2>
            <ul class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($tipe as $t)
                    <li><x-kartu-tipe :tipe="$t" /></li>
                @endforeach
            </ul>
        </section>
    </div>
@endsection
