@extends('layouts.app')

@section('title', 'Verifikasi Wanita (Woman Parking) - Markir.In')

@php
    $kartu = 'rounded-3xl border border-line bg-surface p-5 md:p-6';
    $tombol = 'rounded-lg bg-primary px-5 py-2.5 text-sm font-semibold text-on-primary transition hover:bg-primary-hover focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus disabled:cursor-not-allowed disabled:opacity-60';
    $tombolLuar = 'rounded-lg border-2 border-primary bg-surface px-5 py-2.5 text-center text-sm font-semibold text-primary transition hover:bg-primary-soft focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus';
@endphp

@section('content')
    <div class="mx-auto max-w-xl space-y-5 px-4 pt-5 md:px-6 md:pt-8">
        <div>
            <h1 class="text-xl font-bold md:text-2xl">Verifikasi Wanita (Woman Parking)</h1>
        </div>

        {{-- Kamera --}}
        <section id="area-kamera" aria-label="Kamera" class="{{ $kartu }} space-y-4">
            <div class="relative mx-auto w-full overflow-hidden rounded-2xl bg-black" style="aspect-ratio: 4 / 3;">
                <video id="video" class="h-full w-full object-cover" playsinline muted></video>
                <button type="button" id="btn-ganti" class="hidden absolute right-2 top-2 rounded-lg bg-black/60 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-black/80 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus">Ganti Kamera</button>
                {{-- Bingkai panduan rasio kartu (±1.586:1) --}}
                <div id="bingkai" aria-hidden="true"
                     class="pointer-events-none absolute left-1/2 top-1/2 w-[90%] -translate-x-1/2 -translate-y-1/2 rounded-xl border-2 border-white/90"
                     style="aspect-ratio: 1.586 / 1; max-height: 90%; box-shadow: 0 0 0 9999px rgba(0,0,0,.45);"></div>
            </div>
            <p class="text-center text-sm text-slate-600">Letakkan SIM di dalam bingkai, hindari pantulan cahaya</p>
            <p id="pesan-kamera" role="alert" class="hidden rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-800"></p>
            <div class="flex flex-col gap-2 sm:flex-row sm:justify-center">
                <button type="button" id="btn-ambil" class="{{ $tombol }}" disabled>Ambil Foto</button>
                <a href="{{ $urlBatal }}" class="{{ $tombolLuar }}">Batal</a>
            </div>
        </section>

        {{-- Membaca --}}
        <section id="area-proses" class="hidden {{ $kartu }} space-y-3" aria-live="polite">
            <p id="progres-teks" class="text-sm font-semibold">Membaca SIM…</p>
            <div class="h-2 w-full overflow-hidden rounded-full bg-surface-alt" role="progressbar" aria-label="Progres membaca SIM">
                <div id="progres-bar" class="h-full w-0 rounded-full bg-primary transition-all"></div>
            </div>
        </section>

        {{-- Berhasil --}}
        <section id="area-sukses" class="hidden {{ $kartu }} space-y-4" aria-live="polite">
            <p class="text-sm text-slate-500">Terbaca</p>
            <p class="text-lg font-bold">Jenis kelamin <span id="hasil-jk"></span></p>
            <form id="form-simpan" method="POST" action="{{ route('profil.verifikasi-sim') }}" class="flex flex-col gap-2 sm:flex-row">
                @csrf
                @if ($lokasi && $tipe)
                    <input type="hidden" name="lokasi" value="{{ $lokasi }}">
                    <input type="hidden" name="tipe" value="{{ $tipe }}">
                @endif
                <input type="hidden" name="jenis_kelamin" id="input-jk" value="">
                <button type="submit" class="{{ $tombol }}">Konfirmasi &amp; Simpan</button>
                <button type="button" data-foto-ulang class="{{ $tombolLuar }}">Foto Ulang</button>
                <a href="{{ $urlBatal }}" class="{{ $tombolLuar }}">Batal</a>
            </form>
        </section>

        {{-- Gagal --}}
        <section id="area-gagal" class="hidden space-y-3" aria-live="assertive">
            <div role="alert" class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-800">
                <p id="pesan-gagal"></p>
            </div>
            <div class="flex flex-col gap-2 sm:flex-row">
                <button type="button" data-foto-ulang class="{{ $tombol }}">Foto Ulang</button>
                <a href="{{ $urlBatal }}" class="{{ $tombolLuar }}">Batal</a>
            </div>
        </section>

        {{-- Canvas hanya untuk pemrosesan di memori; tidak ditampilkan. --}}
        <canvas id="canvas" class="hidden" aria-hidden="true"></canvas>
    </div>
@endsection

@push('scripts')
    @vite('resources/js/verifikasi-sim.js')
@endpush
