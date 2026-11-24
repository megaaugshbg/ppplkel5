/*
 * Carousel horizontal ([data-carousel]) untuk daftar kartu mal di beranda.
 * - Track ([data-carousel-track]) memakai scroll-snap bawaan browser: swipe/sentuh
 *   dan trackpad bekerja tanpa JS.
 * - Tombol [data-carousel-prev] / [data-carousel-next] menggeser satu kartu; tombol
 *   dinonaktifkan di ujung.
 * - Mouse bisa menyeret (drag) track; klik setelah menyeret dibatalkan supaya kartu
 *   tidak terbuka tidak sengaja.
 * - Panah kiri/kanan keyboard saat track difokus.
 * - Menghormati prefers-reduced-motion (tanpa animasi gulir).
 */
const kurangiGerak = () => window.matchMedia('(prefers-reduced-motion: reduce)').matches;

function pasangSatu(akar) {
    const track = akar.querySelector('[data-carousel-track]');
    const prev = akar.querySelector('[data-carousel-prev]');
    const next = akar.querySelector('[data-carousel-next]');
    if (!track) return;

    // Jarak geser = lebar satu kartu + gap.
    const langkah = () => {
        const kartu = track.firstElementChild;
        if (!kartu) return track.clientWidth;
        const gap = parseFloat(getComputedStyle(track).columnGap) || 0;
        return kartu.getBoundingClientRect().width + gap;
    };

    const geser = (arah) => {
        track.scrollBy({ left: arah * langkah(), behavior: kurangiGerak() ? 'auto' : 'smooth' });
    };

    const perbaruiTombol = () => {
        const maks = track.scrollWidth - track.clientWidth - 2;
        if (prev) prev.disabled = track.scrollLeft <= 2;
        if (next) next.disabled = track.scrollLeft >= maks;
    };

    prev?.addEventListener('click', () => geser(-1));
    next?.addEventListener('click', () => geser(1));
    track.addEventListener('scroll', perbaruiTombol, { passive: true });
    if ('ResizeObserver' in window) new ResizeObserver(perbaruiTombol).observe(track);
    perbaruiTombol();

    track.addEventListener('keydown', (e) => {
        if (e.target !== track) return; // panah di dalam kartu (mis. tautan) dibiarkan
        if (e.key === 'ArrowRight') { e.preventDefault(); geser(1); }
        if (e.key === 'ArrowLeft') { e.preventDefault(); geser(-1); }
    });

    // Seret dengan mouse (sentuhan memakai scroll bawaan).
    let seret = null;
    track.addEventListener('pointerdown', (e) => {
        if (e.pointerType !== 'mouse' || e.button !== 0) return;
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
        if (seret.pindah) track.scrollLeft = seret.kiri - dx;
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
}

export function pasangCarousel() {
    document.querySelectorAll('[data-carousel]').forEach(pasangSatu);
}
