<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Markir.In - @yield('judul')</title>
    <x-favicon />
    @vite('resources/css/tokens.css')
    <style>
        * { box-sizing: border-box; }
        /* Warna diambil dari resources/css/tokens.css */
        body { font-family: 'Segoe UI', system-ui, sans-serif; margin: 0; background: var(--color-page); color: #1f2937; min-height: 100vh; display: flex; flex-direction: column; }
        .navbar { background: var(--color-surface); border-bottom: 1px solid var(--color-line); }
        .navbar .dalam { max-width: 1120px; margin: 0 auto; padding: 14px 24px; display: flex; align-items: center; justify-content: space-between; }
        .logo { display: block; }
        .logo:focus-visible { outline: 2px solid var(--color-focus); outline-offset: 4px; border-radius: 4px; }
        .navbar nav a { color: var(--color-primary); text-decoration: none; font-weight: 600; font-size: 15px; margin-left: 28px; padding-bottom: 4px; }
        .navbar nav a:hover, .navbar nav a.aktif { border-bottom: 2px solid var(--color-primary); }
        .navbar nav a:focus-visible { outline: 2px solid var(--color-focus); outline-offset: 4px; }
        .hero { background: var(--color-surface-alt); border-bottom: 1px solid var(--color-line); }
        .hero .dalam { max-width: 1120px; margin: 0 auto; padding: 28px 24px; }
        .hero h1 { margin: 0 0 4px; font-size: 28px; color: var(--color-primary); }
        .hero p { margin: 0; color: var(--color-primary); opacity: .8; font-size: 15px; }
        .hero a.kembali { display: inline-block; color: var(--color-primary); font-size: 14px; font-weight: 600; text-decoration: none; margin-bottom: 10px; }
        main { flex: 1; width: 100%; max-width: 1120px; margin: 0 auto; padding: 28px 24px 40px; }
        .grid { display: grid; grid-template-columns: 1fr 340px; gap: 24px; align-items: start; }
        .panel-samping { position: sticky; top: 20px; }
        .kartu { background: var(--color-surface); border: 1px solid var(--color-line); border-radius: 16px; padding: 20px; box-shadow: 0 2px 10px var(--color-shadow); }
        h2 { font-size: 17px; color: var(--color-primary); margin: 0 0 12px; }
        .pill { display: inline-block; background: var(--color-primary); color: var(--color-on-primary); border-radius: 999px; padding: 6px 16px; font-size: 13px; font-weight: 600; margin-bottom: 14px; }
        .btn { display: block; width: 100%; background: var(--color-primary); color: var(--color-on-primary); padding: 12px 20px; border: 0; border-radius: 10px; font-size: 15px; font-weight: 600; text-align: center; cursor: pointer; text-decoration: none; }
        .btn:hover { background: var(--color-primary-hover); }
        .btn:focus-visible { outline: 2px solid var(--color-focus); outline-offset: 2px; }
        .btn.sec { background: var(--color-surface); color: var(--color-primary); border: 1px solid var(--color-primary); }
        .btn.sec:hover { background: var(--color-primary-soft); }
        .btn.off { background: #9ca3af; }
        .btn + .btn { margin-top: 10px; }
        .info { padding: 12px 14px; border-radius: 10px; margin: 0 0 16px; font-size: 14px; }
        .info.warn { background: #fef3c7; color: #92400e; }
        .info.ok { background: #d1fae5; color: #065f46; }
        footer { text-align: center; color: #6b7280; padding: 20px; font-size: 13px; border-top: 1px solid var(--color-line); background: var(--color-surface); }
        @media (max-width: 900px) {
            .grid { grid-template-columns: 1fr; }
            .panel-samping { position: static; }
            .navbar nav a { margin-left: 16px; }
        }
        @yield('style')
    </style>
</head>
<body>
    <div class="navbar">
        <div class="dalam">
            <a href="/home" class="logo"><x-logo :height="40" /></a>
            <nav>
                <a href="/home">Beranda</a>
            </nav>
        </div>
    </div>

    <div class="hero">
        <div class="dalam">
            @hasSection('kembali')
                <a href="@yield('kembali')" class="kembali">&larr; Kembali</a>
            @endif
            <h1>@yield('judul')</h1>
            <p>@yield('deskripsi')</p>
        </div>
    </div>

    <main>
        @yield('isi')
    </main>

    @yield('script')
</body>
</html>
