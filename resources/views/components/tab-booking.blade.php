{{-- Tab "Aktif | Riwayat" di halaman Booking Aktif dan Riwayat (bagian dari menu Booking). --}}
@props(['aktif' => 'aktif']) {{-- 'aktif' | 'riwayat' --}}

@php
    $tab = [
        'aktif'   => ['label' => 'Aktif',   'url' => route('check-out')],
        'riwayat' => ['label' => 'Riwayat', 'url' => route('riwayat')],
    ];
@endphp

<nav aria-label="Booking" class="grid grid-cols-2 gap-1 rounded-xl border border-line bg-surface-alt p-1">
    @foreach ($tab as $kunci => $t)
        <a href="{{ $t['url'] }}" @if ($aktif === $kunci) aria-current="page" @endif
           class="rounded-lg px-4 py-2 text-center text-sm font-semibold transition focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus {{ $aktif === $kunci ? 'bg-surface text-primary shadow-sm' : 'text-slate-500 hover:text-primary' }}">
            {{ $t['label'] }}
        </a>
    @endforeach
</nav>
