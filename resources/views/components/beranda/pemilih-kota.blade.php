{{--
    Pemilih kota: "📍 Surabaya ⌄". Daftar kota dari tabel lokasi_parkir.
    Pilihan dikirim sebagai ?kota=..., disimpan di session oleh HomeController.
    Tombol [data-gps-ulang] ditangani skrip di home.blade.php.
--}}
@props(['daftarKota', 'kota' => null])

<details class="group relative" data-pemilih-kota>
    <summary class="inline-flex cursor-pointer list-none items-center gap-1.5 rounded-full px-1 py-1 text-base font-semibold text-primary hover:opacity-80 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus [&::-webkit-details-marker]:hidden">
        <svg class="h-5 w-5 shrink-0" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2a7 7 0 0 0-7 7c0 5.2 7 13 7 13s7-7.8 7-13a7 7 0 0 0-7-7Zm0 9.5A2.5 2.5 0 1 1 12 6.5a2.5 2.5 0 0 1 0 5Z"/></svg>
        <span class="sr-only">Kota:</span>
        <span>{{ $kota ?? 'Pilih kota' }}</span>
        <svg class="h-4 w-4 shrink-0 transition group-open:rotate-180" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
    </summary>

    <div class="absolute left-0 z-20 mt-2 w-64 overflow-hidden rounded-2xl border border-line bg-surface shadow-lg shadow-[var(--color-shadow)]">
        <button type="button" data-gps-ulang
                class="flex w-full items-center gap-2 border-b border-line px-4 py-3 text-left text-sm font-semibold text-primary hover:bg-surface-alt focus-visible:bg-surface-alt focus-visible:outline-none">
            <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="3"/><path d="M12 2v3M12 19v3M2 12h3M19 12h3"/><circle cx="12" cy="12" r="7"/></svg>
            Gunakan lokasi saya
        </button>

        @if ($daftarKota->isEmpty())
            <p class="px-4 py-3 text-sm text-slate-500">Belum ada kota.</p>
        @else
            <ul class="max-h-72 overflow-y-auto py-1" aria-label="Daftar kota">
                @foreach ($daftarKota as $k)
                    <li>
                        <a href="{{ route('home', ['kota' => $k]) }}" @if ($k === $kota) aria-current="true" @endif
                           class="flex items-center justify-between px-4 py-2.5 text-sm hover:bg-surface-alt focus-visible:bg-surface-alt focus-visible:outline-none {{ $k === $kota ? 'font-semibold text-primary' : 'text-slate-600' }}">
                            {{ $k }}
                            @if ($k === $kota)
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path d="m5 12 5 5 9-10"/></svg>
                            @endif
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</details>
