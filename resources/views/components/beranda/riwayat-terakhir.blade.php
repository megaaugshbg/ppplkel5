{{--
    Riwayat booking terakhir.
    Tabel booking BELUM ADA, jadi HomeController selalu mengirim koleksi kosong.
    Nanti kirim item dengan kunci: lokasi, tipe, waktu, status (dan opsional url).
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
                <li class="flex items-center justify-between gap-3 py-3">
                    <div class="min-w-0">
                        <p class="truncate text-sm font-semibold">{{ $item['lokasi'] }}</p>
                        <p class="text-xs text-slate-500">{{ $item['tipe'] }} &middot; {{ $item['waktu'] }}</p>
                    </div>
                    <span class="shrink-0 rounded-full bg-primary-soft px-2.5 py-0.5 text-xs font-semibold text-primary">{{ $item['status'] }}</span>
                </li>
            @endforeach
        </ul>
    @endif
</section>
