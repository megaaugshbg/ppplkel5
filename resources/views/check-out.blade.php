<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Markir.In - Check-out</title>
    <style>
        body { font-family: sans-serif; margin: 0; background: #fff; color: #1f2937; }
        header { background: #1e40af; color: #fff; padding: 16px 32px; font-size: 20px; font-weight: bold; }
        main { max-width: 600px; margin: 32px auto; padding: 0 20px; }
        h1 { color: #1e40af; }
        .card { border: 1px solid #bfdbfe; background: #eff6ff; border-radius: 8px; padding: 16px; margin-bottom: 16px; }
        .card h3 { margin: 0 0 4px; color: #1e40af; }
        .card p { margin: 4px 0; }
        .btn { background: #1e40af; color: #fff; padding: 10px 20px; border: 0; border-radius: 6px; font-size: 15px; cursor: pointer; }
        .btn.sec { background: #fff; color: #1e40af; border: 1px solid #1e40af; }
        input[type=text] { padding: 10px; border: 1px solid #1e40af; border-radius: 6px; font-size: 15px; width: 100%; margin: 8px 0; }
        .info { padding: 12px 16px; border-radius: 6px; margin: 12px 0; display: none; }
        .info.warn { background: #fef3c7; color: #92400e; }
        .hint { color: #6b7280; font-size: 14px; }
        .step { display: none; }
        .step.aktif { display: block; }
        footer { text-align: center; color: #6b7280; padding: 24px; font-size: 14px; }
    </style>
</head>
<body>
    <header>Markir.In</header>
    <main>

        <!-- Halaman 1: Booking Aktif -->
        <div id="stepAktif" class="step aktif">
            <h1>Booking Aktif</h1>
            <div class="card">
                <h3>{{ $booking['lokasi'] }}</h3>
                <p>Slot: {{ $booking['slot'] }}</p>
                <p>Kode booking: {{ $booking['kode'] }}</p>
                <p>Status: Parkir berlangsung</p>
            </div>
            <button class="btn" id="btnParkir">Parkir Berlangsung</button>
        </div>

        <!-- Halaman 2: Check-out (scan barcode) -->
        <div id="stepScan" class="step">
            <h1>Check-out</h1>
            <p>Pindai barcode booking Anda.</p>
            <input type="text" id="barcode" placeholder="Masukkan kode barcode, contoh: MKR-001">
            <p class="hint">Contoh uji: MKR-001 (berhasil), MKR-002 (belum check-in), MKR-999 (tidak ditemukan), abc (tidak valid).</p>
            <label><input type="checkbox" id="offline"> Simulasikan koneksi terputus</label>
            <div id="pesan" class="info warn"></div>
            <p>
                <button class="btn" id="btnScan">Scan Barcode</button>
                <button class="btn sec" id="btnUlang" style="display:none;">Coba lagi</button>
            </p>
        </div>

        <!-- Halaman 3: Check-out Berhasil -->
        <div id="stepBerhasil" class="step">
            <h1>Check-out Berhasil</h1>
            <div class="card">
                <h3 id="hLokasi"></h3>
                <p>Waktu keluar: <strong id="hKeluar"></strong></p>
                <p>Durasi parkir: <strong id="hDurasi"></strong></p>
                <p>Total biaya: <strong id="hTotal"></strong></p>
            </div>
            <button class="btn sec" id="btnStruk">Lihat Struk</button>
            <button class="btn" id="btnSelesai">Selesai</button>
        </div>

    </main>
    <footer>Dikerjakan oleh: Raditya Prazenko Reswara</footer>

    <script>
        var data = @json($dataBooking);

        var steps = ['stepAktif', 'stepScan', 'stepBerhasil'];
        function tampil(id) {
            steps.forEach(function (s) {
                document.getElementById(s).classList.toggle('aktif', s === id);
            });
        }

        var pesan = document.getElementById('pesan');
        var btnUlang = document.getElementById('btnUlang');

        function error(teks, bisaUlang) {
            pesan.textContent = teks;
            pesan.style.display = 'block';
            btnUlang.style.display = bisaUlang ? 'inline-block' : 'none';
        }

        function proses() {
            pesan.style.display = 'none';
            btnUlang.style.display = 'none';

            var kode = document.getElementById('barcode').value.trim().toUpperCase();

            if (document.getElementById('offline').checked) {
                error('Gagal memproses check-out, silakan coba lagi.', true);
                return;
            }
            if (!/^MKR-[0-9]{3}$/.test(kode)) {
                error('Barcode tidak valid. Silakan lakukan pemindaian ulang.', false);
                return;
            }
            if (!data[kode]) {
                error('Booking tidak ditemukan. Silakan periksa kembali barcode yang digunakan.', false);
                return;
            }
            if (!data[kode].sudah_checkin) {
                error('Anda belum melakukan check-in, sehingga check-out tidak dapat dilakukan.', false);
                return;
            }

            var b = data[kode];
            document.getElementById('hLokasi').textContent = b.lokasi;
            document.getElementById('hKeluar').textContent = b.keluar;
            document.getElementById('hDurasi').textContent = b.durasi + ' jam';
            document.getElementById('hTotal').textContent = 'Rp ' + (b.tarif * b.durasi).toLocaleString('id-ID');
            tampil('stepBerhasil');
        }

        document.getElementById('btnParkir').addEventListener('click', function () { tampil('stepScan'); });
        document.getElementById('btnScan').addEventListener('click', proses);
        btnUlang.addEventListener('click', function () {
            document.getElementById('offline').checked = false;
            proses();
        });
        document.getElementById('btnStruk').addEventListener('click', function () {
            alert('Struk check-out (contoh). Fitur struk belum dibuat.');
        });
        document.getElementById('btnSelesai').addEventListener('click', function () {
            document.getElementById('barcode').value = '';
            pesan.style.display = 'none';
            tampil('stepAktif');
        });
    </script>
</body>
</html>
