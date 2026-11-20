{{--
    Ringkasan check-out (halaman Check-out Berhasil & Struk).
    $struk: data dari session 'struk_terakhir' (CheckoutController::proses).
    Biaya sudah dibayar di muka: Total biaya parkir = jumlah yang sudah dibayar.
    Durasi aktual hanya informasi; tidak ada pelunasan atau pengembalian.
    $lengkap = true (struk): tambah alamat, metode, no. transaksi, dan data charger EV.
--}}
@props(['struk', 'lengkap' => false])

@php
    $rp = fn ($n) => 'Rp ' . number_format((int) $n, 0, ',', '.');
    $baris = fn ($label, $isi) => ['label' => $label, 'isi' => $isi];

    $rows = array_filter([
        $baris('Kode booking', $struk['kode']),
        $baris('Lokasi', $struk['lokasi']),
        $lengkap ? $baris('Alamat', $struk['alamat'] ?? '-') : null,
        $baris('Slot', $struk['slot']),
        $baris('Tipe parkir', $struk['tipe']),
        $baris('Waktu masuk', ($struk['waktu_checkin'] ?? '-') . ' WIB'),
        $baris('Waktu keluar', $struk['waktu_keluar'] . ' WIB'),
        $baris('Durasi parkir', $struk['durasi_aktual'] . ' jam'),
        $baris('Durasi dibayar', ($struk['durasi'] ?? '-') . ' jam'),
        $lengkap && ! empty($struk['metode']) ? $baris('Metode pembayaran', $struk['metode']) : null,
        $lengkap && ! empty($struk['no_transaksi']) ? $baris('No. transaksi', $struk['no_transaksi']) : null,
    ]);
@endphp

<div class="overflow-hidden rounded-2xl border border-line bg-surface">
    <dl class="divide-y divide-line text-sm">
        @foreach ($rows as $r)
            <div class="flex justify-between gap-4 px-4 py-3">
                <dt class="text-slate-500">{{ $r['label'] }}</dt>
                <dd class="text-right font-medium">{{ $r['isi'] }}</dd>
            </div>
        @endforeach

        @if ($lengkap && ! empty($struk['konektor']))
            <div class="flex justify-between gap-4 px-4 py-3">
                <dt class="text-slate-500">Charger</dt>
                <dd class="text-right font-medium">⚡ {{ $struk['konektor'] }} · {{ $struk['daya'] }} kW</dd>
            </div>
            <div class="flex justify-between gap-4 px-4 py-3">
                <dt class="text-slate-500">Tarif pengisian</dt>
                <dd class="text-right">
                    <span class="font-medium">{{ ! empty($struk['tarif_ev_kwh']) ? $rp($struk['tarif_ev_kwh']) . ' / kWh' : '-' }}</span>
                    <span class="block text-xs text-slate-500">Biaya pengisian dihitung terpisah</span>
                </dd>
            </div>
        @endif

        <div class="flex justify-between gap-4 px-4 py-3">
            <dt class="text-slate-500">Sudah dibayar</dt>
            <dd class="text-right font-medium">{{ $rp($struk['total'] ?? 0) }}</dd>
        </div>
        <div class="flex items-center justify-between gap-4 bg-surface-alt px-4 py-3.5">
            <dt class="font-semibold">Total biaya parkir</dt>
            <dd class="text-right text-lg font-bold">{{ $rp($struk['total_biaya']) }}</dd>
        </div>
    </dl>
</div>

@if (($struk['melebihi_jam'] ?? 0) > 0)
    <p role="note" class="mt-3 flex items-start gap-2 rounded-xl border border-line bg-primary-soft px-3 py-2.5 text-xs font-medium text-primary">
        <svg class="mt-px h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 8v4M12 16h.01"/></svg>
        Melebihi durasi yang dibayar {{ $struk['melebihi_jam'] }} jam
    </p>
@endif
