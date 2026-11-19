@extends('layouts.app')

@section('title', 'Tambah Slot Woman Parking - Markir.In')

@php
    $field = 'w-full rounded-lg border bg-surface-alt px-4 py-3 text-sm outline-none focus:ring-2';
    $ok = 'border-slate-200 focus:border-focus focus:ring-focus/20';
    $bad = 'border-rose-400 focus:border-rose-500 focus:ring-rose-200';
@endphp

@section('content')
    <section class="bg-primary">
        <div class="mx-auto max-w-6xl px-6 pb-20 pt-8 md:pb-24 md:pt-12">
            <a href="/woman-parking" class="inline-flex items-center gap-1 text-sm font-medium text-on-primary/80 hover:text-on-primary">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="m15 18-6-6 6-6"/></svg>
                Kembali
            </a>
            <h1 class="mt-3 text-3xl font-bold text-on-primary md:text-4xl">Tambah slot Woman Parking</h1>
            <p class="mt-2 text-on-primary/85">Isi data slot parkir khusus perempuan yang akan ditambahkan.</p>
        </div>
    </section>

    <section class="relative z-10 mx-auto -mt-12 max-w-2xl px-4 md:-mt-14 md:px-6">
        <form method="POST" action="/woman-parking" novalidate class="space-y-5 rounded-3xl bg-surface p-6 shadow-lg md:p-8">
            @csrf

            @if ($errors->any())
                <div role="alert" class="rounded-xl bg-rose-50 px-4 py-3 text-sm text-rose-700">
                    Data belum lengkap. Periksa kolom yang ditandai merah.
                </div>
            @endif

            <div>
                <label for="lokasi" class="mb-1 block text-sm font-semibold">Lokasi</label>
                <input id="lokasi" name="lokasi" value="{{ old('lokasi') }}" placeholder="Contoh: Parkir Mall Galaxy"
                       @error('lokasi') aria-invalid="true" aria-describedby="err-lokasi" @enderror
                       class="{{ $field }} {{ $errors->has('lokasi') ? $bad : $ok }}">
                @error('lokasi') <p id="err-lokasi" class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="kode_slot" class="mb-1 block text-sm font-semibold">Kode slot</label>
                    <input id="kode_slot" name="kode_slot" value="{{ old('kode_slot') }}" placeholder="W-01" autocapitalize="characters"
                           @error('kode_slot') aria-invalid="true" aria-describedby="err-kode" @enderror
                           class="{{ $field }} uppercase {{ $errors->has('kode_slot') ? $bad : $ok }}">
                    @error('kode_slot') <p id="err-kode" class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="tarif" class="mb-1 block text-sm font-semibold">Tarif per jam (Rp)</label>
                    <input id="tarif" name="tarif" type="number" min="0" inputmode="numeric" value="{{ old('tarif') }}" placeholder="5000"
                           @error('tarif') aria-invalid="true" aria-describedby="err-tarif" @enderror
                           class="{{ $field }} {{ $errors->has('tarif') ? $bad : $ok }}">
                    @error('tarif') <p id="err-tarif" class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <fieldset>
                <legend class="mb-2 text-sm font-semibold">Status</legend>
                <div class="grid grid-cols-2 gap-3 sm:max-w-sm">
                    @foreach (['tersedia' => 'Tersedia', 'terisi' => 'Terisi'] as $val => $label)
                        <label class="cursor-pointer">
                            <input type="radio" name="status" value="{{ $val }}" class="peer sr-only" @checked(old('status', 'tersedia') === $val)>
                            <span class="flex items-center justify-center rounded-xl border-2 border-line bg-surface-alt px-4 py-3 text-sm font-semibold transition peer-checked:border-primary peer-checked:bg-primary-soft peer-focus-visible:outline-2 peer-focus-visible:outline-focus">
                                {{ $label }}
                            </span>
                        </label>
                    @endforeach
                </div>
                @error('status') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </fieldset>

            <div>
                <label for="catatan" class="mb-1 block text-sm font-semibold">Catatan <span class="font-normal text-slate-400">(opsional)</span></label>
                <textarea id="catatan" name="catatan" rows="3" placeholder="Contoh: Dekat lift lantai 1"
                          class="{{ $field }} {{ $errors->has('catatan') ? $bad : $ok }}">{{ old('catatan') }}</textarea>
                @error('catatan') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div class="flex flex-col-reverse gap-3 pt-2 sm:flex-row">
                <a href="/woman-parking"
                   class="rounded-lg border border-line px-6 py-3 text-center text-sm font-semibold transition hover:bg-surface-alt">
                    Batal
                </a>
                <button type="submit"
                        class="rounded-lg bg-primary px-6 py-3 text-sm font-semibold text-on-primary transition hover:bg-primary-hover focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus">
                    Simpan slot
                </button>
            </div>
        </form>
    </section>
@endsection
