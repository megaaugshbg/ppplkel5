<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use RuntimeException;

/**
 * Pesanan (booking) Penyewa. Dipakai lewat App\Support\PesananSesi, yang
 * menjaga bentuk array pesanan lama untuk Pembayaran, Check In, dan Check-out.
 *
 * Kolom waktu_* disimpan dalam UTC (tanpa cast datetime); konversi WIB
 * dilakukan eksplisit di PesananSesi.
 */
class Booking extends Model
{
    public const TERPESAN      = 'terpesan';
    public const SUDAH_DIBAYAR = 'sudah_dibayar';
    public const SUDAH_CHECKIN = 'sudah_checkin';
    public const SELESAI       = 'selesai';
    public const DIBATALKAN    = 'dibatalkan';

    // Batas waktu bayar sejak pesanan dibuat (menit). Lewat dari ini, pesanan dibatalkan otomatis.
    public const BATAS_BAYAR_MENIT = 5;

    /** Status pesanan yang masih berjalan (maks. satu per pengguna). */
    public const AKTIF = [self::TERPESAN, self::SUDAH_DIBAYAR, self::SUDAH_CHECKIN];

    /** Label status yang ditampilkan (sama dengan label lama di array pesanan). */
    public const LABEL = [
        self::TERPESAN      => 'Terpesan',
        self::SUDAH_DIBAYAR => 'Sudah dibayar',
        self::SUDAH_CHECKIN => 'Sudah check in',
        self::SELESAI       => 'Selesai',
        self::DIBATALKAN    => 'Dibatalkan',
    ];

    /** Kode booking: "MKR-" + 6 digit (= barcode check-out). */
    public const FORMAT_KODE = '/^MKR-\d{6}$/';

    protected $table = 'booking';

    protected $fillable = [
        'kode', 'user_id', 'lokasi_parkir_id', 'slot_parkir_id',
        'nama_lokasi', 'alamat_lokasi', 'label_tipe', 'kode_slot',
        'konektor', 'daya_kw', 'tarif', 'tarif_ev_kwh', 'status',
        'durasi', 'total', 'metode', 'no_transaksi', 'waktu_bayar',
        'waktu_checkin', 'waktu_keluar', 'durasi_aktual', 'melebihi_jam', 'total_biaya',
    ];

    protected function casts(): array
    {
        return [
            'daya_kw'       => 'integer',
            'tarif'         => 'integer',
            'tarif_ev_kwh'  => 'integer',
            'durasi'        => 'integer',
            'total'         => 'integer',
            'durasi_aktual' => 'integer',
            'melebihi_jam'  => 'integer',
            'total_biaya'   => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function lokasi(): BelongsTo
    {
        return $this->belongsTo(LokasiParkir::class, 'lokasi_parkir_id');
    }

    public function slot(): BelongsTo
    {
        return $this->belongsTo(SlotParkir::class, 'slot_parkir_id');
    }

    public function scopeAktif(Builder $query): Builder
    {
        return $query->whereIn('status', self::AKTIF);
    }

    /** Pesanan belum dibayar yang sudah lewat batas waktu bayar. */
    public function scopeLewatBatasBayar(Builder $query): Builder
    {
        return $query->where('status', self::TERPESAN)
            ->where('created_at', '<=', now()->subMinutes(self::BATAS_BAYAR_MENIT));
    }

    /** Waktu terakhir pesanan ini boleh dibayar (UTC). */
    public function batasBayar(): Carbon
    {
        return $this->created_at->copy()->addMinutes(self::BATAS_BAYAR_MENIT);
    }

    public function scopeMilik(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /** Status database dari label (mis. "Sudah dibayar" -> sudah_dibayar), atau null. */
    public static function statusDariLabel(string $label): ?string
    {
        return array_search($label, self::LABEL, true) ?: null;
    }

    /**
     * Kode booking unik "MKR-" + 6 digit acak (1 juta kombinasi; riwayat tersimpan
     * permanen sehingga 3 digit pasti bentrok). Dicoba ulang bila sudah dipakai;
     * kolom kode juga unique di database sebagai pengaman terakhir.
     */
    public static function buatKodeUnik(): string
    {
        for ($i = 0; $i < 10; $i++) {
            $kode = 'MKR-' . str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            if (! self::where('kode', $kode)->exists()) {
                return $kode;
            }
        }

        throw new RuntimeException('Gagal membuat kode booking unik.');
    }
}
