{{-- Navigasi bawah khusus HP: Beranda, Booking, Pengaturan. --}}
@php
    $item = [
        ['label' => 'Beranda', 'url' => route('home'),     'aktif' => request()->routeIs('home'),
         'ikon' => 'M12 3 2 12h3v8h5v-5h4v5h5v-8h3L12 3Z'],
        // Booking -> halaman Booking Aktif (awal use case Check-out).
        ['label' => 'Booking', 'url' => route('check-out'), 'aktif' => request()->routeIs('check-out*'),
         'ikon' => 'M4 5a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v3a2 2 0 0 0 0 4v3a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-3a2 2 0 0 0 0-4V5Zm6 0v2m0 3v2m0 3v2'],
        ['label' => 'Pengaturan', 'url' => route('profil'), 'aktif' => request()->routeIs('profil'),
         'ikon' => 'M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Zm7.4-3a7.4 7.4 0 0 0-.1-1.2l2-1.6-2-3.4-2.4 1a7.5 7.5 0 0 0-2-1.2L14.5 3h-5l-.4 2.6a7.5 7.5 0 0 0-2 1.2l-2.4-1-2 3.4 2 1.6a7.4 7.4 0 0 0 0 2.4l-2 1.6 2 3.4 2.4-1a7.5 7.5 0 0 0 2 1.2l.4 2.6h5l.4-2.6a7.5 7.5 0 0 0 2-1.2l2.4 1 2-3.4-2-1.6c.1-.4.1-.8.1-1.2Z'],
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
