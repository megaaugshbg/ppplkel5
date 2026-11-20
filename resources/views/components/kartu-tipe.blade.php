{{--
    Kartu satu tipe slot di halaman Pilih Tipe Slot.
    $tipe: kode, label, total, tersedia, bisaPesan, syarat (petunjuk bila syarat profil
    belum terpenuhi, atau null), url (dari SlotController::pilihTipe).
    Nonaktif bila mal tidak punya tipe ini ("Tidak tersedia") atau semua slot terpakai ("Penuh").
--}}
@props(['tipe'])

@php
    $keterangan = [
        'reguler'   => 'Slot reguler untuk semua pengguna.',
        'wanita'    => 'Khusus wanita: dekat pintu masuk/lift, lebih lebar, terpantau CCTV.',
        'prioritas' => 'Untuk penyandang disabilitas, dengan verifikasi kartu disabilitas.',
        'ev'        => 'Dilengkapi stasiun pengisian kendaraan listrik.',
    ][$tipe['kode']] ?? '';

    $ikon = [
        'reguler'   => 'M7 17h10M5 17V9.5L7 5h10l2 4.5V17M5 17v2M19 17v2M5 12h14M8 14.5h.01M16 14.5h.01',
        'wanita'    => 'M12 14a5 5 0 1 0 0-10 5 5 0 0 0 0 10Zm0 0v7m-3-3h6',
        'prioritas' => 'M11 4.5a1.5 1.5 0 1 0 0-.01M9 8h4l1 5h4l2 5M10 8l-.5 5M7 12.5A5 5 0 1 0 14 18',
        'ev'        => 'M13 2 4 14h7l-1 8 9-12h-7l1-8Z',
    ][$tipe['kode']] ?? '';

    $status = match (true) {
        $tipe['total'] === 0    => 'Tidak tersedia',
        $tipe['tersedia'] === 0 => 'Penuh',
        default                 => null,
    };
@endphp

@if ($status)
    <div aria-disabled="true" class="flex h-full flex-col gap-3 rounded-2xl border border-line bg-surface-alt p-5 text-slate-500">
        <span class="grid h-12 w-12 place-items-center rounded-xl bg-surface text-slate-400">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true"><path d="{{ $ikon }}"/></svg>
        </span>
        <div>
            <h3 class="font-semibold">{{ $tipe['label'] }}</h3>
            <p class="mt-1 text-xs leading-relaxed">{{ $keterangan }}</p>
        </div>
        <p class="mt-auto text-sm font-semibold">{{ $status }}</p>
    </div>
@else
    <a href="{{ $tipe['url'] }}"
       class="group flex h-full flex-col gap-3 rounded-2xl border border-line bg-surface p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-primary hover:shadow-md focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus">
        <span class="grid h-12 w-12 place-items-center rounded-xl bg-primary text-on-primary">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true"><path d="{{ $ikon }}"/></svg>
        </span>
        <div>
            <h3 class="font-semibold">{{ $tipe['label'] }}</h3>
            <p class="mt-1 text-xs leading-relaxed text-slate-500">{{ $keterangan }}</p>
        </div>
        <div class="mt-auto flex items-end justify-between gap-2">
            <p class="text-sm"><strong class="text-lg font-bold">{{ $tipe['tersedia'] }}</strong> <span class="text-slate-600">slot kosong</span></p>
            <svg class="h-5 w-5 shrink-0 transition group-hover:translate-x-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path d="m9 6 6 6-6 6"/></svg>
        </div>
        @unless ($tipe['bisaPesan'])
            <p class="rounded-lg bg-primary-soft px-2.5 py-1 text-[11px] font-semibold text-primary">Pemesanan segera hadir &middot; lihat denah</p>
        @endunless
        @if (! empty($tipe['syarat']))
            {{-- Syarat profil belum terpenuhi: kartu tetap bisa dibuka untuk melihat denah. --}}
            <p class="inline-flex items-center gap-1.5 rounded-lg bg-surface-alt px-2.5 py-1 text-[11px] font-semibold text-primary">
                <svg class="h-3.5 w-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 8v4M12 16h.01"/></svg>
                {{ $tipe['syarat'] }}
            </p>
        @endif
    </a>
@endif
