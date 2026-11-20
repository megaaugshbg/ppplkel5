@extends('layouts.app')

@section('title', 'Profil - Markir.In')

@php
    $kartu = 'rounded-3xl border border-line bg-surface p-5 md:p-6';
    $field = 'w-full rounded-lg border px-4 py-3 text-sm outline-none transition focus:border-focus focus:ring-2 focus:ring-focus/20';
    $tombol = 'rounded-lg bg-primary px-5 py-2.5 text-sm font-semibold text-on-primary transition hover:bg-primary-hover focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus';
    $terverifikasi = $user->kartuDisabilitasTerverifikasi();
@endphp

@section('content')
    <div class="mx-auto max-w-3xl space-y-5 px-4 pt-5 md:px-6 md:pt-8">
        <div>
            <h1 class="text-xl font-bold md:text-2xl">Profil</h1>
            <p class="mt-1 text-sm text-slate-500">Data akun dan verifikasi untuk Woman Parking &amp; Priority Parking.</p>
        </div>

        @if (session('sukses'))
            <div role="status" class="flex items-center gap-2 rounded-2xl border border-line bg-primary-soft px-4 py-3 text-sm font-semibold text-primary">
                <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24" aria-hidden="true"><path d="m5 12 5 5 9-10"/></svg>
                {{ session('sukses') }}
            </div>
        @endif

        {{-- Data akun (hanya baca) --}}
        <section aria-labelledby="judul-akun" class="{{ $kartu }}">
            <h2 id="judul-akun" class="text-base font-semibold">Data akun</h2>
            <dl class="mt-3 divide-y divide-line text-sm">
                @foreach (['Nama' => $user->name, 'Email' => $user->email, 'Nomor HP' => $user->no_hp ?: '-'] as $label => $nilai)
                    <div class="flex flex-col gap-0.5 py-2.5 sm:flex-row sm:justify-between sm:gap-4">
                        <dt class="text-slate-500">{{ $label }}</dt>
                        <dd class="font-semibold break-all sm:text-right">{{ $nilai }}</dd>
                    </div>
                @endforeach
            </dl>
        </section>

        {{-- Jenis kelamin --}}
        <section aria-labelledby="judul-jk" class="{{ $kartu }}">
            <h2 id="judul-jk" class="text-base font-semibold">Jenis kelamin</h2>
            <p class="mt-1 text-sm text-slate-500">Dipakai untuk verifikasi Woman Parking (khusus pengguna wanita).</p>

            <form method="POST" action="{{ route('profil.jenis-kelamin') }}" class="mt-4 space-y-3">
                @csrf
                @method('PUT')
                <fieldset>
                    <legend class="sr-only">Jenis kelamin</legend>
                    <div class="grid grid-cols-2 gap-3 sm:max-w-sm">
                        @foreach (\App\Models\User::JENIS_KELAMIN as $nilai => $label)
                            <label class="cursor-pointer">
                                <input type="radio" name="jenis_kelamin" value="{{ $nilai }}" class="peer sr-only"
                                       @checked(old('jenis_kelamin', $user->jenis_kelamin) === $nilai)>
                                <span class="flex items-center justify-center rounded-lg border border-line px-4 py-3 text-sm font-medium transition peer-checked:border-primary peer-checked:bg-primary-soft peer-checked:text-primary peer-focus-visible:outline-2 peer-focus-visible:outline-offset-2 peer-focus-visible:outline-focus">
                                    {{ $label }}
                                </span>
                            </label>
                        @endforeach
                    </div>
                    @error('jenis_kelamin') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </fieldset>
                @unless ($user->jenis_kelamin)
                    <p class="text-xs font-semibold text-primary">Belum diisi.</p>
                @endunless
                <button type="submit" class="{{ $tombol }}">Simpan</button>
            </form>
        </section>

        {{-- Kartu disabilitas --}}
        <section aria-labelledby="judul-kartu" class="{{ $kartu }}">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <h2 id="judul-kartu" class="text-base font-semibold">Kartu disabilitas</h2>
                @if ($terverifikasi)
                    <span class="inline-flex items-center gap-1 rounded-full bg-primary-soft px-2.5 py-1 text-xs font-semibold text-primary">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.6" viewBox="0 0 24 24" aria-hidden="true"><path d="m5 12 5 5 9-10"/></svg>
                        Terverifikasi
                    </span>
                @else
                    <span class="rounded-full bg-surface-alt px-2.5 py-1 text-xs font-semibold text-slate-600">Belum ada</span>
                @endif
            </div>
            <p class="mt-1 text-sm text-slate-500">Dipakai untuk verifikasi Priority Parking (penyandang disabilitas).</p>

            @if ($user->nomor_kartu_disabilitas)
                <p class="mt-3 text-sm">Nomor kartu: <strong class="tracking-wide">{{ $user->nomor_kartu_disabilitas }}</strong></p>
            @endif

            <form method="POST" action="{{ route('profil.kartu') }}" class="mt-4 space-y-2">
                @csrf
                <label for="nomor_kartu_disabilitas" class="block text-sm font-medium">
                    {{ $user->nomor_kartu_disabilitas ? 'Ubah nomor kartu' : 'Nomor kartu disabilitas' }}
                </label>
                <div class="flex flex-col gap-2 sm:flex-row">
                    <input id="nomor_kartu_disabilitas" name="nomor_kartu_disabilitas" maxlength="20" autocomplete="off"
                           value="{{ old('nomor_kartu_disabilitas') }}" placeholder="Contoh: KPD-12345678"
                           @error('nomor_kartu_disabilitas') aria-invalid="true" aria-describedby="err-kartu" @enderror
                           class="{{ $field }} uppercase sm:max-w-xs {{ $errors->has('nomor_kartu_disabilitas') ? 'border-red-400' : 'border-line' }}">
                    <button type="submit" class="{{ $tombol }} shrink-0">Simpan kartu</button>
                </div>
                @error('nomor_kartu_disabilitas') <p id="err-kartu" class="text-xs text-red-600">{{ $message }}</p> @enderror
                <p class="text-xs text-slate-500">8–20 huruf, angka, atau tanda hubung. <strong>Simulasi:</strong> belum ada petugas verifikasi, nomor dengan format valid langsung terverifikasi.</p>
            </form>

            @if ($user->nomor_kartu_disabilitas)
                <form method="POST" action="{{ route('profil.kartu.hapus') }}" class="mt-3">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-sm font-semibold text-red-700 underline underline-offset-4 hover:opacity-80">Hapus kartu</button>
                </form>
            @endif
        </section>
    </div>
@endsection
