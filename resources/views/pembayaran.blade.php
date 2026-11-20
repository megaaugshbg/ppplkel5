{{-- Form Pembayaran (use case Lakukan Pembayaran). --}}
@extends('layouts.app')

@section('title', 'Pembayaran - Markir.In')

@section('content')
<div class="mx-auto max-w-xl space-y-5 px-4 pt-5 md:px-6 md:pt-8">

    <a href="{{ route('konfirmasi-pesanan') }}" class="inline-flex items-center gap-1 text-sm font-semibold hover:opacity-80 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
        Kembali ke pesanan
    </a>

    <div>
        <h1 class="text-xl font-bold md:text-2xl">Pembayaran</h1>
        <p class="text-sm text-slate-500">Cek rincian tagihan, lalu bayar sebelum check in.</p>
    </div>

    @if (session('error') || $errors->any())
        <div role="alert" class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-medium text-amber-800">
            {{ session('error') ?? $errors->first() }}
        </div>
    @endif

    {{-- Rincian tagihan --}}
    <section aria-labelledby="judul-tagihan" class="space-y-3">
        <h2 id="judul-tagihan" class="text-base font-semibold">Rincian tagihan</h2>
        <x-rincian-pesanan :pesanan="$pesanan" />
    </section>

    <form method="POST" action="{{ route('pembayaran.bayar') }}" class="space-y-5">
        @csrf

        {{-- Lama parkir --}}
        <fieldset class="space-y-3">
            <legend class="text-base font-semibold">Lama parkir</legend>
            <div class="grid grid-cols-3 gap-2 sm:grid-cols-6">
                @foreach ($durasi as $jam)
                    <label class="cursor-pointer">
                        <input type="radio" name="durasi" value="{{ $jam }}" class="peer sr-only"
                               @checked((int) old('durasi', 2) === $jam)>
                        <span class="block rounded-xl border border-line bg-surface py-2.5 text-center text-sm font-medium peer-checked:border-primary peer-checked:bg-primary-soft peer-checked:font-semibold peer-focus-visible:outline-2 peer-focus-visible:outline-focus hover:bg-surface-alt">
                            {{ $jam }} jam
                        </span>
                    </label>
                @endforeach
            </div>
        </fieldset>

        {{-- Metode pembayaran --}}
        <fieldset class="space-y-3">
            <legend class="text-base font-semibold">Metode pembayaran</legend>
            <label class="flex cursor-pointer items-center gap-4 rounded-2xl border border-primary bg-surface p-4">
                <input type="radio" name="metode" value="QRIS" checked class="h-4 w-4 accent-[var(--color-primary)]">
                <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-primary-soft text-primary">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path d="M4 4h6v6H4zM14 4h6v6h-6zM4 14h6v6H4zM14 14h2v2h-2zM18 14h2v2h-2zM14 18h2v2h-2zM18 18h2v2h-2z"/></svg>
                </span>
                <span>
                    <span class="block text-sm font-semibold">QRIS</span>
                    <span class="block text-xs text-slate-500">Scan pakai e-wallet atau m-banking yang mendukung QRIS.</span>
                </span>
            </label>
        </fieldset>

        @if (! empty($pesanan['tarif_ev_kwh']))
            <p class="rounded-xl bg-emerald-50 p-3 text-xs text-emerald-800">
                Biaya charging (Rp {{ number_format($pesanan['tarif_ev_kwh'], 0, ',', '.') }}/kWh) dihitung sesuai pemakaian dan ditagihkan terpisah.
            </p>
        @endif

        {{-- Total --}}
        <div class="flex items-center justify-between rounded-2xl bg-surface-alt px-4 py-3">
            <span class="text-sm text-slate-600">Total biaya (<span id="teks-durasi">{{ old('durasi', 2) }}</span> jam)</span>
            <strong id="teks-total" class="text-lg">Rp {{ number_format($pesanan['tarif'] * old('durasi', 2), 0, ',', '.') }}</strong>
        </div>

        <label class="flex items-center gap-2 text-xs text-slate-500">
            <input type="checkbox" name="gagal" value="1" class="accent-[var(--color-primary)]">
            Simulasikan pembayaran gagal
        </label>

        <button type="submit" id="tombol-bayar"
                class="w-full rounded-xl bg-primary px-4 py-3 text-sm font-semibold text-on-primary hover:bg-primary-hover focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus">
            Konfirmasi Pembayaran
        </button>
    </form>

    <form method="POST" action="{{ route('pembayaran.batal') }}">
        @csrf
        <button type="submit"
                class="w-full rounded-xl border border-line px-4 py-3 text-sm font-medium text-primary hover:bg-surface-alt focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus">
            Batalkan Pembayaran
        </button>
    </form>
</div>
@endsection

@push('scripts')
<script>
    // Hitung ulang total saat lama parkir diganti
    const tarif = {{ (int) $pesanan['tarif'] }};
    const rupiah = n => 'Rp ' + n.toLocaleString('id-ID');

    document.querySelectorAll('input[name="durasi"]').forEach(r => {
        r.addEventListener('change', () => {
            document.getElementById('teks-durasi').textContent = r.value;
            document.getElementById('teks-total').textContent = rupiah(tarif * r.value);
        });
    });
</script>
@endpush
