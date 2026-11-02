<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Markir.In - Pembayaran</title>
    <style>
        body { font-family: sans-serif; margin: 0; background: #fff; color: #1f2937; }
        header { background: #1e40af; color: #fff; padding: 16px 32px; font-size: 20px; font-weight: bold; }
        main { max-width: 600px; margin: 32px auto; padding: 0 20px; }
        h1 { color: #1e40af; }
        .card { border: 1px solid #bfdbfe; background: #eff6ff; border-radius: 8px; padding: 16px; margin-bottom: 16px; }
        .card h3 { margin: 0 0 8px; color: #1e40af; }
        .card p { margin: 4px 0; }
        .total { font-size: 18px; margin-top: 10px !important; }
        .metode label { display: block; border: 1px solid #bfdbfe; border-radius: 6px; padding: 10px; margin-bottom: 8px; cursor: pointer; }
        .btn { background: #1e40af; color: #fff; padding: 10px 20px; border: 0; border-radius: 6px; font-size: 15px; cursor: pointer; }
        .btn.sec { background: #fff; color: #1e40af; border: 1px solid #1e40af; }
        .info { padding: 12px 16px; border-radius: 6px; margin: 12px 0; display: none; }
        .info.warn { background: #fef3c7; color: #92400e; }
        .info.sukses { background: #dcfce7; color: #166534; display: block; }
        .step { display: none; }
        .step.aktif { display: block; }
        footer { text-align: center; color: #6b7280; padding: 24px; font-size: 14px; }
    </style>
</head>
<body>
    <header>Markir.In</header>
    <main>

        <!-- Form Pembayaran -->
        <div id="stepForm" class="step aktif">
            <h1>Pembayaran</h1>

            <div class="card">
                <h3>Rincian Tagihan</h3>
                <p>Kode booking: {{ $booking['kode'] }}</p>
                <p>Lokasi: {{ $booking['lokasi'] }}</p>
                <p>Slot: {{ $booking['slot'] }}</p>
                <p>Waktu: {{ $booking['tanggal'] }}, {{ $booking['waktu'] }}</p>
                <p>Durasi: {{ $booking['durasi'] }} jam x Rp {{ number_format($booking['tarif'], 0, ',', '.') }}</p>
                <p class="total">Total biaya: <strong>Rp {{ number_format($total, 0, ',', '.') }}</strong></p>
            </div>

            <h3>Pilih Metode Pembayaran</h3>
            <div class="metode">
                @foreach ($metode as $m)
                    <label><input type="radio" name="metode" value="{{ $m }}"> {{ $m }}</label>
                @endforeach
            </div>

            <label><input type="checkbox" id="gagal"> Simulasikan pembayaran gagal</label>
            <div id="pesan" class="info warn"></div>

            <p>
                <button class="btn" id="btnBayar">Konfirmasi Pembayaran</button>
                <button class="btn sec" id="btnBatal">Batalkan</button>
            </p>
        </div>

        <!-- Halaman Status Pembayaran -->
        <div id="stepStatus" class="step">
            <h1>Status Pembayaran</h1>
            <div class="info sukses">Pembayaran berhasil.</div>
            <div class="card">
                <p>Kode booking: {{ $booking['kode'] }}</p>
                <p>Metode: <strong id="sMetode"></strong></p>
                <p>Total dibayar: <strong>Rp {{ number_format($total, 0, ',', '.') }}</strong></p>
                <p>Status booking: <strong>Sudah dibayar</strong></p>
            </div>
            <button class="btn" id="btnSelesai">Selesai</button>
        </div>

        <!-- Halaman Detail Booking (jika pembayaran dibatalkan) -->
        <div id="stepDetail" class="step">
            <h1>Detail Booking</h1>
            <div class="card">
                <h3>{{ $booking['lokasi'] }}</h3>
                <p>Slot: {{ $booking['slot'] }}</p>
                <p>Kode booking: {{ $booking['kode'] }}</p>
                <p>Status: {{ $booking['status'] }}</p>
            </div>
            <button class="btn" id="btnKembali">Bayar Sekarang</button>
        </div>

    </main>
    <footer>Dikerjakan oleh: Fachry Ananda Akmal</footer>

    <script>
        var steps = ['stepForm', 'stepStatus', 'stepDetail'];
        function tampil(id) {
            steps.forEach(function (s) {
                document.getElementById(s).classList.toggle('aktif', s === id);
            });
        }

        var pesan = document.getElementById('pesan');

        function error(teks) {
            pesan.textContent = teks;
            pesan.style.display = 'block';
        }

        document.getElementById('btnBayar').addEventListener('click', function () {
            pesan.style.display = 'none';

            var pilih = document.querySelector('input[name=metode]:checked');
            if (!pilih) {
                error('Pilih metode pembayaran terlebih dahulu.');
                return;
            }
            if (document.getElementById('gagal').checked) {
                error('Pembayaran gagal diproses. Silakan coba lagi.');
                return;
            }

            document.getElementById('sMetode').textContent = pilih.value;
            tampil('stepStatus');
        });

        document.getElementById('btnBatal').addEventListener('click', function () {
            pesan.style.display = 'none';
            tampil('stepDetail');
        });

        document.getElementById('btnKembali').addEventListener('click', function () {
            tampil('stepForm');
        });

        document.getElementById('btnSelesai').addEventListener('click', function () {
            window.location.href = '/';
        });
    </script>
</body>
</html>
