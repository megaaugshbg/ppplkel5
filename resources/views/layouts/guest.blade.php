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
<body class="min-h-screen bg-brand-200 font-sans text-brand-900 antialiased">
    {{--
        Mobile  : satu kolom, kartu putih melengkung di atas latar biru (seperti desain HP).
        Desktop : dua kolom. Kiri = foto/branding, kanan = form.
    --}}
    <div class="mx-auto grid min-h-screen max-w-6xl lg:grid-cols-2 lg:items-center lg:gap-12 lg:p-10">

        {{-- Panel foto (desktop saja) --}}
        <aside class="relative hidden h-[640px] overflow-hidden rounded-3xl bg-brand-900 lg:block">
            {{-- FOTO: taruh di public/images/auth-bg.jpg --}}
            <img src="{{ asset('images/auth-bg.jpg') }}" alt="" class="absolute inset-0 h-full w-full object-cover" onerror="this.remove()">
            <div class="absolute inset-0 bg-gradient-to-t from-brand-900/90 via-brand-900/40 to-transparent"></div>
            <div class="absolute bottom-0 p-10 text-white">
                <x-logo class="h-12 brightness-0 invert" />
                <p class="mt-4 max-w-sm text-lg leading-snug text-white/90">
                    Pesan slot parkir sebelum berangkat, datang tinggal scan.
                </p>
            </div>
        </aside>

        {{-- Konten form --}}
        <main class="flex min-h-screen flex-col justify-end lg:min-h-0 lg:justify-center">
            <div class="rounded-t-3xl bg-white px-6 pb-10 pt-8 shadow-xl sm:mx-auto sm:w-full sm:max-w-md lg:rounded-3xl lg:px-10 lg:py-10">
                <div class="mb-6 flex justify-center lg:hidden">
                    <x-logo class="h-12" />
                </div>
                @yield('content')
            </div>
        </main>
    </div>
</body>
</html>
