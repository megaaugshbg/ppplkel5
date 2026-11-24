{{-- Landing page Markir.In (pengunjung yang belum login). --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Markir.In - Pesan Slot Parkir Sebelum Berangkat</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=montserrat:400,500,600,700,800" rel="stylesheet" />
    <x-favicon />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-page font-sans text-primary antialiased">

    {{-- ============================== NAVBAR ============================== --}}
    <header class="absolute inset-x-0 top-0 z-20">
        <nav class="mx-auto flex max-w-6xl items-center justify-between px-4 py-4 md:px-8">
            <a href="{{ url('/') }}" aria-label="Markir.In">
                <x-logo :height="44" tone="on-primary" />
            </a>
            <div class="flex items-center gap-2">
                <a href="{{ route('login') }}"
                   class="rounded-xl px-4 py-2 text-sm font-semibold text-on-primary hover:bg-white/10 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-on-primary">
                    Masuk
                </a>
                <a href="{{ route('register') }}"
                   class="rounded-xl bg-on-primary px-4 py-2 text-sm font-semibold text-primary hover:bg-surface-alt focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-on-primary">
                    Daftar
                </a>
            </div>
        </nav>
    </header>

    <main>
        {{-- ============================== HERO ============================== --}}
        <section class="relative isolate overflow-hidden bg-primary">
            <img src="{{ asset('images/mal/CITO.jpg') }}" alt="" class="absolute inset-0 -z-10 h-full w-full object-cover">
            <div class="absolute inset-0 -z-10 bg-primary/70" aria-hidden="true"></div>

            <div class="mx-auto max-w-6xl px-4 pb-24 pt-32 md:px-8 md:pb-32 md:pt-44">
                <div class="max-w-2xl">
                    <p class="mb-4 inline-block rounded-full bg-on-primary/15 px-3 py-1 text-xs font-semibold text-on-primary">
                        Parkir mal tanpa muter-muter
                    </p>
                    <h1 class="text-4xl font-bold leading-tight tracking-tight text-on-primary md:text-6xl">
                        Pesan slot parkir sebelum berangkat.
                    </h1>
                    <p class="mt-5 text-base text-on-primary/90 md:text-lg">
                        Pilih mal, pilih slot di denah, bayar pakai QRIS. Sampai di lokasi tinggal check in dan langsung parkir.
                    </p>
                    <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                        <a href="{{ route('register') }}"
                           class="rounded-2xl bg-on-primary px-7 py-3.5 text-center text-sm font-bold text-primary hover:bg-surface-alt focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-on-primary">
                            Mulai Sekarang
                        </a>
                        <a href="{{ route('login') }}"
                           class="rounded-2xl border border-on-primary/60 px-7 py-3.5 text-center text-sm font-semibold text-on-primary hover:bg-white/10 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-on-primary">
                            Sudah punya akun? Masuk
                        </a>
                    </div>

                    <dl class="mt-10 flex gap-8 text-on-primary">
                        <div>
                            <dt class="text-xs text-on-primary/75">Mal tersedia</dt>
                            <dd class="text-2xl font-bold">{{ $jumlahMal }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-on-primary/75">Kota</dt>
                            <dd class="text-2xl font-bold">{{ $jumlahKota }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-on-primary/75">Tipe slot</dt>
                            <dd class="text-2xl font-bold">4</dd>
                        </div>
                    </dl>
                </div>
            </div>
        </section>

        {{-- ============================== FITUR ============================== --}}
        @php
            $fitur = [
                ['judul' => 'Normal Parking', 'isi' => 'Slot reguler untuk semua pengguna, tinggal pilih dari denah tiap lantai.',
                 'ikon' => 'M5 17h14M6 17V9l2-4h8l2 4v8M8 13h.01M16 13h.01'],
                ['judul' => 'Woman Parking', 'isi' => 'Slot khusus perempuan di area yang lebih dekat dan terang. Verifikasi lewat SIM.',
                 'ikon' => 'M12 14a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm0 0v7m-3-3h6'],
                ['judul' => 'Priority Parking', 'isi' => 'Slot prioritas dekat pintu masuk untuk pengguna dengan kartu disabilitas.',
                 'ikon' => 'M12 6a2 2 0 1 0 0-4 2 2 0 0 0 0 4Zm-2 3h4l1 6h-6l1-6Zm-1 6-2 6m10-6 2 6'],
                ['judul' => 'EV Charging', 'isi' => 'Slot dengan charger mobil listrik. Biaya charging dihitung sesuai pemakaian.',
                 'ikon' => 'm13 2-9 12h7l-1 8 9-12h-7l1-8Z'],
            ];
        @endphp
        <section aria-labelledby="judul-fitur" class="mx-auto max-w-6xl px-4 py-16 md:px-8 md:py-24">
            <h2 id="judul-fitur" class="text-2xl font-bold md:text-3xl">Satu aplikasi, empat tipe slot</h2>
            <p class="mt-2 max-w-xl text-sm text-slate-600 md:text-base">Pilih slot yang sesuai kebutuhanmu sebelum sampai di mal.</p>

            <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($fitur as $f)
                    <article class="rounded-3xl border border-line bg-surface p-6">
                        <span class="grid h-12 w-12 place-items-center rounded-2xl bg-primary-soft">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true"><path d="{{ $f['ikon'] }}"/></svg>
                        </span>
                        <h3 class="mt-4 text-base font-semibold">{{ $f['judul'] }}</h3>
                        <p class="mt-1 text-sm text-slate-600">{{ $f['isi'] }}</p>
                    </article>
                @endforeach
            </div>
        </section>

        {{-- ============================== DAFTAR MAL ============================== --}}
        @if ($mal->isNotEmpty())
            <section aria-labelledby="judul-mal" class="mx-auto max-w-6xl px-4 pb-16 md:px-8 md:pb-24">
                <div class="flex items-end justify-between gap-4">
                    <div>
                        <h2 id="judul-mal" class="text-2xl font-bold md:text-3xl">Sudah tersedia di</h2>
                        <p class="mt-2 text-sm text-slate-600 md:text-base">Beberapa mal yang sudah bisa dipesan lewat Markir.In.</p>
                    </div>
                </div>

                <ul class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($mal as $m)
                        <li class="overflow-hidden rounded-3xl border border-line bg-surface">
                            <img src="{{ $m->foto_url }}" alt="{{ $m->nama }}" loading="lazy" class="h-44 w-full object-cover">
                            <div class="p-4">
                                <p class="font-semibold">{{ $m->nama }}</p>
                                <p class="text-sm text-slate-500">{{ $m->area ? $m->area . ', ' : '' }}{{ $m->kota }}</p>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif

        {{-- ============================== AJAKAN ============================== --}}
        <section class="mx-auto max-w-6xl px-4 pb-16 md:px-8 md:pb-24">
            <div class="flex flex-col items-start justify-between gap-6 rounded-3xl bg-primary p-8 md:flex-row md:items-center md:p-12">
                <div>
                    <h2 class="text-2xl font-bold text-on-primary md:text-3xl">Pusing cari parkir di mall?</h2>
                    <p class="mt-2 text-sm text-on-primary/85 md:text-base">Daftar gratis, langsung bisa pesan slot pertamamu.</p>
                </div>
                <a href="{{ route('register') }}"
                   class="shrink-0 rounded-2xl bg-on-primary px-7 py-3.5 text-sm font-bold text-primary hover:bg-surface-alt focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-on-primary">
                    Daftar Gratis
                </a>
            </div>
        </section>
    </main>
</body>
</html>
