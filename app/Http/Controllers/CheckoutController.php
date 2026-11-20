<?php

namespace App\Http\Controllers;

use App\Models\LokasiParkir;
use App\Models\SlotParkir;
use App\Support\PesananSesi;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

// Use case Lakukan Check-out (3.6.1)
class CheckoutController extends Controller
{
    /** Data struk check-out terakhir (pesanan aktif dikosongkan setelah check-out). */
    public const KUNCI_STRUK = 'struk_terakhir';

    /** Format barcode = kode booking dari Pesan Slot, contoh MKR-001. */
    private const FORMAT_BARCODE = '/^MKR-\d{3}$/';

    private const PESAN_GAGAL = 'Gagal memproses check-out, silakan coba lagi.';
    private const PESAN_BELUM_CHECKIN = 'Anda belum melakukan check-in, sehingga check-out tidak dapat dilakukan.';

    // Halaman Booking Aktif
    public function index()
    {
        // Tanpa pesanan aktif: jangan buat data contoh (PesananSesi::ambil), tampilkan keadaan kosong.
        if (! PesananSesi::ada()) {
            return view('check-out', ['pesanan' => null, 'struk' => session(self::KUNCI_STRUK)]);
        }

        $pesanan = PesananSesi::ambil();
        if ($arah = $this->arahkanBilaBelumCheckIn($pesanan)) {
            return $arah;
        }

        return view('check-out', ['pesanan' => $pesanan, 'struk' => null]);
    }

    // Halaman Check-out (scan barcode)
    public function scan()
    {
        if (! PesananSesi::ada()) {
            return redirect()->route('check-out');
        }

        $pesanan = PesananSesi::ambil();
        if ($arah = $this->arahkanBilaBelumCheckIn($pesanan)) {
            return $arah;
        }

        return view('check-out-scan', compact('pesanan'));
    }

    // Proses scan: validasi barcode, catat waktu keluar, selesaikan booking, kembalikan slot
    public function proses(Request $request)
    {
        $kode = strtoupper(trim((string) $request->input('barcode', '')));
        $gagal = fn (string $pesan, bool $bisaUlang = false) => back()
            ->withInput(['barcode' => $kode])
            ->with('error', $pesan)
            ->with('bisa_ulang', $bisaUlang);

        // Alternate flow: koneksi terputus. Data booking & slot tidak berubah, bisa diulang.
        if ($request->boolean('koneksi_putus')) {
            return $gagal(self::PESAN_GAGAL, true);
        }

        // Alternate flow: barcode tidak valid
        if (! preg_match(self::FORMAT_BARCODE, $kode)) {
            return $gagal('Barcode tidak valid. Silakan lakukan pemindaian ulang.');
        }

        // Hanya booking milik pengguna ini (pesanan di session-nya sendiri) yang bisa di-check-out.
        $pesanan = PesananSesi::ada() ? PesananSesi::ambil() : null;
        if (! $pesanan || $pesanan['kode'] !== $kode) {
            if (($struk = session(self::KUNCI_STRUK)) && $struk['kode'] === $kode) {
                return $gagal('Booking ini sudah check-out.');
            }

            // Alternate flow: booking tidak ditemukan
            return $gagal('Booking tidak ditemukan. Silakan periksa kembali barcode yang digunakan.');
        }

        // Alternate flow: penyewa belum check-in
        if (! PesananSesi::sudahCheckIn($pesanan)) {
            return $gagal(self::PESAN_BELUM_CHECKIN);
        }

        $keluar = now('Asia/Jakarta');

        try {
            $idLokasi = DB::transaction(fn () => $this->kembalikanSlot($pesanan));
        } catch (Throwable $e) {
            report($e);

            return $gagal(self::PESAN_GAGAL, true);
        }

        // Slot ini tidak lagi dianggap terisi di denah sesi ini.
        if ($idLokasi) {
            session(['slot_terisi' => array_values(array_diff(
                session('slot_terisi', []),
                [$idLokasi . '-' . $pesanan['slot']]
            ))]);
        }

        // Biaya sudah dibayar di muka (Pembayaran, sebelum check in): check-out tidak memungut.
        // Durasi aktual hanya informasi; tidak ada pelunasan atau pengembalian.
        $durasiAktual = $this->durasiAktual($pesanan['waktu_checkin'] ?? null, $keluar);
        $durasiDibayar = (int) ($pesanan['durasi'] ?? 0);

        session([self::KUNCI_STRUK => array_merge($pesanan, [
            'waktu_keluar'  => $keluar->format('d-m-Y H:i'),
            'durasi_aktual' => $durasiAktual,
            'melebihi_jam'  => max(0, $durasiAktual - $durasiDibayar),
            'total_biaya'   => (int) ($pesanan['total'] ?? 0), // = jumlah yang sudah dibayar
            'status'        => PesananSesi::SELESAI,
        ])]);
        PesananSesi::kosongkan();

        return redirect()->route('check-out.berhasil');
    }

    // Halaman Check-out Berhasil
    public function berhasil()
    {
        return $this->tampilStruk('check-out-berhasil');
    }

    // Halaman Struk
    public function struk()
    {
        return $this->tampilStruk('check-out-struk');
    }

    private function tampilStruk(string $view)
    {
        $struk = session(self::KUNCI_STRUK);
        if (! $struk) {
            return redirect()->route('check-out');
        }

        return view($view, compact('struk'));
    }

    /** Check-out hanya untuk pesanan yang sudah check in; selain itu arahkan ke tahap yang sesuai. */
    private function arahkanBilaBelumCheckIn(array $pesanan): ?RedirectResponse
    {
        if (! PesananSesi::sudahBayar($pesanan)) {
            return redirect()->route('pembayaran')
                ->with('error', 'Selesaikan pembayaran dan check in terlebih dahulu sebelum check-out.');
        }

        if (! PesananSesi::sudahCheckIn($pesanan)) {
            return redirect()->route('check-in')->with('error', self::PESAN_BELUM_CHECKIN);
        }

        return null;
    }

    /** Durasi parkir dari waktu check in sampai keluar, dibulatkan ke atas per jam, minimal 1 jam. */
    private function durasiAktual(?string $waktuCheckIn, Carbon $keluar): int
    {
        try {
            $masuk = Carbon::createFromFormat('d-m-Y H:i', (string) $waktuCheckIn, 'Asia/Jakarta');
        } catch (Throwable) {
            return 1;
        }

        return max(1, (int) ceil(max(0, $masuk->diffInMinutes($keluar)) / 60));
    }

    /**
     * Kembalikan slot parkir pesanan ini menjadi tersedia.
     * Mengikuti pola CheckInController::perbaruiStatusSlot: data contoh dilewati dan
     * tidak ada data yang diubah bila lokasi tidak ditemukan.
     *
     * @return int|null id lokasi bila slot diperbarui
     */
    private function kembalikanSlot(array $pesanan): ?int
    {
        // data contoh tidak mengubah database bersama
        if (! empty($pesanan['contoh'])) {
            return null;
        }

        $lokasi = LokasiParkir::where('nama', $pesanan['lokasi'])
            ->where('alamat', $pesanan['alamat'])
            ->first();

        if (! $lokasi) {
            return null;
        }

        SlotParkir::where('lokasi_parkir_id', $lokasi->id)
            ->where('kode', $pesanan['slot'])
            ->update(['status' => SlotParkir::STATUS_TERSEDIA]);

        return $lokasi->id;
    }
}
