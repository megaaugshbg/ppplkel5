{{--
    Kartu mal di beranda. $mal berasal dari HomeController::daftarMal():
    nama, area, foto (URL, sudah fallback ke default), buka, keteranganTutup, jam, jarak (km|null),
    kosong (jumlah slot kosong), tipeKosong (tipe yang masih ada slot kosong), url.
--}}
@props(['mal', 'labelTipe' => [], 'tipe' => null])

@php
    $jarak = $mal['jarak'];
    $teksJarak = $jarak === null ? null : number_format($jarak, 1, ',', '.') . ' km';
@endphp

{{-- Mal yang tutup tetap bisa dibuka (lihat tipe & denah), tapi tidak bisa dipesan. --}}
<a href="{{ $mal['url'] }}"
   class="group flex w-[16.5rem] shrink-0 flex-col overflow-hidden rounded-2xl border border-line bg-surface shadow-sm transition hover:-translate-y-0.5 hover:shadow-md focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus md:w-full">

    <div class="relative aspect-[16/10] bg-surface-alt">
        <img src="{{ $mal['foto'] }}" alt="" loading="lazy"
             onerror="this.onerror=null;this.src='{{ asset(\App\Models\LokasiParkir::FOTO_DEFAULT) }}'"
             class="h-full w-full object-cover {{ $mal['buka'] ? '' : 'opacity-60 grayscale' }}">

        @unless ($mal['buka'])
            <span class="absolute left-3 top-3 rounded-full bg-primary px-2.5 py-1 text-xs font-semibold text-on-primary">Tutup</span>
        @endunless

    </div>

    <div class="flex flex-1 flex-col gap-1.5 p-4">
        <h3 class="line-clamp-2 font-semibold leading-snug">{{ $mal['nama'] }}</h3>
        <p class="text-xs text-slate-500">
            {{ $mal['area'] ?? '' }}@if ($mal['area'] && $mal['jam']) &middot; @endif{{ $mal['jam'] ?? '' }}
        </p>
        @if ($mal['keteranganTutup'])
            <p class="inline-flex items-center gap-1 text-xs font-semibold text-primary">
                <svg class="h-3.5 w-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
                {{ $mal['keteranganTutup'] }}
            </p>
        @endif

        <p class="mt-1 text-sm">
            @if ($mal['kosong'] > 0)
                <strong class="font-semibold">{{ $mal['kosong'] }}</strong>
                <span class="text-slate-600">slot kosong{{ $tipe ? ' · ' . ($labelTipe[$tipe] ?? '') : '' }}</span>
            @else
                <span class="text-slate-500">Slot penuh</span>
            @endif
        </p>

        @if ($teksJarak)
            <p class="inline-flex items-center gap-1 text-xs font-semibold text-primary">
                <svg class="h-3.5 w-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" aria-hidden="true"><path d="m3 11 18-8-8 18-2-8-8-2Z"/></svg>
                {{ $teksJarak }} dari lokasimu
            </p>
        @endif

        @if ($mal['tipeKosong'])
            <ul class="mt-auto flex flex-wrap gap-1.5 pt-2" aria-label="Tipe slot tersedia">
                @foreach ($mal['tipeKosong'] as $t)
                    <li class="rounded-full bg-primary-soft px-2 py-0.5 text-[11px] font-semibold text-primary">
                        {{ $t === \App\Models\SlotParkir::TIPE_EV ? 'EV' : ($labelTipe[$t] ?? $t) }}
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</a>
