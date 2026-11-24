/*
 * Navbar transparan di atas hero (hanya beranda, header[data-transparan]).
 * Setelah digulir lebih dari BATAS px, atribut data-solid menjadi "true" sehingga
 * navbar berubah putih solid (gaya ada di components/navbar.blade.php).
 * Navbar juga dibuat solid selama ada elemen di dalamnya yang difokus keyboard,
 * supaya kotak cari dan tautan tetap terbaca.
 */
const BATAS = 40;

export function pasangNavbarTransparan() {
    const header = document.querySelector('header[data-transparan]');
    if (!header) return;

    let menunggu = false;

    // Menu desktop digeser ke tengah navbar saat transparan. Jarak geser dihitung dari posisi
    // alaminya (offsetLeft tidak terpengaruh transform) ke titik tengah lebar navbar.
    const menu = header.querySelector('[data-menu-utama]');
    const ukurGeser = () => {
        if (!menu || menu.offsetParent === null) return; // tersembunyi di HP
        const geser = header.clientWidth / 2 - (menu.offsetLeft + menu.offsetWidth / 2);
        header.style.setProperty('--geser', Math.round(geser) + 'px');
    };

    // Hanya fokus keyboard (bukan klik mouse) yang membuat navbar solid.
    const fokusKeyboard = () => {
        try {
            return header.matches(':has(:focus-visible)');
        } catch {
            return header.matches(':focus-within'); // browser tanpa dukungan :has()
        }
    };

    const perbarui = () => {
        menunggu = false;
        const fokusDiDalam = fokusKeyboard();
        header.dataset.solid = (window.scrollY > BATAS || fokusDiDalam) ? 'true' : 'false';
    };

    const jadwalkan = () => {
        if (!menunggu) {
            menunggu = true;
            requestAnimationFrame(perbarui);
        }
    };

    window.addEventListener('scroll', jadwalkan, { passive: true });
    header.addEventListener('focusin', jadwalkan);
    header.addEventListener('focusout', () => setTimeout(jadwalkan, 0));
    window.addEventListener('pageshow', jadwalkan); // kembali lewat tombol Back (bfcache)
    window.addEventListener('resize', ukurGeser, { passive: true });
    if (document.fonts && document.fonts.ready) document.fonts.ready.then(ukurGeser); // lebar teks berubah setelah font termuat

    ukurGeser();
    perbarui();
}
