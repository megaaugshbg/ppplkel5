// Parser teks OCR SIM -> jenis kelamin. Modul murni (tanpa DOM) supaya mudah diuji:
//   node --test tests/js
// Nilai jenis kelamin sama dengan User::JK_LAKI / User::JK_PEREMPUAN di server.

export const JK_LAKI = 'laki-laki';
export const JK_PEREMPUAN = 'perempuan';

// Pencocokan nama akun vs nama di SIM. MATI secara default: nama di SIM sering
// disingkat/bergelar/salah baca, sehingga risiko menolak pengguna sah lebih besar
// daripada manfaatnya.
export const CEK_NAMA = false;

export const PESAN_BUKAN_SIM = 'Dokumen tidak terbaca sebagai SIM. Coba foto ulang.';
export const PESAN_JK_GAGAL = 'Jenis kelamin pada SIM tidak terbaca. Pastikan SIM terang, tidak silau, dan seluruh kartu masuk bingkai.';
export const PESAN_NAMA_BEDA = 'Nama pada SIM berbeda dengan nama akun. Gunakan SIM milik sendiri.';

const JUDUL = ['SURAT IZIN MENGEMUDI', 'DRIVING LICENCE', 'DRIVING LICENSE'];
const TOKEN_PRIA = ['PRIA', 'LAKILAKI'];
const TOKEN_WANITA = ['WANITA', 'PEREMPUAN'];

/** Huruf besar, tanda baca -> spasi, spasi dikompres. */
export function normalisasi(teks) {
    return String(teks ?? '')
        .toUpperCase()
        .replace(/[^A-Z0-9\s]/g, ' ')
        .replace(/\s+/g, ' ')
        .trim();
}

/** Jarak Levenshtein. */
export function jarak(a, b) {
    if (a === b) return 0;
    let prev = Array.from({ length: b.length + 1 }, (_, i) => i);
    for (let i = 1; i <= a.length; i++) {
        const cur = [i];
        for (let j = 1; j <= b.length; j++) {
            cur[j] = Math.min(prev[j] + 1, cur[j - 1] + 1, prev[j - 1] + (a[i - 1] === b[j - 1] ? 0 : 1));
        }
        prev = cur;
    }
    return prev[b.length];
}

/** Apakah frasa (beberapa kata) muncul, toleran `maks` salah baca huruf. */
function adaFrasa(kata, frasa, maks) {
    const n = frasa.split(' ').length;
    for (let i = 0; i + n <= kata.length; i++) {
        if (jarak(kata.slice(i, i + n).join(' '), frasa) <= maks) return true;
    }
    return false;
}

function adalahSim(norm, kata) {
    if (JUDUL.some(j => adaFrasa(kata, j, 2))) return true;
    // Nomor SIM 12 digit (spasi/tanda hubung di antara digit diabaikan).
    return /(?<!\d)\d{12}(?!\d)/.test(norm.replace(/(?<=\d) (?=\d)/g, ''));
}

/** Toleransi salah baca menurut panjang kata: pendek harus persis. */
function toleransi(token) {
    return token.length >= 8 ? 1 : 0;
}

/** Posisi (indeks kata) semua token jenis kelamin yang cocok, per kelompok. */
function cariToken(kata, daftar) {
    const posisi = [];
    kata.forEach((k, i) => {
        // "LAKI LAKI" terpisah spasi juga dikenali lewat gabungan dua kata.
        const gabung = i + 1 < kata.length ? k + kata[i + 1] : null;
        for (const t of daftar) {
            if (jarak(k, t) <= toleransi(t) || (gabung && jarak(gabung, t) <= toleransi(t))) {
                posisi.push(i);
                break;
            }
        }
    });
    return posisi;
}

function posisiLabel(kata) {
    const idx = [];
    kata.forEach((k, i) => {
        if (jarak(k, 'SEX') === 0 || jarak(k, 'GENDER') === 0) idx.push(i);
        if (jarak(k, 'JENIS') <= 1 && i + 1 < kata.length && jarak(kata[i + 1], 'KELAMIN') <= 2) idx.push(i);
    });
    return idx;
}

/**
 * @param {string} teks  hasil OCR mentah
 * @param {{namaAkun?: string, cekNama?: boolean}} [opsi]
 * @returns {{ok: true, jenisKelamin: string} | {ok: false, pesan: string}}
 */
export function parseSim(teks, opsi = {}) {
    const norm = normalisasi(teks);
    const kata = norm.split(' ').filter(Boolean);

    if (!adalahSim(norm, kata)) return { ok: false, pesan: PESAN_BUKAN_SIM };

    let pria = cariToken(kata, TOKEN_PRIA);
    let wanita = cariToken(kata, TOKEN_WANITA);

    // Bila ada label "JENIS KELAMIN"/"SEX", utamakan token dalam 6 kata sesudahnya.
    const label = posisiLabel(kata);
    if (label.length && (pria.length || wanita.length)) {
        const dekat = p => p.filter(i => label.some(l => i > l && i - l <= 6));
        const p = dekat(pria);
        const w = dekat(wanita);
        if (p.length || w.length) { pria = p; wanita = w; }
    }

    if ((pria.length > 0) === (wanita.length > 0)) return { ok: false, pesan: PESAN_JK_GAGAL };

    const hasil = { ok: true, jenisKelamin: pria.length ? JK_LAKI : JK_PEREMPUAN };

    if ((opsi.cekNama ?? CEK_NAMA) && opsi.namaAkun && !namaCocok(opsi.namaAkun, norm)) {
        return { ok: false, pesan: PESAN_NAMA_BEDA };
    }
    return hasil;
}

/** Nama akun cocok bila setiap kata nama (>=3 huruf) punya kata mirip di teks SIM. */
export function namaCocok(namaAkun, normTeks) {
    const kataSim = normTeks.split(' ');
    const kataNama = normalisasi(namaAkun).split(' ').filter(k => k.length >= 3);
    if (!kataNama.length) return true;
    const cocok = kataNama.filter(n => kataSim.some(s => jarak(n, s) <= (n.length >= 6 ? 2 : 1)));
    return cocok.length >= Math.ceil(kataNama.length / 2);
}
