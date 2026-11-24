<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Markir.In')</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=montserrat:400,500,600,700,800" rel="stylesheet" />
    <x-favicon />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="min-h-screen bg-page font-sans text-primary antialiased">

    {{-- Navbar transparan di atas hero hanya untuk halaman yang memintanya: @section('navbar-transparan', '1'). --}}
    @hasSection('navbar-transparan')
        <x-navbar :transparan="true" />
    @else
        <x-navbar />
    @endif

    <div class="pb-24 md:pb-10">
        @yield('content')
    </div>

    <x-nav-bawah />

    @stack('scripts')
</body>
</html>
