<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Markir.In - @yield('judul')</title>
    <style>
        * { box-sizing: border-box; }
        :root { --navy: #1e2a78; --biru: #4f6fd8; --muda: #c3d3fa; --pucat: #eef2ff; --garis: #dbe4fb; }
        body { font-family: 'Segoe UI', system-ui, sans-serif; margin: 0; background: #f5f7ff; color: #1f2937; min-height: 100vh; display: flex; flex-direction: column; }
        .navbar { background: #fff; border-bottom: 1px solid var(--garis); }
        .navbar .dalam { max-width: 1120px; margin: 0 auto; padding: 14px 24px; display: flex; align-items: center; justify-content: space-between; }
        .logo { font-weight: 900; font-size: 24px; letter-spacing: 1px; color: var(--navy); text-decoration: none; }
        .logo small { font-weight: 700; font-size: 18px; text-transform: lowercase; }
        .navbar nav a { color: var(--navy); text-decoration: none; font-weight: 600; font-size: 15px; margin-left: 28px; padding-bottom: 4px; }
        .navbar nav a:hover, .navbar nav a.aktif { border-bottom: 2px solid var(--biru); }
        .hero { background: var(--muda); }
        .hero .dalam { max-width: 1120px; margin: 0 auto; padding: 28px 24px; }
        .hero h1 { margin: 0 0 4px; font-size: 28px; color: var(--navy); }
        .hero p { margin: 0; color: #3b4a8f; font-size: 15px; }
        .hero a.kembali { display: inline-block; color: var(--navy); font-size: 14px; font-weight: 600; text-decoration: none; margin-bottom: 10px; }
        main { flex: 1; width: 100%; max-width: 1120px; margin: 0 auto; padding: 28px 24px 40px; }
        .grid { display: grid; grid-template-columns: 1fr 340px; gap: 24px; align-items: start; }
        .panel-samping { position: sticky; top: 20px; }
        .kartu { background: #fff; border: 1px solid var(--garis); border-radius: 16px; padding: 20px; box-shadow: 0 2px 10px rgba(30, 42, 120, .05); }
        h2 { font-size: 17px; color: var(--navy); margin: 0 0 12px; }
        .pill { display: inline-block; background: var(--navy); color: #fff; border-radius: 999px; padding: 6px 16px; font-size: 13px; font-weight: 600; margin-bottom: 14px; }
        .btn { display: block; width: 100%; background: var(--biru); color: #fff; padding: 12px 20px; border: 0; border-radius: 10px; font-size: 15px; font-weight: 600; text-align: center; cursor: pointer; text-decoration: none; }
        .btn:hover { background: #3f5cc4; }
        .btn.sec { background: #fff; color: var(--biru); border: 1px solid var(--biru); }
        .btn.sec:hover { background: var(--pucat); }
        .btn.off { background: #9ca3af; }
        .btn + .btn { margin-top: 10px; }
        .info { padding: 12px 14px; border-radius: 10px; margin: 0 0 16px; font-size: 14px; }
        .info.warn { background: #fef3c7; color: #92400e; }
        .info.ok { background: #d1fae5; color: #065f46; }
        footer { text-align: center; color: #6b7280; padding: 20px; font-size: 13px; border-top: 1px solid var(--garis); background: #fff; }
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
            <a href="/home" class="logo">MARKIR<small>.in</small></a>
            <nav>
                <a href="/home">Beranda</a>
                <a href="/cari-parkir" class="aktif">Cari Parkir</a>
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
