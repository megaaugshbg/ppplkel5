@extends('layouts.parkir')

@section('judul', 'Pilih Slot Parkir')
@section('deskripsi', $lokasi['nama'] . ' · ' . $lokasi['alamat'])
@section('kembali', route('lokasi.tipe', $lokasi['id']))

@section('style')
    .lantai-nav { display: flex; align-items: center; justify-content: center; gap: 18px; margin-bottom: 6px; }
    .lantai-nav button { background: var(--color-primary); color: var(--color-on-primary); border: 0; width: 34px; height: 34px; border-radius: 50%; font-size: 18px; cursor: pointer; }
    .lantai-nav button:disabled { background: #cbd5e1; cursor: default; }
    .lantai-nav strong { font-size: 24px; color: var(--color-primary); min-width: 130px; text-align: center; }
    .legend { display: flex; justify-content: center; gap: 18px; font-size: 12px; color: #4b5563; margin-bottom: 18px; }
    .legend i { display: inline-block; width: 11px; height: 11px; border-radius: 3px; margin-right: 5px; vertical-align: -1px; }
    .denah { border: 1px solid var(--color-line); border-radius: 14px; padding: 20px; background: var(--color-surface-alt); }
    .panel { display: none; }
    .panel.aktif { display: grid; grid-template-columns: 1fr 1fr; gap: 12px 120px; }
    .kolom { display: flex; flex-direction: column; gap: 12px; }
    .slot { position: relative; display: block; }
    .slot input { position: absolute; opacity: 0; }
    .slot span { display: flex; align-items: center; justify-content: space-between; height: 56px; padding: 0 16px; border: 1.5px solid var(--color-primary); border-radius: 10px; background: var(--color-surface); color: var(--color-primary); font-weight: 700; font-size: 14px; cursor: pointer; }
    .slot span:hover { background: var(--color-primary-soft); }
    .slot input:checked + span { background: var(--color-primary); color: var(--color-on-primary); }
    .slot input:focus-visible + span { outline: 2px solid var(--color-focus); outline-offset: 2px; }
    .slot.terisi span { background: #e5e7eb; border-color: #d1d5db; color: #9ca3af; cursor: not-allowed; }
    .pintu { text-align: center; font-size: 13px; color: #6b7280; margin: 16px 0 0; }
    .baris { display: flex; justify-content: space-between; gap: 12px; padding: 9px 0; border-bottom: 1px solid var(--color-line); font-size: 14px; }
    .baris span:first-child { color: #6b7280; }
    .baris span:last-child { color: var(--color-primary); font-weight: 600; text-align: right; }
    .total { display: flex; justify-content: space-between; align-items: center; margin: 14px 0; }
    .total strong { color: var(--color-primary); font-size: 20px; }
    .simulasi { font-size: 12px; color: #6b7280; margin: 0 0 14px; }
    .btn:disabled { background: var(--color-surface-alt); color: var(--color-primary); opacity: .6; cursor: not-allowed; }
    .segera { text-align: center; font-size: 13px; font-weight: 600; color: var(--color-primary); margin: 8px 0 12px; }
    @media (max-width: 640px) { .panel.aktif { gap: 12px 24px; } }
@endsection

@section('isi')
    @if (session('error'))
        <div class="info warn">{{ session('error') }}</div>
    @endif

    @if ($tersedia === 0)
        <div class="info warn">Tidak ada slot parkir yang tersedia. Silakan pilih tipe atau lokasi lain.</div>
        <a href="{{ route('lokasi.tipe', $lokasi['id']) }}" class="btn sec" style="max-width:220px">Kembali</a>
    @else
        <form method="POST" action="{{ route('pesan-slot') }}" class="grid">
            @csrf
            <input type="hidden" name="lokasi" value="{{ $lokasi['id'] }}">
            <input type="hidden" name="tipe" value="{{ $tipe }}">

            <section class="kartu">
                <div class="lantai-nav">
                    <button type="button" id="prev" aria-label="Lantai sebelumnya">&lsaquo;</button>
                    <strong id="labelLantai">Lantai 1</strong>
                    <button type="button" id="next" aria-label="Lantai berikutnya">&rsaquo;</button>
                </div>
                <div class="legend">
                    <span><i style="background:#e5e7eb"></i>Terisi</span>
                    <span><i style="background:var(--color-surface);border:1.5px solid var(--color-primary)"></i>Tersedia ({{ $tersedia }})</span>
                    <span><i style="background:var(--color-primary)"></i>Dipilih</span>
                </div>

                <div class="denah">
                    @foreach ($perLantai as $lantai => $daftar)
                        @php
                            $half = (int) ceil($daftar->count() / 2);
                            $kolom = [$daftar->take($half), $daftar->slice($half)];
                        @endphp
                        <div class="panel {{ $loop->first ? 'aktif' : '' }}" data-lantai="{{ $lantai }}">
                            @foreach ($kolom as $isi)
                                <div class="kolom">
                                    @foreach ($isi as $s)
                                        <label class="slot {{ $s['terisi'] ? 'terisi' : '' }}">
                                            <input type="radio" name="slot" value="{{ $s['kode'] }}" {{ $s['terisi'] ? 'disabled' : '' }}>
                                            <span>{{ $s['kode'] }} @if ($s['terisi'])<em style="font-style:normal">&#128663;</em>@endif</span>
                                        </label>
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                    <p class="pintu">&darr; Pintu Masuk</p>
                </div>
            </section>

            <aside class="panel-samping kartu">
                <span class="pill">Ringkasan Pesanan</span>
                <div class="baris"><span>Tempat</span><span>{{ $lokasi['nama'] }}</span></div>
                <div class="baris"><span>Tipe parkir</span><span>{{ $labelTipe }}</span></div>
                <div class="baris"><span>Slot tersedia</span><span>{{ $tersedia }}</span></div>
                <div class="total">
                    <span>Tarif parkir</span>
                    <strong>Rp {{ number_format($lokasi['tarif'], 0, ',', '.') }} / jam</strong>
                </div>

                @if ($bisaPesan)
                    <p class="simulasi"><label><input type="checkbox" name="gagal" value="1"> Simulasikan gagal menyimpan pesanan</label></p>

                    <button type="submit" class="btn">Pesan Slot</button>
                @else
                    {{-- Priority & EV: denah boleh dilihat, pemesanan belum dibuka. --}}
                    <button type="submit" class="btn" disabled aria-describedby="ket-segera">Pesan Slot</button>
                    <p id="ket-segera" class="segera">Segera hadir</p>
                @endif
                <a href="{{ route('lokasi.tipe', $lokasi['id']) }}" class="btn sec">Batal</a>
            </aside>
        </form>
    @endif
@endsection

@section('script')
    <script>
        var panel = document.querySelectorAll('.panel');
        if (panel.length) {
            var i = 0;
            var label = document.getElementById('labelLantai');
            var prev = document.getElementById('prev');
            var next = document.getElementById('next');

            function tampil() {
                panel.forEach(function (p, n) { p.classList.toggle('aktif', n === i); });
                label.textContent = 'Lantai ' + panel[i].dataset.lantai;
                prev.disabled = i === 0;
                next.disabled = i === panel.length - 1;
            }

            prev.addEventListener('click', function () { if (i > 0) { i--; tampil(); } });
            next.addEventListener('click', function () { if (i < panel.length - 1) { i++; tampil(); } });
            tampil();
        }
    </script>
@endsection
