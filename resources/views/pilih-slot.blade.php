<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Markir.In - Pilih Slot</title>
    <style>
        body { font-family: sans-serif; margin: 0; background: #fff; color: #1f2937; }
        header { background: #1e40af; color: #fff; padding: 16px 32px; font-size: 20px; font-weight: bold; }
        main { max-width: 600px; margin: 32px auto; padding: 0 20px; }
        h1 { color: #1e40af; }
        .sub { color: #6b7280; margin-top: 0; }
        .slots { display: grid; grid-template-columns: repeat(auto-fill, minmax(90px, 1fr)); gap: 8px; margin: 12px 0 20px; }
        .slots label { border: 1px solid #bfdbfe; background: #eff6ff; border-radius: 6px; padding: 10px; cursor: pointer; text-align: center; }
        input[type=text] { padding: 10px; border: 1px solid #1e40af; border-radius: 6px; font-size: 15px; width: 100%; margin: 8px 0 16px; box-sizing: border-box; }
        .btn { background: #1e40af; color: #fff; padding: 10px 20px; border: 0; border-radius: 6px; font-size: 15px; cursor: pointer; text-decoration: none; display: inline-block; }
        .btn.sec { background: #fff; color: #1e40af; border: 1px solid #1e40af; }
        .info { padding: 12px 16px; border-radius: 6px; margin: 12px 0; background: #fef3c7; color: #92400e; }
        .info ul { margin: 0; padding-left: 18px; }
    </style>
</head>
<body>
    <header>Markir.In</header>
    <main>
        <h1>Pilih Slot</h1>
        <p class="sub">
            {{ $lokasi->nama }} &middot;
            {{ $tipe === 'wanita' ? 'Woman Parking' : 'Parkir Reguler' }} &middot;
            Rp {{ number_format($lokasi->tarif, 0, ',', '.') }} / jam
        </p>

        @if ($errors->any())
            <div class="info">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if ($slots->isEmpty())
            <div class="info">Semua slot {{ $tipe === 'wanita' ? 'Woman Parking' : 'reguler' }} di lokasi ini sedang terpakai.</div>
            <a class="btn sec" href="/cari-parkir?tipe={{ $tipe }}">Kembali</a>
        @else
            <form method="POST" action="/booking">
                @csrf

                <strong>Slot tersedia ({{ $slots->count() }})</strong>
                <div class="slots">
                    @foreach ($slots as $s)
                        <label>
                            <input type="radio" name="slot_parkir_id" value="{{ $s->id }}" @checked((string) old('slot_parkir_id') === (string) $s->id)>
                            {{ $s->kode }}
                        </label>
                    @endforeach
                </div>

                <label for="plat_nomor"><strong>Plat nomor kendaraan</strong></label>
                <input type="text" id="plat_nomor" name="plat_nomor" value="{{ old('plat_nomor') }}" placeholder="Contoh: L 1234 AB">

                <button type="submit" class="btn">Pesan Slot</button>
                <a class="btn sec" href="/cari-parkir?tipe={{ $tipe }}">Kembali</a>
            </form>
        @endif
    </main>
</body>
</html>
