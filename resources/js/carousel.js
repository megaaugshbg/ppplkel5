/*
 * Carousel horizontal ([data-carousel]) untuk daftar kartu mal di beranda.
 * - Track ([data-carousel-track]) memakai scroll-snap bawaan browser: swipe/sentuh
 *   dan trackpad bekerja tanpa JS.
 * - Berputar tanpa ujung bila kartu cukup banyak: daftar digandakan menjadi 3 set
 *   (klon | asli | klon). Klon diberi aria-hidden="true" dan tabindex="-1" sehingga
 *   tidak dibaca pembaca layar dan tidak bisa difokus, tetapi tetap bisa diklik.
 *   Posisi dijaga di sekitar set tengah: saat gulir berhenti, posisi dilompatkan
 *   sejauh satu set secara instan (tanpa animasi, snap dimatikan sesaat).
 * - Kartu sedikit (tidak memenuhi layar) atau hanya 1: tanpa klon; tombol panah
 *   dinonaktifkan/disembunyikan di ujung.
 * - Tombol [data-carousel-prev] / [data-carousel-next] menggeser satu kartu + gap.
 * - Mouse bisa menyeret (drag) track; klik setelah menyeret dibatalkan supaya kartu
 *   tidak terbuka tidak sengaja.
 * - Panah kiri/kanan keyboard saat track difokus.
 * - Menghormati prefers-reduced-motion (tanpa animasi gulir).
 */
const kurangiGerak = () => window.matchMedia('(prefers-reduced-motion: reduce)').matches;

// Jeda tanpa event scroll yang dianggap "gulir berhenti" (cadangan bila scrollend tidak ada).
const JEDA_DIAM = 150;

function buatKlon(li) {
    const klon = li.cloneNode(true);
    klon.dataset.klon = '';
    klon.setAttribute('aria-hidden', 'true');
    klon.removeAttribute('id');
    klon.querySelectorAll('[id]').forEach((el) => el.removeAttribute('id'));
    klon.querySelectorAll('a, button, input, select, textarea, [tabindex]')
        .forEach((el) => el.setAttribute('tabindex', '-1'));
    return klon;
}

function pasangSatu(akar) {
    const track = akar.querySelector('[data-carousel-track]');
    const prev = akar.querySelector('[data-carousel-prev]');
    const next = akar.querySelector('[data-carousel-next]');
    if (!track) return;

    const asli = Array.from(track.children);
    if (asli.length === 0) return;

    let putar = false;   // mode berputar aktif?
    let lebarSet = 0;    // lebar satu set kartu (termasuk gap)
    let klon = [];
    let seret = null;    // status drag mouse
    let disentuh = false;
    let timerDiam = 0;

    const gap = () => parseFloat(getComputedStyle(track).columnGap) || 0;

    // Jarak geser = lebar satu kartu + gap.
    const langkah = () => asli[0].getBoundingClientRect().width + gap();

    const ukurLebarSet = () => (putar && klon.length)
        ? klon[asli.length].offsetLeft - asli[0].offsetLeft // awal klon belakang - awal set asli
        : asli.length * langkah();

    // Layak berputar: minimal 2 kartu dan satu set lebih lebar dari layar + satu kartu.
    const layakPutar = () => asli.length >= 2 && asli.length * langkah() >= track.clientWidth + langkah();

    // Pindah posisi seketika (tanpa animasi). Snap dimatikan sesaat supaya browser tidak
    // ikut menggeser ke titik snap lain di tengah lompatan.
    const lompatKe = (x) => {
        track.style.scrollSnapType = 'none';
        track.scrollLeft = x;
        requestAnimationFrame(() => {
            if (!seret?.pindah) track.style.scrollSnapType = '';
        });
    };

    // Jaga posisi di sekitar set tengah: [0.5, 1.5) x lebar set.
    const rapikan = () => {
        if (!putar) return;
        const x = track.scrollLeft;
        if (x < lebarSet * 0.5) lompatKe(x + lebarSet);
        else if (x >= lebarSet * 1.5) lompatKe(x - lebarSet);
    };

    const perbaruiTombol = () => {
        if (putar) {
            if (prev) prev.disabled = false;
            if (next) next.disabled = false;
            return;
        }
        const maks = track.scrollWidth - track.clientWidth - 2;
        if (prev) prev.disabled = track.scrollLeft <= 2;
        if (next) next.disabled = track.scrollLeft >= maks;
    };

    const nyalakanPutar = () => {
        const geserAwal = track.scrollLeft;
        const depan = asli.map(buatKlon);
        const belakang = asli.map(buatKlon);
        track.prepend(...depan);
        track.append(...belakang);
        klon = [...depan, ...belakang];
        putar = true;
        lebarSet = ukurLebarSet();
        lompatKe(lebarSet + geserAwal); // mulai di set tengah
    };

    const matikanPutar = () => {
        // Pertahankan kartu yang sedang terlihat: posisi relatif di dalam satu set.
        const relatif = ((track.scrollLeft - lebarSet) % lebarSet + lebarSet) % lebarSet;
        klon.forEach((k) => k.remove());
        klon = [];
        putar = false;
        lebarSet = ukurLebarSet();
        lompatKe(Math.min(relatif, track.scrollWidth - track.clientWidth));
    };

    const sesuaikanUkuran = () => {
        const layak = layakPutar();
        if (layak && !putar) nyalakanPutar();
        else if (!layak && putar) matikanPutar();
        else if (putar) {
            const lama = lebarSet;
            lebarSet = ukurLebarSet();
            if (lama && lama !== lebarSet) lompatKe(track.scrollLeft * (lebarSet / lama));
            rapikan();
        }
        perbaruiTombol();
    };

    const geser = (arah) => {
        rapikan(); // pastikan selalu ada ruang di kedua arah
        track.scrollBy({ left: arah * langkah(), behavior: kurangiGerak() ? 'auto' : 'smooth' });
    };

    // Gulir berhenti -> rapikan posisi (tidak saat jari masih menempel / sedang menyeret).
    const saatDiam = () => {
        clearTimeout(timerDiam);
        if (!disentuh && !seret) rapikan();
    };

    track.addEventListener('scroll', () => {
        perbaruiTombol();
        if (!putar) return;
        // Darurat: hampir menyentuh ujung asli (swipe sangat cepat) -> rapikan sekarang.
        const maks = track.scrollWidth - track.clientWidth;
        const x = track.scrollLeft;
        if (!seret && (x < langkah() || x > maks - langkah())) rapikan();
        clearTimeout(timerDiam);
        timerDiam = setTimeout(saatDiam, JEDA_DIAM);
    }, { passive: true });
    if ('onscrollend' in window) track.addEventListener('scrollend', saatDiam);

    track.addEventListener('touchstart', () => { disentuh = true; }, { passive: true });
    const lepasSentuh = () => {
        disentuh = false;
        clearTimeout(timerDiam);
        timerDiam = setTimeout(saatDiam, JEDA_DIAM);
    };
    track.addEventListener('touchend', lepasSentuh, { passive: true });
    track.addEventListener('touchcancel', lepasSentuh, { passive: true });

    prev?.addEventListener('click', () => geser(-1));
    next?.addEventListener('click', () => geser(1));

    track.addEventListener('keydown', (e) => {
        if (e.target !== track) return; // panah di dalam kartu (mis. tautan) dibiarkan
        if (e.key === 'ArrowRight') { e.preventDefault(); geser(1); }
        if (e.key === 'ArrowLeft') { e.preventDefault(); geser(-1); }
    });

    // Seret dengan mouse (sentuhan memakai scroll bawaan).
    track.addEventListener('pointerdown', (e) => {
        if (e.pointerType !== 'mouse' || e.button !== 0) return;
        rapikan();
        seret = { x: e.clientX, kiri: track.scrollLeft, pindah: false };
    });
    track.addEventListener('pointermove', (e) => {
        if (!seret) return;
        const dx = e.clientX - seret.x;
        if (!seret.pindah && Math.abs(dx) > 5) {
            seret.pindah = true;
            track.setPointerCapture(e.pointerId);
            track.style.scrollSnapType = 'none'; // snap dimatikan selama menyeret
            track.style.cursor = 'grabbing';
        }
        if (!seret.pindah) return;
        let x = seret.kiri - dx;
        // Mode berputar: lompat satu set selama menyeret supaya tidak pernah mentok.
        if (putar && x < lebarSet * 0.5) { seret.kiri += lebarSet; x += lebarSet; }
        else if (putar && x >= lebarSet * 1.5) { seret.kiri -= lebarSet; x -= lebarSet; }
        track.scrollLeft = x;
    });
    const selesai = (e) => {
        if (!seret) return;
        const tadiPindah = seret.pindah;
        seret = null;
        track.style.cursor = '';
        if (!tadiPindah) return;
        if (track.hasPointerCapture?.(e.pointerId)) track.releasePointerCapture(e.pointerId);
        // Kembalikan snap supaya berhenti rapi di tepi kartu.
        const kiri = track.scrollLeft;
        track.style.scrollSnapType = '';
        track.scrollLeft = kiri;
        // Batalkan klik yang muncul tepat setelah menyeret (hanya klik itu saja).
        const batalkanKlik = (k) => { k.preventDefault(); k.stopPropagation(); };
        track.addEventListener('click', batalkanKlik, { capture: true, once: true });
        setTimeout(() => track.removeEventListener('click', batalkanKlik, { capture: true }), 0);
    };
    track.addEventListener('pointerup', selesai);
    track.addEventListener('pointercancel', selesai);
    // Cegah browser menyeret gambar/tautan bawaan.
    track.addEventListener('dragstart', (e) => e.preventDefault());

    // Ukuran berubah (rotasi HP, jendela diubah, font selesai dimuat) -> hitung ulang.
    if ('ResizeObserver' in window) new ResizeObserver(sesuaikanUkuran).observe(track);
    sesuaikanUkuran();
}

export function pasangCarousel() {
    document.querySelectorAll('[data-carousel]').forEach(pasangSatu);
}
