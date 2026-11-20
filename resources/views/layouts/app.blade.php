<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Markir.In')</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    <x-favicon />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="min-h-screen bg-page font-sans text-primary antialiased">

    <x-navbar />

    <div class="pb-24 md:pb-10">
        @yield('content')
    </div>

    <x-nav-bawah />

    @stack('scripts')
</body>
</html>
