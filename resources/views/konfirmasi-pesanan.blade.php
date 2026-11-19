@extends('layouts.parkir')

@section('judul', 'Slot Berhasil Dipesan')
@section('deskripsi', 'Simpan kode booking kamu, lalu lanjutkan ke pembayaran.')
@section('kembali', '/home')

@section('style')
    .baris { display: flex; justify-content: space-between; gap: 16px; padding: 10px 0; border-bottom: 1px solid var(--color-line); font-size: 14px; }
    .baris:last-child { border-bottom: 0; }
    .baris span:first-child { color: #6b7280; }
    .baris span:last-child { color: var(--color-primary); font-weight: 600; text-align: right; }
    .kode { text-align: center; background: var(--color-primary-soft); border: 1px solid var(--color-line); border-radius: 14px; padding: 28px 16px; }
    .kode small { display: block; color: #6b7280; font-size: 13px; }
    .kode strong { display: block; font-size: 56px; color: var(--color-primary); letter-spacing: 3px; line-height: 1.1; margin: 6px 0; }
    .kode span { font-size: 13px; color: var(--color-primary); opacity: .8; }
@endsection

@section('isi')
    <div class="info ok">Pesanan Anda berhasil disimpan.</div>

    <div class="grid">
        <section class="kartu">
            <h2>Detail Pesanan</h2>
            <div class="baris"><span>Kode booking</span><span>{{ $pesanan['kode'] }}</span></div>
            <div class="baris"><span>Tempat</span><span>{{ $pesanan['lokasi'] }}</span></div>
            <div class="baris"><span>Alamat</span><span>{{ $pesanan['alamat'] }}</span></div>
            <div class="baris"><span>Tipe parkir</span><span>{{ $pesanan['tipe'] }}</span></div>
            <div class="baris"><span>Tarif</span><span>Rp {{ number_format($pesanan['tarif'], 0, ',', '.') }} / jam</span></div>
            <div class="baris"><span>Status</span><span>{{ $pesanan['status'] }}</span></div>
        </section>

        <aside class="panel-samping kartu">
            <div class="kode">
                <small>Kode Parkir</small>
                <strong>{{ $pesanan['slot'] }}</strong>
                <span>{{ $pesanan['lokasi'] }}</span>
            </div>
            <div style="margin-top:16px">
                <a href="/pembayaran" class="btn">Lanjut ke Pembayaran</a>
                <a href="/home" class="btn sec">Kembali ke Beranda</a>
            </div>
        </aside>
    </div>
@endsection
