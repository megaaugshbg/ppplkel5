<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Pilih Slot Parkir</title>
<style>
    :root {
        --primary: #1b1870;
        --primary-soft: #e7e5f4;
        --surface: #ffffff;
        --surface-alt: #eef0f4;
        --line: #e6e1bd;
        --header: #f5efd6;
        --bg: #fefce9;
        --muted: #6b7280;
        --slot-line: #cbd5e1;
        --focus: #3b82f6;
        --ev: #10b981;
    }
    * { box-sizing: border-box; }
    body {
        margin: 0;
        font-family: "Segoe UI", system-ui, -apple-system, sans-serif;
        background: var(--bg);
        color: #1f2937;
    }

    /* ---------- Header ---------- */
    .header {
        background: var(--header);
        border-bottom: 1px solid var(--line);
        padding: 22px 6vw 28px;
    }
    .kembali { color: var(--primary); text-decoration: none; font-size: 15px; font-weight: 600; }
    .kembali:hover { text-decoration: underline; }
    .header h1 { margin: 14px 0 6px; font-size: 34px; font-weight: 700; color: var(--primary); }
    .header p { margin: 0; font-size: 17px; color: #4a4a8a; }

    /* Pesan dari server */
    .header .pesan { margin: 14px 0 0; padding: 12px 16px; border-radius: 10px; font-size: 15px; font-weight: 600; }
    .pesan-error { background: #fef3c7; color: #92400e; }
    .pesan-info { background: var(--primary-soft); color: var(--primary); }

    /* ---------- Layout ---------- */
    .wrap {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 380px;
        gap: 28px;
        padding: 36px 6vw 48px;
        align-items: start;
    }
    .kartu {
        background: var(--surface);
        border: 1px solid var(--line);
        border-radius: 18px;
        box-shadow: 0 4px 16px rgba(27, 24, 112, .05);
        padding: 28px;
    }

    /* ---------- Navigasi lantai ---------- */
    .lantai-nav { display: flex; align-items: center; justify-content: center; gap: 24px; }
    .lantai-nav button {
        background: var(--primary); color: #fff; border: 0;
        width: 42px; height: 42px; border-radius: 50%;
        font-size: 22px; line-height: 1; cursor: pointer;
    }
    .lantai-nav button:disabled { background: #cfd5e3; cursor: default; }
    .lantai-nav strong { min-width: 160px; text-align: center; font-size: 30px; color: var(--primary); }
    .lantai-lokasi { text-align: center; font-size: 14px; color: var(--muted); margin: 4px 0 14px; }
    .legend {
        display: flex; justify-content: center; flex-wrap: wrap;
        gap: 10px 22px; font-size: 14px; color: #4b5563; margin-bottom: 22px;
    }
    .legend span { display: inline-flex; align-items: center; gap: 6px; }
    .legend i { display: inline-block; width: 13px; height: 13px; border-radius: 3px; }

    /* ---------- Denah ---------- */
    .denah {
        border: 1px solid var(--line); border-radius: 16px;
        padding: 26px 22px 18px; background: var(--surface);
    }
    .panel { display: none; }
    .panel.aktif { display: grid; grid-template-columns: 1fr 90px 1fr; }
    .kolom { display: flex; flex-direction: column; border-top: 2px solid var(--slot-line); }
    .kolom.kiri { border-left: 2px solid var(--slot-line); }
    .kolom.kanan { border-right: 2px solid var(--slot-line); }
    .lorong { position: relative; }
    .lorong::before {
        content: ""; position: absolute; top: 0; bottom: 0; left: 50%;
        border-left: 2px dashed #e2e8f0;
    }

    .bay { position: relative; display: block; }
    .bay input { position: absolute; opacity: 0; pointer-events: none; }
    .bay-box {
        position: relative; display: flex; flex-direction: column;
        align-items: center; justify-content: center; gap: 4px;
        height: 104px; border-bottom: 2px solid var(--slot-line);
        color: var(--primary); cursor: pointer; transition: background .15s;
        overflow: hidden;
    }
    .bay:not(.terisi):hover .bay-box { background: var(--primary-soft); }
    .bay input:focus-visible + .bay-box { outline: 2px solid var(--focus); outline-offset: -3px; }
    .bay input:checked + .bay-box { background: var(--primary-soft); }
    .bay.terisi .bay-box { cursor: not-allowed; }

    .bay-kode {
        position: absolute; bottom: 8px;
        font-size: 14px; font-weight: 700; color: var(--primary);
    }
    .kiri .bay-kode { left: 10px; }
    .kanan .bay-kode { right: 10px; }
    .bay.terisi .bay-kode { color: #94a3b8; }

    /* Mobil: statis untuk slot terisi, animasi untuk slot yang dipilih */
    .mobil { width: 104px; height: 49px; color: #475569; flex-shrink: 0; }
    .kanan .mobil { transform: scaleX(-1); }
    .mobil-pilih { display: none; color: var(--primary); }
    .bay input:checked + .bay-box .mobil-pilih {
        display: block;
        animation: mobilMasuk .45s cubic-bezier(.2, .8, .2, 1) both;
    }
    @keyframes mobilMasuk {
        0%   { translate: 0 -24px; opacity: 0; }
        60%  { translate: 0 3px;   opacity: 1; }
        100% { translate: 0 0;     opacity: 1; }
    }

    .tp { display: none; font-size: 13px; font-weight: 700; color: var(--primary); }
    .bay input:checked + .bay-box .tp {
        display: block;
        animation: teksMuncul .35s ease-out .15s both;
    }
    @keyframes teksMuncul {
        from { opacity: 0; transform: translateY(4px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    /* ---------- EV: chip konektor ---------- */
    .ev-info {
        position: absolute; top: 10px;
        display: inline-flex; align-items: center; gap: 4px;
        font-size: 12px; font-weight: 600; color: var(--primary); white-space: nowrap;
    }
    .kiri .ev-info { left: 10px; }
    .kanan .ev-info { right: 10px; }
    .ev-info svg { width: 13px; height: 13px; color: var(--ev); }
    .bay.terisi .ev-info, .bay.terisi .ev-info svg { color: #94a3b8; }

    .pintu { text-align: center; font-size: 14px; color: var(--muted); margin: 18px 0 0; line-height: 1.5; }

    /* ---------- Ringkasan ---------- */
    .panel-samping { position: sticky; top: 24px; }
    .pill {
        display: inline-block; background: var(--primary); color: #fff;
        padding: 10px 20px; border-radius: 999px; font-size: 15px; font-weight: 600;
    }
    .baris {
        display: flex; justify-content: space-between; gap: 12px;
        padding: 12px 0; border-bottom: 1px solid var(--line); font-size: 15px;
    }
    .baris:first-of-type { margin-top: 18px; }
    .baris span:first-child { color: var(--muted); }
    .baris span:last-child { color: var(--primary); font-weight: 600; text-align: right; }
    .baris.sembunyi { display: none; }

    .total { display: flex; justify-content: space-between; align-items: center; margin: 22px 0 16px; }
    .total span { font-size: 18px; color: var(--muted); }
    .total strong { color: var(--primary); font-size: 24px; }

    .simulasi { font-size: 13px; color: var(--muted); margin: 0 0 16px; }
    .simulasi label { display: flex; align-items: center; gap: 8px; cursor: pointer; }

    .btn {
        display: block; width: 100%; padding: 16px; border-radius: 12px;
        font-size: 18px; font-weight: 600; text-align: center; text-decoration: none;
        border: 2px solid var(--primary); cursor: pointer; font-family: inherit;
        background: var(--primary); color: #fff; margin-bottom: 12px;
        transition: transform .1s, opacity .15s;
    }
    .btn:not(:disabled):active { transform: scale(.98); }
    .btn.sec { background: #fff; color: var(--primary); }
    .btn:disabled { background: var(--surface-alt); color: var(--primary); opacity: .6; cursor: not-allowed; }

    /* ---------- Responsif ---------- */
    @media (max-width: 960px) {
        .wrap { grid-template-columns: 1fr; padding: 24px 16px 40px; }
        .panel-samping { position: static; }
        .header { padding: 20px 16px 24px; }
        .header h1 { font-size: 28px; }
    }
    @media (max-width: 640px) {
        .kartu { padding: 18px; }
        .denah { padding: 14px 10px 10px; }
        .panel.aktif { grid-template-columns: 1fr 40px 1fr; }
        .bay-box { height: 84px; }
        .mobil { width: 70px; height: 33px; }
        .bay-kode { font-size: 12px; }
        .ev-info { font-size: 10px; }
        .lantai-nav strong { font-size: 24px; min-width: 130px; }
    }
    @media (prefers-reduced-motion: reduce) {
        .bay input:checked + .bay-box .mobil-pilih,
        .bay input:checked + .bay-box .tp { animation: none; }
    }
</style>
</head>
<body>

<header class="header">
    <a href="#" class="kembali" id="linkKembali">&larr; Kembali</a>
    <h1>Pilih Slot Parkir</h1>
    <p id="deskripsi"></p>
    @if (session('error'))
        <p class="pesan pesan-error" role="alert">{{ session('error') }}</p>
    @endif
    @if ($bannerTutup)
        <p class="pesan pesan-info" role="status">{{ $bannerTutup }}</p>
    @endif
    @unless ($bisaPesan)
        <p class="pesan pesan-info" role="status">Pemesanan tipe ini segera hadir. Denah hanya untuk dilihat.</p>
    @endunless
</header>

<main class="wrap">
    <form id="formPesan" method="POST" action="{{ route('pesan-slot') }}" style="display:contents">
        @csrf
        <input type="hidden" name="lokasi" value="{{ $lokasi->id }}">
        <input type="hidden" name="tipe" value="{{ $tipe }}">
        <section class="kartu">
            <div class="lantai-nav">
                <button type="button" id="prev" aria-label="Lantai sebelumnya">&lsaquo;</button>
                <strong id="labelLantai">Lantai 1</strong>
                <button type="button" id="next" aria-label="Lantai berikutnya">&rsaquo;</button>
            </div>
            <p class="lantai-lokasi" id="namaLokasi"></p>

            <div class="legend">
                <span><i style="background:#475569"></i>Terisi</span>
                <span><i style="background:#fff;border:1.5px solid var(--primary)"></i>Tersedia (<span id="jumlahTersedia">0</span>)</span>
                <span><i style="background:var(--primary-soft);border:1.5px solid var(--primary)"></i>Dipilih</span>
            </div>

            <div class="denah" id="denah"></div>
        </section>

        <aside class="panel-samping kartu">
            <span class="pill">Ringkasan Pesanan</span>
            <div class="baris"><span>Tempat</span><span id="ringkasTempat"></span></div>
            <div class="baris"><span>Tipe parkir</span><span id="ringkasTipe"></span></div>
            <div class="baris"><span>Slot tersedia</span><span id="ringkasTersedia">0</span></div>
            <div class="baris sembunyi" id="barisSlot"><span>Slot dipilih</span><span id="isiSlot">-</span></div>
            <div class="baris sembunyi" id="barisCharger"><span>Charger</span><span id="isiCharger">-</span></div>
            <div class="baris sembunyi" id="barisTarifEv"><span>Tarif charging</span><span id="isiTarifEv"></span></div>

            <div class="total">
                <span>Tarif parkir</span>
                <strong id="tarif"></strong>
            </div>

            <p class="simulasi"><label><input type="checkbox" id="gagal" name="gagal" value="1"> Simulasikan gagal menyimpan pesanan</label></p>
            <button type="submit" class="btn" id="btnPesan" disabled>Pesan Slot</button>
            <a href="#" class="btn sec" id="linkBatal">Batal</a>
        </aside>
    </form>
</main>

<script>
    // ==========================================================
    // DATA DARI SERVER (SlotController@index)
    // ==========================================================
    const urlKembali = @json(route('lokasi.tipe', $lokasi->id));
    const bisaPesan = @json($bisaPesan && ! $tutup);

    const lokasi = {
        id: @json($lokasi->id),
        nama: @json($lokasi->nama),
        alamat: @json($lokasi->alamat),
        tarif: @json($lokasi->tarif),
    };

    // Konfigurasi tipe yang sedang dilihat
    const cfg = {
        label: @json($labelTipe),
        ev: @json($adalahEv),
        tarifEv: @json($tarifEv),
    };

    // Slot per lantai dari tabel slot_parkir: [{ nomor, slot: [{ kode, lantai, konektor, daya, terisi }] }]
    const lantaiData = @json($perLantai->map(fn ($daftar, $nomor) => ['nomor' => $nomor, 'slot' => $daftar->values()])->values());

    // ---------- Ikon ----------
    const ikonMobil = `
        <svg class="mobil" aria-hidden="true" viewBox="0 0 120 56">
            <rect x="26" y="1" width="20" height="7" rx="3.5" fill="#0f172a"/>
            <rect x="26" y="48" width="20" height="7" rx="3.5" fill="#0f172a"/>
            <rect x="82" y="1" width="20" height="7" rx="3.5" fill="#0f172a"/>
            <rect x="82" y="48" width="20" height="7" rx="3.5" fill="#0f172a"/>
            <rect x="4" y="6" width="112" height="44" rx="16" fill="currentColor"/>
            <path d="M80 12 L99 16 Q104 28 99 40 L80 44 Z" fill="#fff" opacity=".85"/>
            <path d="M32 14 L22 17 Q18 28 22 39 L32 42 Z" fill="#fff" opacity=".55"/>
            <rect x="34" y="13" width="44" height="30" rx="7" fill="#fff" opacity=".22"/>
        </svg>`;
    const ikonMobilPilih = ikonMobil.replace('class="mobil"', 'class="mobil mobil-pilih"');
    const ikonPetir = `<svg aria-hidden="true" viewBox="0 0 24 24"><path d="M13 2 4 14h6l-1 8 9-12h-6l1-8Z" fill="currentColor"/></svg>`;

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
    const barisTarifEv = document.getElementById('barisTarifEv');
    let indeks = 0;

    // ---------- Render utama (dipanggil ulang saat tipe berubah) ----------
    function render() {
        const semuaSlot = lantaiData.flatMap(l => l.slot);
        const tersedia = semuaSlot.filter(s => !s.terisi).length;

        // Teks statis
        document.getElementById('linkKembali').href = urlKembali;
        document.getElementById('linkBatal').href = urlKembali;
        document.getElementById('deskripsi').textContent = lokasi.nama + ' · ' + lokasi.alamat;
        document.getElementById('namaLokasi').textContent = lokasi.nama;
        document.getElementById('ringkasTempat').textContent = lokasi.nama;
        document.getElementById('ringkasTipe').textContent = cfg.label;
        document.getElementById('ringkasTersedia').textContent = tersedia;
        document.getElementById('jumlahTersedia').textContent = tersedia;
        document.getElementById('tarif').textContent = 'Rp ' + lokasi.tarif.toLocaleString('id-ID') + ' / jam';

        if (cfg.ev) {
            barisTarifEv.classList.remove('sembunyi');
            document.getElementById('isiTarifEv').textContent = 'Rp ' + cfg.tarifEv.toLocaleString('id-ID') + ' / kWh';
        } else {
            barisTarifEv.classList.add('sembunyi');
        }

        // Reset pilihan
        barisSlot.classList.add('sembunyi');
        barisCharger.classList.add('sembunyi');
        btnPesan.disabled = true;

        // Tidak ada slot sama sekali untuk tipe ini di lokasi ini
        if (!semuaSlot.length) {
            denah.innerHTML = '<p class="pintu">Tidak ada slot parkir yang tersedia.</p>';
            prev.disabled = true;
            next.disabled = true;
            return;
        }

        // Denah
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
                ? `${ikonMobil}<span style="position:absolute;left:-9999px">Terisi</span>`
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
    function panelGrid() { return document.querySelectorAll('.panel'); }

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
                barisSlot.classList.remove('sembunyi');

                if (cfg.ev) {
                    isiCharger.textContent = `${r.dataset.konektor} · ${r.dataset.daya} kW`;
                    barisCharger.classList.remove('sembunyi');
                }
                btnPesan.disabled = !bisaPesan;
            });
        });
    }

    // Tombol "Pesan Slot" mengirim form ke POST /pesan-slot (SlotController@pesan).
    // Bila berhasil, server mengarahkan ke halaman Pembayaran.
    render();
</script>
</body>
</html>
