<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Markir.In - Cari Lokasi Parkir</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: sans-serif; margin: 0; background: #f4f6f8; color: #1f2937; }
        header { background: #1e40af; color: #fff; padding: 20px 40px; font-size: 22px; font-weight: bold; }
        main { max-width: 800px; margin: 40px auto; padding: 0 20px; }
        h1 { color: #1e40af; margin-bottom: 4px; }
        .sub { color: #6b7280; margin-top: 0; }
        .search { display: flex; gap: 8px; margin: 24px 0 12px; flex-wrap: wrap; }
        .search input, .search select { padding: 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 16px; }
        .search input { flex: 1; min-width: 200px; }
        .btn { background: #1e40af; color: #fff; padding: 12px 24px; border: 0; border-radius: 8px; font-size: 16px; cursor: pointer; text-decoration: none; }
        .btn.sec { background: #fff; color: #1e40af; border: 1px solid #1e40af; }
        .btn.off { background: #9ca3af; }
        .map { background: #dbeafe; border: 2px dashed #93c5fd; border-radius: 12px; height: 160px; display: flex; align-items: center; justify-content: center; color: #1e40af; margin: 16px 0; }
        .card { background: #fff; padding: 20px; border-radius: 12px; margin-bottom: 16px; display: flex; justify-content: space-between; align-items: center; gap: 16px; }
        .card h3 { margin: 0 0 4px; }
        .card p { margin: 2px 0; color: #6b7280; }
        .badge { color: #047857; font-weight: bold; }
        .badge.tutup { color: #b91c1c; }
        .info { padding: 12px 16px; border-radius: 8px; margin: 12px 0; display: none; }
        .info.warn { background: #fef3c7; color: #92400e; }
        .info.ok { background: #d1fae5; color: #065f46; }
        #kosong { text-align: center; color: #6b7280; padding: 24px; display: none; }
        footer { text-align: center; color: #6b7280; padding: 24px; font-size: 14px; }
    </style>
</head>
<body>
    <header>Markir.In</header>
    <main>
        <h1>Cari Lokasi Parkir</h1>
        <p class="sub">Pilih tipe parkir, lalu temukan lahan parkir terdekat.</p>

        <div class="search">
            <input type="text" id="keyword" placeholder="Cari nama lokasi atau mall...">
            <select id="tipe">
                <option value="reguler">Parkir Reguler</option>
                <option value="wanita">Woman Parking</option>
            </select>
            <button class="btn sec" id="btnGps" type="button">Gunakan lokasi saya</button>
        </div>

        <div class="map">Peta sekitar lokasi Penyewa (placeholder)</div>

        <div id="pesan" class="info"></div>

        <div id="daftar">
            @foreach ($lokasi as $l)
                <div class="card"
                     data-teks="{{ strtolower($l['nama'].' '.$l['alamat']) }}"
                     data-tipe="{{ implode(' ', $l['tipe']) }}"
                     data-buka="{{ $l['buka'] ? 1 : 0 }}"
                     data-nama="{{ $l['nama'] }}">
                    <div>
                        <h3>{{ $l['nama'] }}</h3>
                        <p>{{ $l['alamat'] }}</p>
                        <p>Rp {{ number_format($l['tarif'], 0, ',', '.') }} / jam
                            <span class="jarak"> &middot; {{ $l['jarak'] }} km</span></p>
                        @if ($l['buka'])
                            <span class="badge">{{ $l['slot'] }} slot tersedia</span>
                        @else
                            <span class="badge tutup">Sedang tutup</span>
                        @endif
                    </div>
                    <a href="#" class="btn pilih {{ $l['buka'] ? '' : 'off' }}">Pilih</a>
                </div>
            @endforeach
        </div>

        <div id="kosong">Lokasi tidak ditemukan. Silakan ubah kata kunci pencarian.</div>
    </main>
    <footer>Dikerjakan oleh: Nabil Athalafirja Dharmawan</footer>

    <script>
        var kw = document.getElementById('keyword');
        var tipe = document.getElementById('tipe');
        var pesan = document.getElementById('pesan');
        var kosong = document.getElementById('kosong');
        var cards = document.querySelectorAll('#daftar .card');

        function tampilPesan(teks, jenis) {
            pesan.textContent = teks;
            pesan.className = 'info ' + jenis;
            pesan.style.display = 'block';
        }

        function filter() {
            var k = kw.value.toLowerCase();
            var t = tipe.value;
            var n = 0;
            cards.forEach(function (c) {
                var cocok = c.dataset.teks.indexOf(k) !== -1 && c.dataset.tipe.split(' ').indexOf(t) !== -1;
                c.style.display = cocok ? 'flex' : 'none';
                if (cocok) n++;
            });
            kosong.style.display = n ? 'none' : 'block';
        }

        kw.addEventListener('input', filter);
        tipe.addEventListener('change', filter);

        cards.forEach(function (c) {
            c.querySelector('.pilih').addEventListener('click', function (e) {
                e.preventDefault();
                if (c.dataset.buka === '0') {
                    tampilPesan('Lokasi parkir sedang tutup. Silakan pilih lahan parkir lain.', 'warn');
                } else {
                    tampilPesan('Lahan parkir dipilih: ' + c.dataset.nama + '. Lanjut ke Pilih Slot (belum dibuat).', 'ok');
                }
            });
        });

        document.getElementById('btnGps').addEventListener('click', function () {
            if (!navigator.geolocation) { return; }
            navigator.geolocation.getCurrentPosition(
                function () { tampilPesan('Lokasi berhasil dibaca. Jarak ditampilkan dari lokasi Anda.', 'ok'); },
                function () {
                    tampilPesan('Izin lokasi tidak diberikan. Jarak tidak dapat dihitung, gunakan kata kunci pencarian.', 'warn');
                    document.querySelectorAll('.jarak').forEach(function (j) { j.style.display = 'none'; });
                }
            );
        });

        filter();
    </script>
</body>
</html>