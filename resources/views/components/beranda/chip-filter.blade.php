{{--
    Chip filter beranda: Semua / Normal / Woman / Priority / EV Charging (?tipe=),
    lalu Terdekat (?urut=jarak) yang hanya aktif bila posisi GPS tersedia.
    Pencarian (?q=) dipertahankan saat chip diklik.
--}}
@props(['tipe' => null, 'urut' => null, 'q' => '', 'adaPosisi' => false, 'labelTipe' => []])

@php
    $url = fn (array $ubah) => route('home', array_filter(
        array_merge(['q' => $q, 'tipe' => $tipe, 'urut' => $urut], $ubah),
        fn ($v) => $v !== null && $v !== ''
    ));

    $chips = [['label' => 'Semua', 'tipe' => null]];
    foreach ($labelTipe as $nilai => $label) {
        $chips[] = ['label' => $label, 'tipe' => $nilai];
    }

    $dasar = 'inline-flex shrink-0 items-center gap-1.5 rounded-full border px-4 py-1.5 text-sm font-medium transition focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus';
    $aktif = 'border-primary bg-primary text-on-primary';
    $biasa = 'border-line bg-surface text-primary hover:bg-surface-alt';
@endphp

<nav aria-label="Filter mal" class="-mx-4 flex gap-2 overflow-x-auto px-4 pb-1 [scrollbar-width:none] md:mx-0 md:flex-wrap md:px-0">
    @foreach ($chips as $c)
        @php $on = $tipe === $c['tipe']; @endphp
        <a href="{{ $url(['tipe' => $c['tipe']]) }}" @if ($on) aria-current="true" @endif
           class="{{ $dasar }} {{ $on ? $aktif : $biasa }}">{{ $c['label'] }}</a>
    @endforeach

    <span class="mx-1 w-px shrink-0 self-stretch bg-line" aria-hidden="true"></span>

    @if ($adaPosisi)
        @php $on = $urut === 'jarak'; @endphp
        <a href="{{ $url(['urut' => $on ? null : 'jarak']) }}" aria-pressed="{{ $on ? 'true' : 'false' }}"
           class="{{ $dasar }} {{ $on ? $aktif : $biasa }}">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path d="m3 11 18-8-8 18-2-8-8-2Z"/></svg>
            Terdekat
        </a>
    @else
        <span aria-disabled="true" title="Izinkan akses lokasi untuk memakai filter ini"
              class="{{ $dasar }} cursor-not-allowed border-line bg-surface text-slate-400">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path d="m3 11 18-8-8 18-2-8-8-2Z"/></svg>
            Terdekat
        </span>
    @endif
</nav>
