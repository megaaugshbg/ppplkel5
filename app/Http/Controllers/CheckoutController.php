<?php

namespace App\Http\Controllers;

use App\Models\Booking;
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
    /** Format barcode = kode booking dari Pesan Slot ("MKR-" + 6 digit, contoh MKR-000123). */
    private const FORMAT_BARCODE = Booking::FORMAT_KODE;

    private const PESAN_GAGAL = 'Gagal memproses check-out, silakan coba lagi.';
    private const PESAN_BELUM_CHECKIN = 'Anda belum melakukan check-in, sehingga check-out tidak dapat dilakukan.';

    // Halaman Booking Aktif
    public function index()
    {
        // Tanpa pesanan aktif: tampilkan keadaan kosong (+ tautan struk terakhir dari database).
        if (! PesananSesi::ada()) {
            return view('check-out', ['pesanan' => null, 'struk' => PesananSesi::strukTerakhir()]);
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

        // Hanya booking milik pengguna yang login yang bisa di-check-out (cek user_id).
        $booking = Booking::where('kode', $kode)->where('user_id', $request->user()->id)->first();
        if (! $booking || ! in_array($booking->status, [...Booking::AKTIF, Booking::SELESAI], true)) {
            // Alternate flow: booking tidak ditemukan (termasuk milik orang lain / dibatalkan)
            return $gagal('Booking tidak ditemukan. Silakan periksa kembali barcode yang digunakan.');
        }
        if ($booking->status === Booking::SELESAI) {
            return $gagal('Booking ini sudah check-out.');
        }

        // Alternate flow: penyewa belum check-in
        if ($booking->status !== Booking::SUDAH_CHECKIN) {
            return $gagal(self::PESAN_BELUM_CHECKIN);
        }

        try {
            $hasil = DB::transaction(fn () => $this->selesaikan($booking->id));
        } catch (Throwable $e) {
            report($e);

            return $gagal(self::PESAN_GAGAL, true);
        }

        // Status berubah di antara pengecekan dan transaksi (mis. dua tab sekaligus)
        if ($hasil === Booking::SELESAI) {
            return $gagal('Booking ini sudah check-out.');
        }

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
        $struk = PesananSesi::strukTerakhir();
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
     * Selesaikan booking dalam SATU transaksi: kunci baris booking, cek ulang status,
     * catat waktu keluar, durasi, dan total biaya, ubah status menjadi selesai, lalu
     * kembalikan slot (lewat slot_parkir_id) menjadi tersedia.
     *
     * Biaya sudah dibayar di muka (Pembayaran, sebelum check in): check-out tidak memungut.
     * Durasi aktual hanya informasi; tidak ada pelunasan atau pengembalian.
     *
     * @return string|null Booking::SELESAI bila sudah selesai sebelumnya, null bila berhasil
     */
    private function selesaikan(int $idBooking): ?string
    {
        $booking = Booking::whereKey($idBooking)->lockForUpdate()->firstOrFail();
        if ($booking->status === Booking::SELESAI) {
            return Booking::SELESAI;
        }
        if ($booking->status !== Booking::SUDAH_CHECKIN) {
            throw new \RuntimeException('Booking belum check in.');
        }

        $keluar = now(PesananSesi::ZONA_WAKTU)->format(PesananSesi::FORMAT_WAKTU); // WIB
        $masuk = PesananSesi::keWib($booking->getRawOriginal('waktu_checkin'));
        $durasiAktual = $this->durasiAktual($masuk, Carbon::createFromFormat(PesananSesi::FORMAT_WAKTU, $keluar, PesananSesi::ZONA_WAKTU));

        $booking->update([
            'waktu_keluar'  => PesananSesi::keUtc($keluar), // disimpan UTC
            'durasi_aktual' => $durasiAktual,
            'melebihi_jam'  => max(0, $durasiAktual - (int) $booking->durasi),
            'total_biaya'   => (int) $booking->total, // = jumlah yang sudah dibayar
            'status'        => Booking::SELESAI,
        ]);

        SlotParkir::whereKey($booking->slot_parkir_id)->update(['status' => SlotParkir::STATUS_TERSEDIA]);

        return null;
    }
}
