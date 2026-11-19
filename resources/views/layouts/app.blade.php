<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Markir.In')</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-brand-50 font-sans text-brand-900 antialiased">

    {{-- Navbar atas (desktop) --}}
    <header class="sticky top-0 z-30 hidden border-b border-brand-200 bg-white/90 backdrop-blur md:block">
        <div class="mx-auto flex h-16 max-w-6xl items-center justify-between px-6">
            <a href="{{ route('home') }}"><x-logo class="h-9" /></a>

            <nav class="flex items-center gap-8 text-sm font-medium">
                <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'text-brand-900' : 'text-slate-500 hover:text-brand-900' }}">Home</a>
                <a href="#" class="text-slate-500 hover:text-brand-900">Riwayat</a>
                <a href="#" class="text-slate-500 hover:text-brand-900">Pengaturan</a>
            </nav>

            <div class="flex items-center gap-3 text-sm">
                <span class="text-slate-500">{{ auth()->user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="rounded-lg border border-brand-200 px-4 py-1.5 font-medium hover:bg-brand-100">Keluar</button>
                </form>
            </div>
        </div>
    </header>

    <div class="pb-24 md:pb-10">
        @yield('content')
    </div>

    {{-- Navigasi bawah (HP) --}}
    <nav class="fixed inset-x-0 bottom-0 z-30 grid grid-cols-3 rounded-t-3xl bg-white py-2 shadow-[0_-4px_16px_rgba(27,42,107,0.12)] md:hidden">
        <a href="#" class="flex flex-col items-center gap-0.5 py-1 text-[11px] font-medium text-slate-500">
            <svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path d="M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z"/><path d="M19.4 15a1.7 1.7 0 0 0 .3 1.8l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.7 1.7 0 0 0-1.8-.3 1.7 1.7 0 0 0-1 1.5V21a2 2 0 1 1-4 0v-.1a1.7 1.7 0 0 0-1.1-1.5 1.7 1.7 0 0 0-1.8.3l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1a1.7 1.7 0 0 0 .3-1.8 1.7 1.7 0 0 0-1.5-1H3a2 2 0 1 1 0-4h.1a1.7 1.7 0 0 0 1.5-1.1 1.7 1.7 0 0 0-.3-1.8l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1a1.7 1.7 0 0 0 1.8.3H9a1.7 1.7 0 0 0 1-1.5V3a2 2 0 1 1 4 0v.1a1.7 1.7 0 0 0 1 1.5 1.7 1.7 0 0 0 1.8-.3l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1.7 1.7 0 0 0-.3 1.8V9a1.7 1.7 0 0 0 1.5 1H21a2 2 0 1 1 0 4h-.1a1.7 1.7 0 0 0-1.5 1Z"/></svg>
            Settings
        </a>
        <a href="{{ route('home') }}" class="flex flex-col items-center gap-0.5 py-1 text-[11px] font-semibold text-brand-900">
            <svg class="h-7 w-7" fill="currentColor" viewBox="0 0 24 24"><path d="M12 3 2 12h3v8h5v-5h4v5h5v-8h3L12 3Z"/></svg>
            Home
        </a>
        <form method="POST" action="{{ route('logout') }}" class="contents">
            @csrf
            <button class="flex flex-col items-center gap-0.5 py-1 text-[11px] font-medium text-slate-500">
                <svg class="h-7 w-7 text-brand-900" fill="currentColor" viewBox="0 0 24 24"><path d="M12 12a5 5 0 1 0 0-10 5 5 0 0 0 0 10Zm0 2c-4.4 0-8 2.2-8 5v3h16v-3c0-2.8-3.6-5-8-5Z"/></svg>
                Akun
            </button>
        </form>
    </nav>
</body>
</html>
