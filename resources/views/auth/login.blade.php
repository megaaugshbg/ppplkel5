@extends('layouts.guest')

@section('title', 'Masuk - Markir.In')

@section('content')
    @php
        $jam  = now()->setTimezone('Asia/Jakarta')->hour;
        $sapa = $jam < 11 ? 'pagi' : ($jam < 15 ? 'siang' : ($jam < 19 ? 'sore' : 'malam'));
    @endphp

    <h1 class="text-center text-xl font-semibold">Halo! Selamat {{ $sapa }}!</h1>
    <p class="mt-1 text-center text-sm text-slate-500">Masuk untuk mulai cari parkir.</p>

    <form method="POST" action="{{ route('login') }}" class="mt-8 space-y-4" novalidate>
        @csrf

        <div>
            <label for="email" class="mb-1 block text-sm font-medium">Alamat email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" autocomplete="email" autofocus
                   placeholder="nama@email.com"
                   class="w-full rounded-lg border border-slate-200 px-4 py-3 text-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200 @error('email') border-red-400 @enderror">
            @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="password" class="mb-1 block text-sm font-medium">Password</label>
            <input id="password" type="password" name="password" autocomplete="current-password"
                   placeholder="Masukkan password"
                   class="w-full rounded-lg border border-slate-200 px-4 py-3 text-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200 @error('password') border-red-400 @enderror">
            @error('password') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>


        <button type="submit"
                class="w-full rounded-lg bg-brand-500 px-4 py-3 text-sm font-semibold text-white transition hover:bg-brand-600 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-900">
            Masuk
        </button>
    </form>

    <p class="mt-6 text-center text-sm text-slate-500">
        Belum punya akun?
        <a href="{{ route('register') }}" class="font-semibold text-brand-900 underline underline-offset-4">Daftar</a>
    </p>
@endsection
