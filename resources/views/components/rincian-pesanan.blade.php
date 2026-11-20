{{-- Rincian pesanan (dipakai di Pembayaran, Check In, dan Detail Booking). --}}
@props(['pesanan'])

<dl class="divide-y divide-line rounded-2xl border border-line bg-surface text-sm">
    <div class="flex justify-between gap-4 p-3">
        <dt class="text-slate-500">Kode pesanan</dt>
        <dd class="text-right font-semibold text-primary">{{ $pesanan['kode'] }}</dd>
    </div>
    <div class="flex justify-between gap-4 p-3">
        <dt class="text-slate-500">Lokasi</dt>
        <dd class="text-right font-medium text-primary">{{ $pesanan['lokasi'] }}</dd>
    </div>
    <div class="flex justify-between gap-4 p-3">
        <dt class="text-slate-500">Alamat</dt>
        <dd class="text-right">{{ $pesanan['alamat'] }}</dd>
    </div>
    <div class="flex justify-between gap-4 p-3">
        <dt class="text-slate-500">Tipe slot</dt>
        <dd class="text-right">{{ $pesanan['tipe'] }}</dd>
    </div>
    <div class="flex justify-between gap-4 p-3">
        <dt class="text-slate-500">Slot</dt>
        <dd class="text-right font-medium text-primary">{{ $pesanan['slot'] }}</dd>
    </div>

    @if (! empty($pesanan['konektor']))
        <div class="flex justify-between gap-4 p-3">
            <dt class="text-slate-500">Charger</dt>
            <dd class="text-right">⚡ {{ $pesanan['konektor'] }} · {{ $pesanan['daya'] }} kW</dd>
        </div>
    @endif

    <div class="flex justify-between gap-4 p-3">
        <dt class="text-slate-500">Tarif parkir</dt>
        <dd class="text-right">Rp {{ number_format($pesanan['tarif'], 0, ',', '.') }}/jam</dd>
    </div>

    {{ $slot }}

    <div class="flex justify-between gap-4 p-3">
        <dt class="text-slate-500">Status</dt>
        <dd class="text-right"><span class="rounded-full bg-primary-soft px-2.5 py-0.5 text-xs font-semibold text-primary">{{ $pesanan['status'] }}</span></dd>
    </div>
</dl>
