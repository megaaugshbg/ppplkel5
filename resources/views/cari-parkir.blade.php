@extends('layouts.parkir')

@section('judul', 'Cari Lokasi Parkir')
@section('deskripsi', 'Temukan lahan parkir terdekat dari lokasimu, lalu pilih slot yang kamu mau.')

@section('style')
    .cari { display: flex; gap: 10px; margin-bottom: 18px; }
    .cari input { flex: 1; min-width: 0; padding: 13px 18px; border: 1px solid var(--garis); background: #fff; border-radius: 999px; font-size: 15px; outline: none; }
    .cari input:focus { border-color: var(--biru); }
    .gps { background: #fff; border: 1px solid var(--biru); color: var(--biru); border-radius: 999px; padding: 0 20px; font-size: 14px; font-weight: 600; cursor: pointer; white-space: nowrap; }
    .gps:hover { background: var(--pucat); }
    #daftar { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    .lokasi { display: flex; flex-direction: column; gap: 12px; padding: 18px; }
    .lokasi .atas { display: flex; gap: 12px; }
    .ikon { flex: none; width: 48px; height: 48px; border-radius: 12px; background: var(--navy); color: #fff; font-weight: 900; font-size: 22px; display: flex; align-items: center; justify-content: center; }
    .lokasi.tutup .ikon { background: #9ca3af; }
    .lokasi h3 { margin: 0; font-size: 16px; color: var(--navy); }
    .lokasi p { margin: 3px 0 0; font-size: 13px; color: #6b7280; }
    .meta { display: flex; flex-wrap: wrap; gap: 6px; }
    .chip { font-size: 12px; font-weight: 600; padding: 4px 10px; border-radius: 999px; background: var(--pucat); border: 1px solid var(--garis); color: var(--navy); }
    .chip.hijau { background: #d1fae5; border-color: #a7f3d0; color: #065f46; }
    .chip.merah { background: #fee2e2; border-color: #fecaca; color: #b91c1c; }
    .map { background: var(--pucat); border: 2px dashed #93abf0; border-radius: 12px; height: 260px; display: flex; align-items: center; justify-content: center; text-align: center; padding: 16px; color: var(--navy); font-size: 14px; }
    .panel-samping ul { margin: 14px 0 0; padding-left: 18px; color: #4b5563; font-size: 13px; line-height: 1.7; }
    #kosong { display: none; text-align: center; color: #6b7280; padding: 32px; font-size: 15px; }
    @media (max-width: 640px) { #daftar { grid-template-columns: 1fr; } }
@endsection

@section('isi')
    @if (session('pesan'))
        <div class="info warn">{{ session('pesan') }}</div>
    @endif
    <div id="pesan" class="info" style="display:none;"></div>

    <div class="grid">
        <section>
            <div class="cari">
                <input type="search" id="keyword" value="{{ request('q') }}" placeholder="Cari nama lokasi atau mall...">
                <button class="gps" id="btnGps" type="button">Gunakan lokasi saya</button>
            </div>

            <div id="daftar">
                @foreach ($lokasi as $l)
                    <div class="kartu lokasi {{ $l['buka'] ? '' : 'tutup' }}"
                         data-id="{{ $l['id'] }}"
                         data-teks="{{ strtolower($l['nama'].' '.$l['alamat']) }}"
                         data-buka="{{ $l['buka'] ? 1 : 0 }}">
                        <div class="atas">
                            <div class="ikon">P</div>
                            <div>
                                <h3>{{ $l['nama'] }}</h3>
                                <p>{{ $l['alamat'] }}</p>
                            </div>
                        </div>
                        <div class="meta">
                            <span class="chip jarak">{{ $l['jarak'] }} km</span>
                            <span class="chip">Rp {{ number_format($l['tarif'], 0, ',', '.') }} / jam</span>
                            @if ($l['buka'])
                                <span class="chip hijau">{{ $l['slot_tipe']['reguler'] ?? 0 }} slot tersedia</span>
                            @else
                                <span class="chip merah">Sedang tutup</span>
                            @endif
                        </div>
                        <a href="#" class="btn pilih {{ $l['buka'] ? '' : 'off' }}">Pilih Lokasi</a>
                    </div>
                @endforeach
            </div>

            <p id="kosong">Lokasi tidak ditemukan. Silakan ubah kata kunci pencarian.</p>
        </section>

        <aside class="panel-samping kartu">
            <h2>Peta Sekitar Kamu</h2>
            <div class="map">Peta sekitar lokasi Penyewa (placeholder)</div>
            <ul>
                <li>Pilih lokasi untuk melihat denah slot per lantai.</li>
                <li>Lokasi yang tutup tidak bisa dipesan.</li>
                <li>Aktifkan lokasi agar jarak dihitung dari posisimu.</li>
            </ul>
        </aside>
    </div>
@endsection

@section('script')
    <script>
        var kw = document.getElementById('keyword');
        var pesan = document.getElementById('pesan');
        var kosong = document.getElementById('kosong');
        var cards = document.querySelectorAll('#daftar .lokasi');

        function tampilPesan(teks, jenis) {
            pesan.textContent = teks;
            pesan.className = 'info ' + jenis;
            pesan.style.display = 'block';
        }

        function filter() {
            var k = kw.value.toLowerCase();
            var n = 0;
            cards.forEach(function (c) {
                var cocok = c.dataset.teks.indexOf(k) !== -1;
                c.style.display = cocok ? 'flex' : 'none';
                if (cocok) { n++; }
            });
            kosong.style.display = n ? 'none' : 'block';
        }

        kw.addEventListener('input', filter);

        cards.forEach(function (c) {
            c.querySelector('.pilih').addEventListener('click', function (e) {
                e.preventDefault();
                if (c.dataset.buka === '0') {
                    tampilPesan('Lokasi parkir sedang tutup. Silakan pilih lahan parkir lain.', 'warn');
                } else {
                    window.location.href = '/slot-parkir/' + c.dataset.id + '?tipe=reguler';
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
@endsection
