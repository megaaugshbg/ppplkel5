@extends('layouts.app')

@section('title', 'Pilih Slot - ' . $lokasi->nama . ' - Markir.In')

@php
    // Keterangan di bawah tombol Pesan. Urutan prioritas: lokasi tutup, tipe belum dibuka, syarat profil.
    $keteranganTombol = match (true) {
        $tutup                                    => 'Lokasi sedang tutup',
        ! $bisaPesan                              => 'Segera hadir',
        $syarat !== null && ! $syarat['terpenuhi'] => $syarat['tombol'],
        default                                   => null,
    };
    $bolehPesan = $keteranganTombol === null;

    $banner = 'flex items-start gap-3 rounded-2xl border px-4 py-3 text-sm font-medium';
    $field = 'w-full rounded-lg border px-4 py-2.5 text-sm outline-none transition focus:border-focus focus:ring-2 focus:ring-focus/20';
    $tombolKecil = 'shrink-0 rounded-lg bg-primary px-4 py-2.5 text-sm font-semibold text-on-primary transition hover:bg-primary-hover focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus';
    $chip = 'inline-flex shrink-0 items-center gap-1.5 rounded-full border px-3.5 py-1.5 text-sm font-medium transition focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus';
@endphp

@push('styles')
<style>
    /* Hanya untuk denah; warna dari resources/css/tokens.css. */
    .denah-parkir {
        --garis-slot: color-mix(in srgb, var(--color-primary) 22%, transparent);
    }

    /* ---------- Navigasi lantai ---------- */
    .denah-parkir .lantai-nav { display: flex; align-items: center; justify-content: center; gap: 24px; }
    .denah-parkir .lantai-nav button {
        display: grid; place-items: center;
        background: var(--color-primary); color: var(--color-on-primary);
        width: 42px; height: 42px; border-radius: 50%;
        font-size: 22px; line-height: 1; cursor: pointer;
    }
    .denah-parkir .lantai-nav button:hover:not(:disabled) { background: var(--color-primary-hover); }
    .denah-parkir .lantai-nav button:focus-visible { outline: 2px solid var(--color-focus); outline-offset: 2px; }
    .denah-parkir .lantai-nav button:disabled { background: var(--color-surface-alt); color: var(--color-primary); opacity: .5; cursor: default; }
    .denah-parkir .lantai-nav strong { min-width: 160px; text-align: center; font-size: 28px; color: var(--color-primary); }

    /* ---------- Denah ---------- */
    .denah-parkir .denah {
        border: 1px solid var(--color-line); border-radius: 16px;
        padding: 26px 22px 18px; background: var(--color-surface);
    }
    .denah-parkir .panel { display: none; }
    .denah-parkir .panel.aktif { display: grid; grid-template-columns: 1fr 90px 1fr; }
    .denah-parkir .kolom { display: flex; flex-direction: column; border-top: 2px solid var(--garis-slot); }
    .denah-parkir .kolom.kiri { border-left: 2px solid var(--garis-slot); }
    .denah-parkir .kolom.kanan { border-right: 2px solid var(--garis-slot); }
    .denah-parkir .lorong { position: relative; }
    .denah-parkir .lorong::before {
        content: ""; position: absolute; top: 0; bottom: 0; left: 50%;
        border-left: 2px dashed var(--color-line);
    }

    .denah-parkir .bay { position: relative; display: block; }
    .denah-parkir .bay input { position: absolute; opacity: 0; pointer-events: none; }
    .denah-parkir .bay-box {
        position: relative; display: flex; flex-direction: column;
        align-items: center; justify-content: center; gap: 4px;
        height: 104px; border-bottom: 2px solid var(--garis-slot);
        color: var(--color-primary); cursor: pointer; transition: background .15s;
        overflow: hidden;
    }
    .denah-parkir .bay:not(.terisi):hover .bay-box { background: var(--color-primary-soft); }
    .denah-parkir .bay input:focus-visible + .bay-box { outline: 2px solid var(--color-focus); outline-offset: -3px; }
    .denah-parkir .bay input:checked + .bay-box { background: var(--color-primary-soft); }
    .denah-parkir .bay.terisi .bay-box { cursor: not-allowed; }

    .denah-parkir .bay-kode { position: absolute; bottom: 8px; font-size: 14px; font-weight: 700; color: var(--color-primary); }
    .denah-parkir .kiri .bay-kode { left: 10px; }
    .denah-parkir .kanan .bay-kode { right: 10px; }
    .denah-parkir .bay.terisi .bay-kode { opacity: .45; }

    /* Mobil: statis untuk slot terisi, animasi untuk slot yang dipilih */
    .denah-parkir .mobil { width: 104px; height: 49px; flex-shrink: 0; }
    .denah-parkir .mobil .kaca { fill: var(--color-surface); }
    .denah-parkir .kanan .mobil { transform: scaleX(-1); }
    .denah-parkir .mobil-pilih { display: none; color: var(--color-primary); }
    .denah-parkir .bay input:checked + .bay-box .mobil-pilih {
        display: block;
        animation: mobilMasuk .45s cubic-bezier(.2, .8, .2, 1) both;
    }
    @keyframes mobilMasuk {
        0%   { translate: 0 -24px; opacity: 0; }
        60%  { translate: 0 3px;   opacity: 1; }
        100% { translate: 0 0;     opacity: 1; }
    }

    .denah-parkir .tp { display: none; font-size: 13px; font-weight: 700; color: var(--color-primary); }
    .denah-parkir .bay input:checked + .bay-box .tp { display: block; animation: teksMuncul .35s ease-out .15s both; }
    @keyframes teksMuncul {
        from { opacity: 0; transform: translateY(4px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    /* ---------- EV: label konektor & daya di kotak slot ---------- */
    .denah-parkir .ev-info {
        position: absolute; top: 10px;
        display: inline-flex; align-items: center; gap: 4px;
        font-size: 12px; font-weight: 600; color: var(--color-primary); white-space: nowrap;
    }
    .denah-parkir .kiri .ev-info { left: 10px; }
    .denah-parkir .kanan .ev-info { right: 10px; }
    .denah-parkir .ev-info svg { width: 13px; height: 13px; }
    .denah-parkir .bay.terisi .ev-info { opacity: .45; }

    .denah-parkir .pintu { text-align: center; font-size: 14px; color: var(--color-primary); opacity: .7; margin: 18px 0 0; line-height: 1.5; }

    @media (max-width: 640px) {
        .denah-parkir .denah { padding: 14px 10px 10px; }
        .denah-parkir .panel.aktif { grid-template-columns: 1fr 40px 1fr; }
        .denah-parkir .bay-box { height: 84px; }
        .denah-parkir .mobil { width: 70px; height: 33px; }
        .denah-parkir .bay-kode { font-size: 12px; }
        .denah-parkir .ev-info { font-size: 10px; }
        .denah-parkir .lantai-nav strong { font-size: 22px; min-width: 130px; }
    }
    @media (prefers-reduced-motion: reduce) {
        .denah-parkir .bay input:checked + .bay-box .mobil-pilih,
        .denah-parkir .bay input:checked + .bay-box .tp { animation: none; }
    }
</style>
@endpush

@section('content')
    <div class="denah-parkir mx-auto max-w-6xl space-y-5 px-4 pt-5 md:px-6 md:pt-8">

        {{-- Judul --}}
        <div>
            <a href="{{ route('lokasi.tipe', $lokasi->id) }}" class="inline-flex items-center gap-1 text-sm font-semibold hover:opacity-80 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
                Kembali
            </a>
            <h1 class="mt-3 text-2xl font-bold md:text-3xl">Pilih Slot Parkir</h1>
            <p class="mt-1 text-sm text-slate-600">{{ $lokasi->nama }} &middot; {{ $lokasi->alamat }}</p>
        </div>

        {{-- Pesan dari server --}}
        @if (session('error'))
            <div role="alert" class="{{ $banner }} border-red-200 bg-red-50 text-red-800">{{ session('error') }}</div>
        @endif
        @if (session('sukses'))
            <div role="status" class="{{ $banner }} border-line bg-primary-soft font-semibold text-primary">{{ session('sukses') }}</div>
        @endif
        @if ($bannerTutup)
            <div role="status" class="{{ $banner }} border-line bg-primary-soft text-primary">
                <svg class="mt-0.5 h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
                <p>{{ $bannerTutup }}</p>
            </div>
        @endif
        @unless ($bisaPesan)
            <div role="status" class="{{ $banner }} border-line bg-primary-soft text-primary">Pemesanan tipe ini segera hadir. Denah hanya untuk dilihat.</div>
        @endunless

        {{-- Status syarat profil (Woman / Priority) --}}
        @if ($syarat)
            @if ($syarat['terpenuhi'])
                <div role="status" class="{{ $banner }} border-line bg-primary-soft font-semibold text-primary">{{ $syarat['banner'] }}</div>
            @else
                <div role="status" class="{{ $banner }} flex-col border-line bg-surface-alt text-primary sm:flex-row sm:items-center">
                    <div class="flex flex-1 items-start gap-3">
                        <svg class="mt-0.5 h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 8v4M12 16h.01"/></svg>
                        <p class="font-semibold">{{ $syarat['banner'] }}</p>
                    </div>

                    @if ($syarat['aksi'] === 'profil')
                        <a href="{{ route('profil') }}" class="{{ $tombolKecil }} text-center">Buka profil</a>
                    @elseif ($syarat['aksi'] === 'kartu')
                        {{-- Simpan kartu langsung dari sini; setelah itu kembali ke denah yang sama. --}}
                        <form method="POST" action="{{ route('profil.kartu') }}" class="w-full sm:w-auto">
                            @csrf
                            <input type="hidden" name="lokasi" value="{{ $lokasi->id }}">
                            <input type="hidden" name="tipe" value="{{ $tipe }}">
                            <label for="nomor_kartu_disabilitas" class="sr-only">Nomor kartu disabilitas</label>
                            <div class="flex flex-col gap-2 sm:flex-row">
                                <input id="nomor_kartu_disabilitas" name="nomor_kartu_disabilitas" maxlength="20" autocomplete="off"
                                       value="{{ old('nomor_kartu_disabilitas') }}" placeholder="Nomor kartu disabilitas"
                                       @error('nomor_kartu_disabilitas') aria-invalid="true" aria-describedby="err-kartu" @enderror
                                       class="{{ $field }} bg-surface uppercase sm:w-56 {{ $errors->has('nomor_kartu_disabilitas') ? 'border-red-400' : 'border-line' }}">
                                <button type="submit" class="{{ $tombolKecil }}">Simpan kartu</button>
                            </div>
                            @error('nomor_kartu_disabilitas') <p id="err-kartu" class="mt-1 text-xs font-normal text-red-700">{{ $message }}</p> @enderror
                        </form>
                    @endif
                </div>
            @endif
        @endif

        {{-- EV: filter konektor (hanya konektor yang ada di lokasi ini) --}}
        @if ($adalahEv && count($daftarKonektor))
            <nav aria-label="Filter konektor" class="-mx-4 flex gap-2 overflow-x-auto px-4 pb-1 [scrollbar-width:none] md:mx-0 md:flex-wrap md:px-0">
                @foreach (array_merge([null], $daftarKonektor) as $k)
                    @php $aktif = $konektor === $k; @endphp
                    <a href="{{ route('slot-parkir', array_filter(['lokasi' => $lokasi->id, 'tipe' => $tipe, 'konektor' => $k])) }}"
                       @if ($aktif) aria-current="true" @endif
                       class="{{ $chip }} {{ $aktif ? 'border-primary bg-primary text-on-primary' : 'border-line bg-surface text-primary hover:bg-surface-alt' }}">
                        @if ($k)
                            <svg class="h-3.5 w-3.5 {{ $aktif ? '' : 'text-emerald-600' }}" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M13 2 4 14h6l-1 8 9-12h-6l1-8Z"/></svg>
                        @endif
                        {{ $k ?? 'Semua' }}
                    </a>
                @endforeach
            </nav>
        @endif

        <form id="formPesan" method="POST" action="{{ route('pesan-slot') }}"
              class="grid items-start gap-6 lg:grid-cols-[minmax(0,1fr)_380px]">
            @csrf
            <input type="hidden" name="lokasi" value="{{ $lokasi->id }}">
            <input type="hidden" name="tipe" value="{{ $tipe }}">

            {{-- Denah --}}
            <section aria-label="Denah slot" class="rounded-3xl border border-line bg-surface p-4 shadow-sm sm:p-7">
                <div class="lantai-nav">
                    <button type="button" id="prev" aria-label="Lantai sebelumnya">&lsaquo;</button>
                    <strong id="labelLantai" aria-live="polite">Lantai 1</strong>
                    <button type="button" id="next" aria-label="Lantai berikutnya">&rsaquo;</button>
                </div>
                <p class="mb-3.5 mt-1 text-center text-sm text-slate-500">{{ $lokasi->nama }}</p>

                <div class="mb-5 flex flex-wrap justify-center gap-x-5 gap-y-2 text-sm text-slate-600">
                    <span class="inline-flex items-center gap-1.5"><i class="inline-block h-3.5 w-3.5 rounded-sm bg-slate-500"></i>Terisi</span>
                    <span class="inline-flex items-center gap-1.5"><i class="inline-block h-3.5 w-3.5 rounded-sm border-[1.5px] border-primary bg-surface"></i>Tersedia (<span id="jumlahTersedia">{{ $tersedia }}</span>)</span>
                    <span class="inline-flex items-center gap-1.5"><i class="inline-block h-3.5 w-3.5 rounded-sm border-[1.5px] border-primary bg-primary-soft"></i>Dipilih</span>
                </div>

                <div class="denah" id="denah"></div>
            </section>

            {{-- Ringkasan --}}
            <aside class="rounded-3xl border border-line bg-surface p-5 shadow-sm sm:p-7 lg:sticky lg:top-24">
                <span class="inline-block rounded-full bg-primary px-5 py-2 text-sm font-semibold text-on-primary">Ringkasan Pesanan</span>

                <dl class="mt-4 divide-y divide-line text-sm">
                    <div class="flex justify-between gap-3 py-3"><dt class="text-slate-500">Tempat</dt><dd class="text-right font-semibold">{{ $lokasi->nama }}</dd></div>
                    <div class="flex justify-between gap-3 py-3"><dt class="text-slate-500">Tipe parkir</dt><dd class="text-right font-semibold">{{ $labelTipe }}</dd></div>
                    <div class="flex justify-between gap-3 py-3"><dt class="text-slate-500">Slot tersedia</dt><dd class="text-right font-semibold" id="ringkasTersedia">{{ $tersedia }}</dd></div>
                    <div class="hidden justify-between gap-3 py-3" id="barisSlot"><dt class="text-slate-500">Slot dipilih</dt><dd class="text-right font-semibold" id="isiSlot">-</dd></div>
                    <div class="hidden justify-between gap-3 py-3" id="barisCharger"><dt class="text-slate-500">Charger</dt><dd class="text-right font-semibold" id="isiCharger">-</dd></div>
                    @if ($adalahEv)
                        <div class="flex justify-between gap-3 py-3"><dt class="text-slate-500">Tarif charging</dt><dd class="text-right font-semibold">{{ $tarifEv ? 'Rp ' . number_format($tarifEv, 0, ',', '.') . ' / kWh' : '-' }}</dd></div>
                    @endif
                </dl>

                <div class="my-5 flex items-center justify-between gap-3">
                    <span class="text-slate-500">Tarif parkir</span>
                    <strong class="text-xl">Rp {{ number_format($lokasi->tarif, 0, ',', '.') }} / jam</strong>
                </div>

                <p class="mb-4 text-xs text-slate-500">
                    <label class="flex cursor-pointer items-center gap-2"><input type="checkbox" id="gagal" name="gagal" value="1" class="accent-[var(--color-primary)]"> Simulasikan gagal menyimpan pesanan</label>
                </p>

                <button type="submit" id="btnPesan" disabled @if ($keteranganTombol) aria-describedby="ket-pesan" @endif
                        class="w-full rounded-xl bg-primary px-4 py-3.5 text-base font-semibold text-on-primary transition hover:bg-primary-hover focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus active:scale-[.98] disabled:cursor-not-allowed disabled:bg-surface-alt disabled:text-primary disabled:opacity-60 disabled:active:scale-100">
                    Pesan Slot
                </button>
                @if ($keteranganTombol)
                    <p id="ket-pesan" class="mt-2 text-center text-sm font-semibold text-primary">{{ $keteranganTombol }}</p>
                @endif
                <a href="{{ route('lokasi.tipe', $lokasi->id) }}"
                   class="mt-3 block w-full rounded-xl border-2 border-primary bg-surface px-4 py-3 text-center text-base font-semibold text-primary transition hover:bg-primary-soft focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus">
                    Batal
                </a>
            </aside>
        </form>
    </div>
@endsection

@push('scripts')
<script>
    // ==========================================================
    // DATA DARI SERVER (SlotController@index)
    // ==========================================================
    // Boleh pesan = lokasi buka, tipe sudah dibuka, dan syarat profil terpenuhi.
    // (Server tetap mengecek ulang di SlotController@pesan.)
    const bisaPesan = @json($bolehPesan);
    const cfg = { ev: @json($adalahEv) };

    // Slot per lantai dari tabel slot_parkir: [{ nomor, slot: [{ kode, lantai, konektor, daya, terisi }] }]
    const lantaiData = @json($perLantai->map(fn ($daftar, $nomor) => ['nomor' => $nomor, 'slot' => $daftar->values()])->values());

    // ---------- Ikon ----------
    const ikonMobil = `
        <svg class="mobil text-slate-500" aria-hidden="true" viewBox="0 0 120 56">
            <rect x="26" y="1" width="20" height="7" rx="3.5" fill="currentColor" opacity=".9"/>
            <rect x="26" y="48" width="20" height="7" rx="3.5" fill="currentColor" opacity=".9"/>
            <rect x="82" y="1" width="20" height="7" rx="3.5" fill="currentColor" opacity=".9"/>
            <rect x="82" y="48" width="20" height="7" rx="3.5" fill="currentColor" opacity=".9"/>
            <rect x="4" y="6" width="112" height="44" rx="16" fill="currentColor"/>
            <path class="kaca" d="M80 12 L99 16 Q104 28 99 40 L80 44 Z" opacity=".85"/>
            <path class="kaca" d="M32 14 L22 17 Q18 28 22 39 L32 42 Z" opacity=".55"/>
            <rect class="kaca" x="34" y="13" width="44" height="30" rx="7" opacity=".22"/>
        </svg>`;
    const ikonMobilPilih = ikonMobil.replace('class="mobil text-slate-500"', 'class="mobil mobil-pilih"');
    const ikonPetir = `<svg class="text-emerald-600" aria-hidden="true" viewBox="0 0 24 24"><path d="M13 2 4 14h6l-1 8 9-12h-6l1-8Z" fill="currentColor"/></svg>`;

    // ---------- Elemen ----------
    const denah = document.getElementById('denah');
    const label = document.getElementById('labelLantai');
    const prev = document.getElementById('prev');
    const next = document.getElementById('next');
    const btnPesan = document.getElementById('btnPesan');
    const barisSlot = document.getElementById('barisSlot');
    const isiSlot = document.getElementById('isiSlot');
    const barisCharger = document.getElementById('barisCharger');
    const isiCharger = document.getElementById('isiCharger');
    let indeks = 0;

    function tampilkan(el, ya) { el.classList.toggle('hidden', !ya); el.classList.toggle('flex', ya); }

    // ---------- Render denah ----------
    function render() {
        const semuaSlot = lantaiData.flatMap(l => l.slot);

        tampilkan(barisSlot, false);
        tampilkan(barisCharger, false);
        btnPesan.disabled = true;

        // Tidak ada slot untuk tipe (dan konektor) ini di lokasi ini
        if (!semuaSlot.length) {
            denah.innerHTML = '<p class="pintu">Tidak ada slot parkir yang tersedia.</p>';
            prev.disabled = true;
            next.disabled = true;
            return;
        }

        denah.innerHTML = lantaiData.map((lantai, idx) => {
            const half = Math.ceil(lantai.slot.length / 2);
            return `
                <div class="panel ${idx === 0 ? 'aktif' : ''}" data-lantai="${lantai.nomor}">
                    <div class="kolom kiri">${bayHtml(lantai.slot.slice(0, half), lantai.nomor, cfg.ev)}</div>
                    <div class="lorong" aria-hidden="true"></div>
                    <div class="kolom kanan">${bayHtml(lantai.slot.slice(half), lantai.nomor, cfg.ev)}</div>
                </div>`;
        }).join('') + '<p class="pintu">&darr;<br>Pintu Masuk</p>';

        indeks = 0;
        tampilLantai();
        pasangEventSlot();
    }

    function bayHtml(daftar, nomorLantai, ev) {
        return daftar.map(s => {
            const konektor = ev && s.konektor
                ? `<small class="ev-info">${ikonPetir}${s.konektor} &middot; ${s.daya} kW</small>`
                : '';
            const isi = s.terisi
                ? `${ikonMobil}<span class="sr-only">Terisi</span>`
                : `${ikonMobilPilih}<span class="tp">Terpilih</span>`;
            return `
                <label class="bay ${s.terisi ? 'terisi' : ''}">
                    <input type="radio" name="slot" value="${s.kode}" data-lantai="${nomorLantai}"
                           data-konektor="${s.konektor || ''}" data-daya="${s.daya || ''}"
                           ${s.terisi ? 'disabled' : ''}>
                    <span class="bay-box">
                        ${konektor}
                        ${isi}
                        <b class="bay-kode">${s.kode}</b>
                    </span>
                </label>`;
        }).join('');
    }

    // ---------- Navigasi lantai ----------
    function panelGrid() { return document.querySelectorAll('.denah-parkir .panel'); }

    function tampilLantai() {
        const panels = panelGrid();
        if (!panels.length) { return; }
        panels.forEach((p, n) => p.classList.toggle('aktif', n === indeks));
        label.textContent = 'Lantai ' + panels[indeks].dataset.lantai;
        prev.disabled = indeks === 0;
        next.disabled = indeks === panels.length - 1;
    }
    prev.addEventListener('click', () => { if (indeks > 0) { indeks--; tampilLantai(); } });
    next.addEventListener('click', () => { if (indeks < panelGrid().length - 1) { indeks++; tampilLantai(); } });

    // ---------- Pilih slot & ringkasan ----------
    function pasangEventSlot() {
        document.querySelectorAll('input[name="slot"]').forEach(r => {
            r.addEventListener('change', () => {
                isiSlot.textContent = `${r.value} (Lantai ${r.dataset.lantai})`;
                tampilkan(barisSlot, true);

                if (cfg.ev) {
                    isiCharger.textContent = `${r.dataset.konektor} · ${r.dataset.daya} kW`;
                    tampilkan(barisCharger, true);
                }
                btnPesan.disabled = !bisaPesan;
            });
        });
    }

    // Tombol "Pesan Slot" mengirim form ke POST /pesan-slot (SlotController@pesan).
    // Bila berhasil, server mengarahkan ke halaman Pembayaran.
    render();
</script>
@endpush
