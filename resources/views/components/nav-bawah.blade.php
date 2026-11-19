{{-- Navigasi bawah khusus HP: Beranda, Lokasi, Booking. --}}
@php
    $item = [
        ['label' => 'Beranda', 'url' => route('home'),     'aktif' => request()->routeIs('home'),
         'ikon' => 'M12 3 2 12h3v8h5v-5h4v5h5v-8h3L12 3Z'],
        ['label' => 'Lokasi',  'url' => url('/cari-parkir'), 'aktif' => request()->is('cari-parkir'),
         'ikon' => 'M12 2a7 7 0 0 0-7 7c0 5.2 7 13 7 13s7-7.8 7-13a7 7 0 0 0-7-7Zm0 9.5A2.5 2.5 0 1 1 12 6.5a2.5 2.5 0 0 1 0 5Z'],
        // Halaman booking belum ada: tampil nonaktif dulu.
        ['label' => 'Booking', 'url' => null,               'aktif' => false,
         'ikon' => 'M4 5a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v3a2 2 0 0 0 0 4v3a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-3a2 2 0 0 0 0-4V5Zm6 0v2m0 3v2m0 3v2'],
    ];
@endphp

<nav aria-label="Navigasi bawah"
     class="fixed inset-x-0 bottom-0 z-30 grid grid-cols-3 rounded-t-3xl border-t border-line bg-surface pb-[max(0.5rem,env(safe-area-inset-bottom))] pt-2 shadow-[0_-4px_16px_var(--color-shadow)] md:hidden">
    @foreach ($item as $i)
        @php
            $kelas = 'flex flex-col items-center gap-0.5 py-1 text-[11px] '
                . ($i['aktif'] ? 'font-semibold text-primary' : 'font-medium text-slate-500');
        @endphp

        @if ($i['url'])
            <a href="{{ $i['url'] }}" @if ($i['aktif']) aria-current="page" @endif
               class="{{ $kelas }} focus-visible:outline-2 focus-visible:outline-focus">
                <svg class="h-6 w-6" fill="{{ $i['aktif'] ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24" aria-hidden="true"><path d="{{ $i['ikon'] }}"/></svg>
                {{ $i['label'] }}
            </a>
        @else
            <span aria-disabled="true" title="Segera hadir" class="{{ $kelas }} opacity-50">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24" aria-hidden="true"><path d="{{ $i['ikon'] }}"/></svg>
                {{ $i['label'] }}
            </span>
        @endif
    @endforeach
</nav>
