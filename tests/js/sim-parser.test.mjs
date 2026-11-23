// Jalankan: node --test tests/js
// Semua teks di bawah adalah contoh TIRUAN (bukan data SIM asli).
import test from 'node:test';
import assert from 'node:assert/strict';
import { parseSim, PESAN_BUKAN_SIM, PESAN_JK_GAGAL, PESAN_NAMA_BEDA } from '../../resources/js/sim-parser.js';

const SIM_PRIA = `SURAT IZIN MENGEMUDI
DRIVING LICENCE
1. NAMA BUDI CONTOH
2. TEMPAT/TGL LAHIR SURABAYA, 01-01-1990
3. JENIS KELAMIN PRIA GOL. DARAH O
4. ALAMAT JL. CONTOH NO. 1
SIM A 1234 5678 9012`;

const SIM_WANITA = `SURAT IZIN MENGEMUDI
NAMA SITI CONTOH
JENIS KELAMIN : WANITA
ALAMAT JL. TIRUAN 2`;

test('SIM pria -> laki-laki', () => {
    assert.deepEqual(parseSim(SIM_PRIA), { ok: true, jenisKelamin: 'laki-laki' });
});

test('SIM wanita -> perempuan', () => {
    assert.deepEqual(parseSim(SIM_WANITA), { ok: true, jenisKelamin: 'perempuan' });
});

test('varian LAKI-LAKI dan PEREMPUAN, huruf kecil & tanda baca', () => {
    assert.equal(parseSim('surat izin mengemudi\nJenis Kelamin: laki-laki').jenisKelamin, 'laki-laki');
    assert.equal(parseSim('Surat Izin Mengemudi\nSex / Jenis kelamin . perempuan').jenisKelamin, 'perempuan');
});

test('ambigu: dua token jenis kelamin -> gagal', () => {
    const r = parseSim('SURAT IZIN MENGEMUDI PRIA WANITA');
    assert.deepEqual(r, { ok: false, pesan: PESAN_JK_GAGAL });
});

test('ambigu: label JENIS KELAMIN dipakai untuk memilih token terdekat', () => {
    const teks = 'SURAT IZIN MENGEMUDI NAMA PRIA UTAMA SANTOSO ' + 'X '.repeat(12) + 'JENIS KELAMIN WANITA';
    assert.equal(parseSim(teks).jenisKelamin, 'perempuan');
});

test('tanpa token jenis kelamin -> gagal', () => {
    assert.deepEqual(parseSim('SURAT IZIN MENGEMUDI NAMA BUDI'), { ok: false, pesan: PESAN_JK_GAGAL });
});

test('bukan SIM -> gagal', () => {
    assert.deepEqual(parseSim('KARTU TANDA PENDUDUK NIK 1234 JENIS KELAMIN PRIA'), { ok: false, pesan: PESAN_BUKAN_SIM });
    assert.deepEqual(parseSim(''), { ok: false, pesan: PESAN_BUKAN_SIM });
});

test('salah baca ringan pada judul dan token', () => {
    assert.equal(parseSim('SURAT IZ1N MENGEMUD1\nJENIS KELAMIN WANITA').jenisKelamin, 'perempuan');
    assert.equal(parseSim('DRIVING LICENSE\nSEX PEREMPUAN').jenisKelamin, 'perempuan');
    assert.equal(parseSim('SURAT IZIN MENGEMUDI\nJENIS KELAMIN LAKI LAKI').jenisKelamin, 'laki-laki');
    assert.equal(parseSim('SURAT IZIN MENGEMUDI\nJENIS KELAMIN PEREMPUAM').jenisKelamin, 'perempuan');
});

test('nomor SIM 12 digit tanpa judul tetap dikenali sebagai SIM', () => {
    assert.equal(parseSim('1234 5678 9012 JENIS KELAMIN WANITA').jenisKelamin, 'perempuan');
    assert.equal(parseSim('NO 123456789012 PRIA').jenisKelamin, 'laki-laki');
});

test('pencocokan nama: mati secara default', () => {
    assert.equal(parseSim(SIM_PRIA, { namaAkun: 'Orang Lain Sekali' }).ok, true);
});

test('pencocokan nama bila dinyalakan', () => {
    assert.equal(parseSim(SIM_PRIA, { namaAkun: 'Budi Contoh', cekNama: true }).ok, true);
    assert.deepEqual(parseSim(SIM_PRIA, { namaAkun: 'Andi Wijaya', cekNama: true }), { ok: false, pesan: PESAN_NAMA_BEDA });
});
