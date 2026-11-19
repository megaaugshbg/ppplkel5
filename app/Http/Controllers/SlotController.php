<?php

namespace App\Http\Controllers;

use App\Models\LokasiParkir;
use App\Models\SlotParkir;
use Illuminate\Http\Request;

class SlotController extends Controller
{
    /**
     * Simulasi alternate flow "Slot telah dipesan pengguna lain" (slot diserobot
     * tepat saat ditekan Pesan). Kunci: nama lokasi => kode slot.
     * HAPUS saat pemesanan sudah memakai tabel booking di database
     * (cek ulang ketersediaan langsung dari database).
     */
    private const REBUTAN = [
        'Parkir Mall Galaxy' => ['R-06'],
    ];


    // Pilih Tipe Slot (3.3.6): Normal, Woman, Priority, EV beserta jumlah slot kosong
    public function pilihTipe(int $lokasi)
    {
        $lokasi = LokasiParkir::denganJumlahSlot()->findOrFail($lokasi);

        $slotSesi = $this->slotTerisiDiSesi($lokasi->id);

        $tipe = collect($lokasi->ringkasan_slot)->map(function ($r, $kode) use ($lokasi, $slotSesi) {
            // Kurangi slot yang baru dipesan di sesi ini (pemesanan belum tersimpan ke database).
            $tersedia = $r['tersedia'] - ($slotSesi
                ? SlotParkir::where('lokasi_parkir_id', $lokasi->id)->tipe($kode)->tersedia()->whereIn('kode', $slotSesi)->count()
                : 0);

            return array_merge($r, [
                'kode'      => $kode,
                'tersedia'  => max(0, $tersedia),
                'bisaPesan' => in_array($kode, SlotParkir::TIPE, true),
                'url'       => route('slot-parkir', ['lokasi' => $lokasi->id, 'tipe' => $kode]),
            ]);
        })->values();

        // Lokasi tutup tetap bisa dilihat; pemesanan ditolak (lihat pesan()).
        $bannerTutup = $this->bannerTutup($lokasi);

        return view('pilih-tipe', compact('lokasi', 'tipe', 'bannerTutup'));
    }

    // Cari Slot Parkir (3.3.7): denah lantai dan status slot
    public function index(Request $request, int $lokasi)
    {
        $lokasi = LokasiParkir::findOrFail($lokasi);

        $tipe = $request->query('tipe');
        if (! in_array($tipe, SlotParkir::SEMUA_TIPE, true)) {
            return redirect()->route('lokasi.tipe', $lokasi->id);
        }

        $slots = $this->daftarSlot($lokasi->id, $tipe);
        $tersedia = count(array_filter($slots, fn ($s) => ! $s['terisi']));

        return view('slot-parkir', [
            'lokasi' => $lokasi,
            'tipe' => $tipe,
            'labelTipe' => SlotParkir::LABEL[$tipe],
            'bisaPesan' => in_array($tipe, SlotParkir::TIPE, true),
            'tutup' => ! $lokasi->sedang_buka,
            'bannerTutup' => $this->bannerTutup($lokasi),
            'perLantai' => collect($slots)->groupBy('lantai'),
            'tersedia' => $tersedia,
        ]);
    }

    // Pesan Slot Parkir (3.3.8)
    public function pesan(Request $request)
    {
        $idLokasi = (int) $request->input('lokasi');
        $tipe = $request->input('tipe', 'reguler');
        $balik = route('slot-parkir', ['lokasi' => $idLokasi, 'tipe' => $tipe]);

        // Priority & EV: denah boleh dilihat, pemesanan belum dibuka.
        if (in_array($tipe, SlotParkir::SEMUA_TIPE, true) && ! in_array($tipe, SlotParkir::TIPE, true)) {
            return redirect($balik)->with('error', 'Pemesanan tipe ini segera hadir.');
        }
        if (! in_array($tipe, SlotParkir::TIPE, true)) {
            $tipe = 'reguler';
            $balik = route('slot-parkir', ['lokasi' => $idLokasi, 'tipe' => $tipe]);
        }
        $kode = $request->input('slot');

        $lokasi = LokasiParkir::find($idLokasi);
        abort_if(! $lokasi, 404);

        // lokasi di luar jam operasional / ditutup manual: tolak di server, bukan hanya di view
        if (! $lokasi->sedang_buka) {
            return redirect($balik)->with('error', 'Lokasi parkir sedang tutup.');
        }

        if (! $kode) {
            return redirect($balik)->with('error', 'Pilih slot parkir terlebih dahulu.');
        }

        $slot = collect($this->daftarSlot($idLokasi, $tipe))->firstWhere('kode', $kode);
        if (! $slot) {
            return redirect($balik)->with('error', 'Slot parkir tidak ditemukan.');
        }

        $key = $idLokasi . '-' . $kode;

        // verifikasi ketersediaan slot
        if ($slot['terisi'] || in_array($kode, self::REBUTAN[$lokasi->nama] ?? [], true)) {
            session()->push('slot_terisi', $key);

            return redirect($balik)->with('error', 'Slot telah dipesan pengguna lain. Silakan pilih slot lain.');
        }

        // simulasi gagal menyimpan pesanan
        if ($request->boolean('gagal')) {
            return redirect($balik)->with('error', 'Terjadi kesalahan saat menyimpan pesanan. Silakan coba lagi.');
        }

        // simpan pemesanan (sementara di session, belum ke database)
        session()->push('slot_terisi', $key);
        session()->put('pesanan_terakhir', [
            'kode' => 'MKR-' . str_pad((string) random_int(1, 999), 3, '0', STR_PAD_LEFT),
            'lokasi' => $lokasi->nama,
            'alamat' => $lokasi->alamat,
            'tipe' => SlotParkir::LABEL[$tipe],
            'slot' => $kode,
            'tarif' => $lokasi->tarif,
            'status' => 'Terpesan',
        ]);

        return redirect('/konfirmasi-pesanan');
    }

    public function konfirmasi()
    {
        $pesanan = session('pesanan_terakhir');
        if (! $pesanan) {
            return redirect()->route('home');
        }

        return view('konfirmasi-pesanan', compact('pesanan'));
    }

    /**
     * Slot satu lokasi & tipe dari tabel slot_parkir, urut lantai lalu kode.
     * terisi = status bukan 'tersedia', ATAU sudah dipesan di sesi ini
     * (pemesanan masih disimpan di session, belum ke database).
     */
    private function daftarSlot(int $idLokasi, string $tipe): array
    {
        $slotSesi = $this->slotTerisiDiSesi($idLokasi);

        // Diurutkan di PHP (bukan orderBy('lantai')) supaya tetap jalan sebelum
        // migration kolom lantai dijalankan: semua slot dianggap lantai 1.
        return SlotParkir::where('lokasi_parkir_id', $idLokasi)
            ->tipe($tipe)
            ->get()
            ->map(fn (SlotParkir $s) => [
                'kode'   => $s->kode,
                'lantai' => $s->lantai ?? 1,
                'terisi' => $s->status !== SlotParkir::STATUS_TERSEDIA || in_array($s->kode, $slotSesi, true),
            ])
            ->sortBy(fn ($s) => sprintf('%03d-%04d-%s', $s['lantai'], strlen($s['kode']), $s['kode'])) // R-2 sebelum R-10
            ->values()
            ->all();
    }

    /** Teks banner untuk lokasi yang sedang tutup, atau null bila buka. */
    private function bannerTutup(LokasiParkir $lokasi): ?string
    {
        $ket = $lokasi->keterangan_tutup;
        if ($ket === null) {
            return null;
        }

        return 'Lokasi parkir sedang tutup. ' . (str_starts_with($ket, 'Buka pukul ')
            ? 'Pemesanan dibuka pukul ' . substr($ket, strlen('Buka pukul ')) . '.'
            : $ket . '.');
    }

    /** Kode slot di lokasi ini yang sudah dipesan di sesi sekarang. */
    private function slotTerisiDiSesi(int $idLokasi): array
    {
        $awalan = $idLokasi . '-';

        return collect(session('slot_terisi', []))
            ->filter(fn ($k) => str_starts_with($k, $awalan))
            ->map(fn ($k) => substr($k, strlen($awalan)))
            ->values()
            ->all();
    }
}
