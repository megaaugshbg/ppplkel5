{{--
    Riwayat booking terakhir di beranda (RiwayatController::terbaru).
    Item: lokasi, tipe, waktu, status (label), status_kode (opsional, untuk ikon badge), url (opsional, ke detail).
--}}
@props(['items' => collect(), 'urlSemua' => '#'])

<section aria-labelledby="judul-riwayat" class="rounded-3xl border border-line bg-surface p-5 md:p-6">
    <div class="flex items-center justify-between gap-3">
        <h2 id="judul-riwayat" class="text-base font-semibold">Riwayat terakhir</h2>
        <a href="{{ $urlSemua }}" class="text-sm font-semibold underline underline-offset-4 hover:opacity-80">Lihat semua</a>
    </div>

    @if (collect($items)->isEmpty())
        <div class="mt-4 flex items-center gap-4 rounded-2xl bg-surface-alt p-4">
            <span class="grid h-11 w-11 shrink-0 place-items-center rounded-full bg-surface text-primary">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path d="M3 12a9 9 0 1 0 3-6.7L3 8"/><path d="M3 3v5h5M12 7v5l3 2"/></svg>
            </span>
            <div>
                <p class="text-sm font-semibold">Belum ada riwayat booking</p>
                <p class="mt-0.5 text-xs text-slate-500">Pesan slot dari daftar mal di atas, riwayatnya akan muncul di sini.</p>
            </div>
        </div>
    @else
        <ul class="mt-4 divide-y divide-line">
            @foreach ($items as $item)
                <li>
                    @php $isi = 'flex items-center justify-between gap-3 py-3'; @endphp
                    @if (! empty($item['url']))
                        <a href="{{ $item['url'] }}" class="{{ $isi }} -mx-2 rounded-xl px-2 hover:bg-surface-alt focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus">
                    @else
                        <div class="{{ $isi }}">
                    @endif
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold">{{ $item['lokasi'] }}</p>
                            <p class="text-xs text-slate-500">{{ $item['tipe'] }} &middot; {{ $item['waktu'] }}</p>
                        </div>
                        <x-badge-status :status="$item['status_kode'] ?? null" :label="$item['status']" />
                    @if (! empty($item['url']))
                        </a>
                    @else
                        </div>
                    @endif
                </li>
            @endforeach
        </ul>
    @endif
</section>
