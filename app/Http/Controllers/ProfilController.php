<?php

namespace App\Http\Controllers;

use App\Models\LokasiParkir;
use App\Models\SlotParkir;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProfilController extends Controller
{
    public function index(Request $request)
    {
        return view('profil', ['user' => $request->user()]);
    }

    /** Halaman kamera verifikasi SIM. lokasi & tipe (opsional) = denah asal. */
    public function verifikasiSim(Request $request)
    {
        $lokasi = filter_var($request->query('lokasi'), FILTER_VALIDATE_INT);
        $tipe = $request->query('tipe');
        $adaDenah = $lokasi && in_array($tipe, SlotParkir::SEMUA_TIPE, true) && LokasiParkir::whereKey($lokasi)->exists();

        return view('verifikasi-sim', [
            'user'      => $request->user(),
            'lokasi'    => $adaDenah ? $lokasi : null,
            'tipe'      => $adaDenah ? $tipe : null,
            'urlBatal'  => $adaDenah ? route('slot-parkir', ['lokasi' => $lokasi, 'tipe' => $tipe]) : route('profil'),
        ]);
    }

    /**
     * Simpan hasil verifikasi SIM. Browser hanya mengirim jenis kelamin hasil
     * OCR; foto, nomor SIM, nama, dan teks OCR tidak pernah sampai ke server.
     * (OCR di sisi klien bisa dimanipulasi; lihat keterbatasan di laporan.)
     */
    public function simpanVerifikasiSim(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'jenis_kelamin' => ['required', 'in:' . User::JK_LAKI . ',' . User::JK_PEREMPUAN],
        ], [
            'jenis_kelamin.required' => 'Jenis kelamin pada SIM tidak terbaca.',
            'jenis_kelamin.in'       => 'Jenis kelamin pada SIM tidak terbaca.',
        ]);

        if (! $request->user()->terapkanVerifikasiSim($data['jenis_kelamin'])) {
            return redirect()->route('profil')->with('error', 'Jenis kelamin pada SIM berbeda dengan data terverifikasi. Jenis kelamin tidak dapat diubah.');
        }

        $pesan = $data['jenis_kelamin'] === User::JK_LAKI
            ? 'Verifikasi Wanita berhasil. Woman Parking khusus pengguna wanita.'
            : 'Verifikasi Wanita berhasil.';

        return $this->kembali($request)->with('sukses', $pesan);
    }

    /**
     * Simpan / ubah nomor kartu disabilitas.
     *
     * SIMULASI VERIFIKASI: belum ada petugas atau layanan yang memeriksa kartu.
     * Nomor dengan format valid (User::FORMAT_KARTU_DISABILITAS) langsung diberi
     * status 'terverifikasi'. Ganti dengan alur verifikasi sungguhan (mis. status
     * 'menunggu' sampai diperiksa) bila sudah tersedia.
     *
     * Dipakai dari halaman Profil dan dari denah Priority Parking. Bila dikirim
     * dari denah (field lokasi & tipe), pengguna dikembalikan ke denah yang sama.
     */
    public function simpanKartu(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nomor_kartu_disabilitas' => ['required', 'string', 'regex:' . User::FORMAT_KARTU_DISABILITAS],
        ], [
            'nomor_kartu_disabilitas.required' => 'Nomor kartu disabilitas wajib diisi.',
            'nomor_kartu_disabilitas.regex'    => 'Format nomor kartu disabilitas tidak valid (8–20 huruf/angka).',
        ]);

        $request->user()->update([
            'nomor_kartu_disabilitas'  => strtoupper($data['nomor_kartu_disabilitas']),
            'status_kartu_disabilitas' => User::KARTU_TERVERIFIKASI, // simulasi: langsung terverifikasi
        ]);

        return $this->kembali($request)->with('sukses', 'Kartu disabilitas terverifikasi.');
    }

    public function hapusKartu(Request $request): RedirectResponse
    {
        $request->user()->update([
            'nomor_kartu_disabilitas'  => null,
            'status_kartu_disabilitas' => null,
        ]);

        return redirect()->route('profil')->with('sukses', 'Kartu disabilitas dihapus.');
    }

    /**
     * Tujuan setelah simpan kartu / verifikasi SIM: denah asal (dibangun dari route, bukan URL
     * mentah dari input, supaya tidak bisa dipakai untuk redirect ke luar),
     * atau halaman Profil.
     */
    private function kembali(Request $request): RedirectResponse
    {
        $lokasi = filter_var($request->input('lokasi'), FILTER_VALIDATE_INT);
        $tipe = $request->input('tipe');

        if ($lokasi && in_array($tipe, SlotParkir::SEMUA_TIPE, true) && LokasiParkir::whereKey($lokasi)->exists()) {
            return redirect()->route('slot-parkir', ['lokasi' => $lokasi, 'tipe' => $tipe]);
        }

        return redirect()->route('profil');
    }
}
