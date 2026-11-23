// Halaman Verifikasi SIM. Foto SIM hanya ada di memori browser: tidak diunggah,
// tidak disimpan, dibuang setelah OCR. Server hanya menerima jenis kelamin.
import { parseSim } from './sim-parser.js';

const RASIO = 1.586;      // rasio kartu SIM (ID-1)
const LEBAR_OCR = 1600;   // skala gambar sebelum OCR
const FRAME_PERSEN = 0.9; // lebar bingkai panduan relatif terhadap lebar video

const $ = id => document.getElementById(id);
const el = {
    kamera: $('area-kamera'), video: $('video'), canvas: $('canvas'), bingkai: $('bingkai'),
    pesanKamera: $('pesan-kamera'), btnAmbil: $('btn-ambil'), btnGanti: $('btn-ganti'),
    proses: $('area-proses'), progresTeks: $('progres-teks'), progresBar: $('progres-bar'),
    sukses: $('area-sukses'), hasilJk: $('hasil-jk'), formSimpan: $('form-simpan'), inputJk: $('input-jk'),
    gagal: $('area-gagal'), pesanGagal: $('pesan-gagal'),
    ulang: document.querySelectorAll('[data-foto-ulang]'),
};

let stream = null;

const tampil = (bagian) => {
    for (const [nama, node] of Object.entries({ kamera: el.kamera, proses: el.proses, sukses: el.sukses, gagal: el.gagal })) {
        node.classList.toggle('hidden', nama !== bagian);
    }
};

function hentikanKamera() {
    stream?.getTracks().forEach(t => t.stop());
    stream = null;
    el.video.srcObject = null;
}

function kosongkanCanvas() {
    el.canvas.getContext('2d')?.clearRect(0, 0, el.canvas.width, el.canvas.height);
    el.canvas.width = 0;
    el.canvas.height = 0;
}

function pesanKamera(teks) {
    el.pesanKamera.textContent = teks;
    el.pesanKamera.classList.remove('hidden');
    el.btnAmbil.disabled = true;
}

const PESAN_IZIN = 'Izinkan akses kamera di browser untuk melanjutkan.';
const PESAN_TIDAK_ADA = 'Kamera tidak ditemukan di perangkat ini.';
const PESAN_SIBUK = 'Kamera tidak dapat dibuka. Tutup aplikasi lain yang memakai kamera lalu coba lagi.';

function pesanDariError(e) {
    if (e?.name === 'NotAllowedError' || e?.name === 'SecurityError') return PESAN_IZIN;
    if (e?.name === 'NotFoundError' || e?.name === 'OverconstrainedError') return PESAN_TIDAK_ADA;
    return PESAN_SIBUK;
}

// Pilihan kamera aktif: { deviceId } bila diketahui, atau { facing } ('environment' | 'user').
let pilihan = { facing: 'environment' };
let daftarKamera = [];

/** Buka stream sesuai pilihan; hentikan stream lama lebih dulu. exact = paksa kamera itu. */
async function bukaStream(p, exact) {
    hentikanKamera();
    const video = { width: { ideal: 1920 }, height: { ideal: 1080 } };
    if (p.deviceId) video.deviceId = exact ? { exact: p.deviceId } : { ideal: p.deviceId };
    else video.facingMode = exact ? { exact: p.facing } : { ideal: p.facing };
    stream = await navigator.mediaDevices.getUserMedia({ video, audio: false });
    el.video.srcObject = stream;
    await el.video.play();
}

/** Setelah izin diberikan: catat kamera aktif, atur cermin (hanya pratinjau) dan tombol Ganti Kamera. */
async function perbaruiKamera() {
    const track = stream?.getVideoTracks()[0];
    const set = track?.getSettings?.() ?? {};
    try {
        const semua = await navigator.mediaDevices.enumerateDevices();
        daftarKamera = semua.filter(d => d.kind === 'videoinput');
    } catch {
        daftarKamera = [];
    }
    if (set.deviceId) pilihan = { deviceId: set.deviceId, facing: pilihan.facing };
    const label = (track?.label || '').toLowerCase();
    const belakang = set.facingMode ? set.facingMode === 'environment' : /back|rear|environment|belakang/.test(label);
    pilihan.facing = belakang ? 'environment' : 'user';
    // Cermin hanya pada elemen <video>; canvas/OCR tetap tidak terbalik.
    el.video.style.transform = belakang ? '' : 'scaleX(-1)';
    el.btnGanti.classList.toggle('hidden', daftarKamera.length < 2);
}

async function mulaiKamera() {
    tampil('kamera');
    el.pesanKamera.classList.add('hidden');
    el.btnAmbil.disabled = true;
    el.btnGanti.disabled = true;

    if (!window.isSecureContext || !navigator.mediaDevices?.getUserMedia) {
        pesanKamera('Kamera hanya bisa dipakai lewat HTTPS atau localhost/127.0.0.1. Buka aplikasi lewat alamat tersebut.');
        return;
    }
    try {
        try {
            await bukaStream(pilihan, false);
        } catch (e) {
            // Tidak ada kamera yang cocok (mis. laptop): pakai kamera apa pun yang tersedia.
            if (e?.name !== 'OverconstrainedError' && e?.name !== 'NotFoundError') throw e;
            hentikanKamera();
            stream = await navigator.mediaDevices.getUserMedia({ video: true, audio: false });
            el.video.srcObject = stream;
            await el.video.play();
        }
        await perbaruiKamera();
        el.btnAmbil.disabled = false;
        el.btnGanti.disabled = false;
    } catch (e) {
        hentikanKamera();
        pesanKamera(pesanDariError(e));
    }
}

/** Bergantian ke kamera berikutnya; bila gagal, kembali ke kamera sebelumnya. */
async function gantiKamera() {
    if (daftarKamera.length < 2) return;
    const sebelumnya = { ...pilihan };
    let tujuan;
    const idx = daftarKamera.findIndex(d => d.deviceId && d.deviceId === pilihan.deviceId);
    if (daftarKamera.every(d => d.deviceId) && idx >= 0) {
        tujuan = { deviceId: daftarKamera[(idx + 1) % daftarKamera.length].deviceId, facing: pilihan.facing };
    } else {
        tujuan = { facing: pilihan.facing === 'environment' ? 'user' : 'environment' };
    }

    el.btnGanti.disabled = true;
    el.btnAmbil.disabled = true;
    el.pesanKamera.classList.add('hidden');
    try {
        await bukaStream(tujuan, true);
        pilihan = tujuan;
        await perbaruiKamera();
    } catch (e) {
        const pesan = pesanDariError(e);
        try {
            await bukaStream(sebelumnya, true); // kembali ke kamera sebelumnya
            pilihan = sebelumnya;
            await perbaruiKamera();
            el.pesanKamera.textContent = 'Tidak bisa berganti kamera. ' + pesan;
            el.pesanKamera.classList.remove('hidden');
        } catch (e2) {
            hentikanKamera();
            pesanKamera(pesanDariError(e2));
            el.btnGanti.disabled = false;
            return;
        }
    }
    el.btnAmbil.disabled = false;
    el.btnGanti.disabled = false;
}

/** Crop ke bingkai panduan (tengah video), skala ~1600px, grayscale + kontras. */
function tangkapFrame() {
    const vw = el.video.videoWidth;
    const vh = el.video.videoHeight;
    let cw = vw * FRAME_PERSEN;
    let ch = cw / RASIO;
    if (ch > vh * FRAME_PERSEN) { ch = vh * FRAME_PERSEN; cw = ch * RASIO; }
    const sx = (vw - cw) / 2;
    const sy = (vh - ch) / 2;

    const skala = LEBAR_OCR / cw;
    el.canvas.width = LEBAR_OCR;
    el.canvas.height = Math.round(ch * skala);
    const ctx = el.canvas.getContext('2d', { willReadFrequently: true });
    ctx.drawImage(el.video, sx, sy, cw, ch, 0, 0, el.canvas.width, el.canvas.height);

    const img = ctx.getImageData(0, 0, el.canvas.width, el.canvas.height);
    const d = img.data;
    const kontras = 1.5;
    for (let i = 0; i < d.length; i += 4) {
        const g = 0.299 * d[i] + 0.587 * d[i + 1] + 0.114 * d[i + 2];
        const v = Math.max(0, Math.min(255, (g - 128) * kontras + 128));
        d[i] = d[i + 1] = d[i + 2] = v;
    }
    ctx.putImageData(img, 0, 0);
}

async function ambilFoto() {
    el.btnAmbil.disabled = true;
    tangkapFrame();
    el.video.pause(); // gambar "beku" selama membaca
    tampil('proses');
    el.progresTeks.textContent = 'Membaca SIM…';
    el.progresBar.style.width = '0%';

    let worker = null;
    let teks = '';
    try {
        const { createWorker } = await import('tesseract.js'); // dimuat hanya di halaman ini
        worker = await createWorker('eng', 1, {
            logger: m => {
                if (typeof m.progress === 'number') {
                    const p = Math.round(m.progress * 100);
                    el.progresBar.style.width = p + '%';
                    el.progresTeks.textContent = `Membaca SIM… ${p}%`;
                }
            },
        });
        const blob = await new Promise(res => el.canvas.toBlob(res, 'image/png'));
        const { data } = await worker.recognize(blob);
        teks = data.text || '';
    } catch (e) {
        teks = '';
        console.error('OCR gagal', e);
    } finally {
        await worker?.terminate().catch(() => {});
        // Buang gambar dari memori.
        hentikanKamera();
        kosongkanCanvas();
    }

    const hasil = parseSim(teks);
    teks = ''; // teks OCR tidak disimpan

    if (hasil.ok) {
        el.inputJk.value = hasil.jenisKelamin;
        el.hasilJk.textContent = hasil.jenisKelamin === 'perempuan' ? 'Perempuan' : 'Laki-laki';
        tampil('sukses');
    } else {
        el.pesanGagal.textContent = hasil.pesan;
        tampil('gagal');
    }
}

el.btnAmbil.addEventListener('click', ambilFoto);
el.btnGanti.addEventListener('click', gantiKamera);
el.ulang.forEach(b => b.addEventListener('click', () => { el.inputJk.value = ''; mulaiKamera(); }));
window.addEventListener('pagehide', () => { hentikanKamera(); kosongkanCanvas(); });

mulaiKamera();
