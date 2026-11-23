<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\LokasiParkir;
use App\Models\SlotParkir;
use App\Models\User;
use App\Support\PesananSesi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class SlotController extends Controller
{
    private const PESAN_DIAMBIL = 'Slot telah dipesan pengguna lain. Silakan pilih slot lain.';
    private const PESAN_GAGAL = 'Terjadi kesalahan saat menyimpan pesanan. Silakan coba lagi.';

    // Pilih Tipe Slot (3.3.6)
    public function pilihTipe(Request $request, int $lokasi)
    {
        $lokasi = LokasiParkir::denganJumlahSlot()->findOrFail($lokasi);
        $user = $request->user();

        // Jumlah slot kosong dibaca dari status slot di database (terpesan & terisi = tidak tersedia).
        $tipe = collect($lokasi->ringkasan_slot)->map(function ($r, $kode) use ($lokasi, $user) {
            return array_merge($r, [
                'kode'      => $kode,
                'bisaPesan' => in_array($kode, SlotParkir::TIPE, true),
                // Petunjuk singkat bila syarat profil belum terpenuhi (kartu tetap bisa dibuka).
                'syarat'    => $this->syaratProfil($user, $kode)['tombol'] ?? null,
                'url'       => route('slot-parkir', ['lokasi' => $lokasi->id, 'tipe' => $kode]),
            ]);
        })->values();

        $bannerTutup = $this->bannerTutup($lokasi);

        return view('pilih-tipe', compact('lokasi', 'tipe', 'bannerTutup'));
    }

    // Cari Slot Parkir (3.3.7)
    public function index(Request $request, int $lokasi)
    {
        $lokasi = LokasiParkir::findOrFail($lokasi);

        $tipe = $request->query('tipe');
        if (! in_array($tipe, SlotParkir::SEMUA_TIPE, true)) {
            return redirect()->route('lokasi.tipe', $lokasi->id);
        }

        $adalahEv = $tipe === SlotParkir::TIPE_EV;

        // Filter konektor hanya berlaku untuk EV.
        $konektor = $adalahEv && in_array($request->query('konektor'), SlotParkir::KONEKTOR, true)
            ? $request->query('konektor')
            : null;

        $semuaSlot = $this->daftarSlot($lokasi->id, $tipe);
        $slots = $konektor
            ? array_values(array_filter($semuaSlot, fn ($s) => $s['konektor'] === $konektor))
            : $semuaSlot;

        // Chip filter: hanya konektor yang benar-benar ada di lokasi ini.
        $daftarKonektor = $adalahEv
            ? collect($semuaSlot)->pluck('konektor')->filter()->unique()->sort()->values()->all()
            : [];

        return view('slot-parkir', [
            'lokasi'         => $lokasi,
            'tipe'           => $tipe,
            'labelTipe'      => SlotParkir::LABEL[$tipe],
            'bisaPesan'      => in_array($tipe, SlotParkir::TIPE, true),
            'tutup'          => ! $lokasi->sedang_buka,
            'syarat'         => $this->syaratProfil($request->user(), $tipe),
            'bannerTutup'    => $this->bannerTutup($lokasi),
            'perLantai'      => collect($slots)->groupBy('lantai'),
            'tersedia'       => count(array_filter($slots, fn ($s) => ! $s['terisi'])),
            'adalahEv'       => $adalahEv,
            'daftarKonektor' => $daftarKonektor,
            'konektor'       => $konektor,
            'tarifEv'        => $lokasi->tarif_ev_kwh,
        ]);
    }

    // Pesan Slot Parkir (3.3.8)
    public function pesan(Request $request)
    {
        $idLokasi = (int) $request->input('lokasi');
        $tipe = $request->input('tipe', 'reguler');
        $balik = route('slot-parkir', ['lokasi' => $idLokasi, 'tipe' => $tipe]);

        // Prioritas: denah boleh dilihat, pemesanan belum dibuka.
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

        // Syarat profil per tipe (Woman: perempuan, Priority: kartu disabilitas).
        // Dicek di server, bukan hanya lewat tombol nonaktif di view.
        $syarat = $this->syaratProfil($request->user(), $tipe);
        if ($syarat && ! $syarat['terpenuhi']) {
            return redirect($balik)->with('error', $syarat['error']);
        }

        if (! $kode) {
            return redirect($balik)->with('error', 'Pilih slot parkir terlebih dahulu.');
        }

        $slot = collect($this->daftarSlot($idLokasi, $tipe))->firstWhere('kode', $kode);
        if (! $slot) {
            return redirect($balik)->with('error', 'Slot parkir tidak ditemukan.');
        }

        // verifikasi ketersediaan slot (cek awal; dicek ulang dengan kunci baris di transaksi)
        if ($slot['terisi']) {
            return redirect($balik)->with('error', self::PESAN_DIAMBIL);
        }

        // Slot EV wajib punya data charger agar info di konfirmasi akurat.
        if ($tipe === SlotParkir::TIPE_EV && (! $slot['konektor'] || ! $slot['daya'])) {
            return redirect($balik)->with('error', 'Data charger slot ini belum lengkap. Silakan pilih slot lain.');
        }

        // simulasi gagal menyimpan pesanan
        if ($request->boolean('gagal')) {
            return redirect($balik)->with('error', self::PESAN_GAGAL);
        }

        // Simpan pemesanan ke tabel booking. Baris user & slot dikunci (lockForUpdate) supaya
        // satu pengguna hanya punya satu pesanan aktif dan satu slot tidak bisa dipesan dua orang.
        $user = $request->user();
        try {
            $hasil = DB::transaction(function () use ($user, $lokasi, $tipe, $kode) {
                User::whereKey($user->id)->lockForUpdate()->first();

                $aktif = Booking::milik($user->id)->aktif()->orderByDesc('id')->first();
                if ($aktif) {
                    return ['aktif' => $aktif];
                }

                $baris = SlotParkir::where('lokasi_parkir_id', $lokasi->id)
                    ->tipe($tipe)
                    ->where('kode', $kode)
                    ->lockForUpdate()
                    ->first();

                if (! $baris || $baris->status !== SlotParkir::STATUS_TERSEDIA) {
                    return ['diambil' => true];
                }

                $ev = $tipe === SlotParkir::TIPE_EV;
                $booking = Booking::create([
                    'kode'             => Booking::buatKodeUnik(),
                    'user_id'          => $user->id,
                    'lokasi_parkir_id' => $lokasi->id,
                    'slot_parkir_id'   => $baris->id,
                    'nama_lokasi'      => $lokasi->nama,
                    'alamat_lokasi'    => $lokasi->alamat,
                    'label_tipe'       => SlotParkir::LABEL[$tipe],
                    'kode_slot'        => $baris->kode,
                    'konektor'         => $ev ? $baris->konektor : null,
                    'daya_kw'          => $ev ? $baris->daya_kw : null,
                    'tarif'            => $lokasi->tarif,
                    'tarif_ev_kwh'     => $ev ? $lokasi->tarif_ev_kwh : null,
                    'status'           => Booking::TERPESAN,
                ]);

                $baris->update(['status' => SlotParkir::STATUS_TERPESAN]);

                return ['booking' => $booking];
            });
        } catch (Throwable $e) {
            report($e);

            return redirect($balik)->with('error', self::PESAN_GAGAL);
        }

        // Satu pengguna hanya boleh punya satu pesanan aktif: arahkan ke pesanan itu.
        if (isset($hasil['aktif'])) {
            $aktif = $hasil['aktif'];
            $tujuan = match ($aktif->status) {
                Booking::TERPESAN      => 'pembayaran',
                Booking::SUDAH_DIBAYAR => 'check-in',
                default                => 'check-out',
            };

            return redirect()->route($tujuan)->with('error', 'Kamu masih punya pesanan aktif (' . $aktif->kode
                . '). Selesaikan atau batalkan dulu sebelum memesan lagi.');
        }

        if (isset($hasil['diambil'])) {
            return redirect($balik)->with('error', self::PESAN_DIAMBIL);
        }

        // Pesanan berhasil disimpan: langsung lanjut ke halaman Pembayaran.
        return redirect()->route('pembayaran');
    }

    public function konfirmasi()
    {
        $pesanan = PesananSesi::ambil();
        if (! $pesanan) {
            return redirect()->route('home');
        }

        return view('konfirmasi-pesanan', compact('pesanan'));
    }

    /**
     * Syarat profil untuk memesan tipe tertentu. null = tanpa syarat (Normal, EV).
     * Hasil: terpenuhi, banner (teks di denah), aksi ('profil' | 'kartu' | null),
     * tombol (keterangan di bawah tombol Pesan bila belum terpenuhi), error (pesan server).
     */
    private function syaratProfil(?User $user, string $tipe): ?array
    {
        return match ($tipe) {
            SlotParkir::TIPE_WANITA    => $this->syaratWanita($user),
            SlotParkir::TIPE_PRIORITAS => $this->syaratPrioritas($user),
            default                    => null,
        };
    }

    /** Woman Parking: SIM terverifikasi (kamera) dan jenis kelamin dari SIM = perempuan. */
    private function syaratWanita(?User $user): array
    {
        if (! $user?->simTerverifikasi()) {
            return [
                'terpenuhi' => false,
                'banner'    => 'Verifikasi Wanita untuk memesan Woman Parking',
                'aksi'      => 'sim',
                'tombol'    => 'Perlu verifikasi wanita',
                'error'     => 'Verifikasi Wanita terlebih dahulu untuk memesan Woman Parking.',
            ];
        }

        if ($user->perempuan()) {
            return ['terpenuhi' => true, 'banner' => '✓ Terverifikasi sebagai pengguna Woman Parking', 'aksi' => null, 'tombol' => null, 'error' => null];
        }

        return [
            'terpenuhi' => false,
            'banner'    => 'Woman Parking khusus pengguna wanita',
            'aksi'      => null,
            'tombol'    => 'Khusus pengguna wanita',
            'error'     => 'Woman Parking khusus pengguna wanita.',
        ];
    }

    /**
     * Priority Parking: kartu disabilitas berstatus terverifikasi.
     * (Verifikasi kartu masih SIMULASI, lihat ProfilController::simpanKartu.)
     */
    private function syaratPrioritas(?User $user): array
    {
        if ($user?->kartuDisabilitasTerverifikasi()) {
            return ['terpenuhi' => true, 'banner' => '✓ Kartu disabilitas terverifikasi', 'aksi' => null, 'tombol' => null, 'error' => null];
        }

        return [
            'terpenuhi' => false,
            'banner'    => 'Tambahkan kartu disabilitas untuk memesan Priority Parking',
            'aksi'      => 'kartu',
            'tombol'    => 'Perlu kartu disabilitas terverifikasi',
            'error'     => 'Tambahkan kartu disabilitas yang terverifikasi untuk memesan Priority Parking.',
        ];
    }

    /**
     * Slot satu lokasi & tipe dari tabel slot_parkir, urut lantai lalu kode.
     * terisi = status di database bukan 'tersedia' (terpesan atau terisi).
     */
    private function daftarSlot(int $idLokasi, string $tipe): array
    {
        return SlotParkir::where('lokasi_parkir_id', $idLokasi)
            ->tipe($tipe)
            ->get()
            ->map(fn (SlotParkir $s) => [
                'kode'     => $s->kode,
                'lantai'   => $s->lantai ?? 1,
                'konektor' => $s->konektor,
                'daya'     => $s->daya_kw,
                'terisi'   => $s->status !== SlotParkir::STATUS_TERSEDIA, // terpesan / terisi
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
}
