@extends('layouts.guest')

@section('title', 'Daftar - Markir.In')

@php
    $field = 'w-full rounded-lg border border-slate-200 px-4 py-3 text-sm outline-none transition focus:border-focus focus:ring-2 focus:ring-focus/20';
@endphp

@section('content')
    <h1 class="text-center text-xl font-semibold">Buat akun baru</h1>
    <p class="mt-1 text-center text-sm text-slate-500">Isi data diri kamu dengan benar ya.</p>

    <form method="POST" action="{{ route('register') }}" class="mt-6 space-y-4" novalidate>
        @csrf


        <div class="grid grid-cols-2 gap-3">
            <div>
                <label for="nama_depan" class="mb-1 block text-sm font-medium">Nama depan</label>
                <input id="nama_depan" name="nama_depan" value="{{ old('nama_depan') }}" autocomplete="given-name" class="{{ $field }}">
                @error('nama_depan') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="nama_belakang" class="mb-1 block text-sm font-medium">Nama belakang</label>
                <input id="nama_belakang" name="nama_belakang" value="{{ old('nama_belakang') }}" autocomplete="family-name" class="{{ $field }}">
                @error('nama_belakang') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>

        <div>
            <label for="alamat" class="mb-1 block text-sm font-medium">Alamat</label>
            <input id="alamat" name="alamat" value="{{ old('alamat') }}" autocomplete="street-address" class="{{ $field }}">
            @error('alamat') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        <fieldset>
            <legend class="mb-1 block text-sm font-medium">Jenis kelamin</legend>
            <div class="grid grid-cols-2 gap-3">
                @foreach (\App\Models\User::JENIS_KELAMIN as $nilai => $label)
                    <label class="cursor-pointer">
                        <input type="radio" name="jenis_kelamin" value="{{ $nilai }}" class="peer sr-only" @checked(old('jenis_kelamin') === $nilai)
                               @error('jenis_kelamin') aria-invalid="true" aria-describedby="err-jk" @enderror>
                        <span class="flex items-center justify-center rounded-lg border px-4 py-3 text-sm font-medium transition peer-checked:border-primary peer-checked:bg-primary-soft peer-checked:text-primary peer-focus-visible:outline-2 peer-focus-visible:outline-offset-2 peer-focus-visible:outline-focus {{ $errors->has('jenis_kelamin') ? 'border-red-400' : 'border-slate-200' }}">
                            {{ $label }}
                        </span>
                    </label>
                @endforeach
            </div>
            @error('jenis_kelamin') <p id="err-jk" class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            <p class="mt-1 text-xs text-slate-500">Untuk Woman Parking, lakukan Verifikasi Wanita di Profil.</p>
        </fieldset>

        <div>
            <label for="nik" class="mb-1 block text-sm font-medium">NIK</label>
            <input id="nik" name="nik" value="{{ old('nik') }}" inputmode="numeric" maxlength="16" placeholder="16 digit" class="{{ $field }}">
            @error('nik') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="no_hp" class="mb-1 block text-sm font-medium">Nomor HP</label>
            <div class="flex">
                <span class="flex items-center rounded-l-lg border border-r-0 border-slate-200 bg-surface-alt px-3 text-sm text-slate-600">+62</span>
                <input id="no_hp" name="no_hp" value="{{ old('no_hp') }}" inputmode="tel" autocomplete="tel-national" placeholder="81234567890"
                       class="{{ $field }} rounded-l-none">
            </div>
            @error('no_hp') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="email" class="mb-1 block text-sm font-medium">Alamat email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" autocomplete="email" class="{{ $field }}">
            @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="password" class="mb-1 block text-sm font-medium">Password <span class="font-normal text-slate-400">(min. 8 karakter)</span></label>
            <input id="password" type="password" name="password" autocomplete="new-password" class="{{ $field }}">
            @error('password') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="password_confirmation" class="mb-1 block text-sm font-medium">Verifikasi password</label>
            <input id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password" class="{{ $field }}">
        </div>

        <button type="submit"
                class="w-full rounded-lg bg-primary px-4 py-3 text-sm font-semibold text-on-primary transition hover:bg-primary-hover focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus">
            Daftar
        </button>
    </form>

    <p class="mt-6 text-center text-sm text-slate-500">
        Sudah punya akun?
        <a href="{{ route('login') }}" class="font-semibold text-primary underline underline-offset-4">Masuk</a>
    </p>
@endsection
