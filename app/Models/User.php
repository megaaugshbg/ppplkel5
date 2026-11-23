<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    // Nilai kolom jenis_kelamin.
    public const JK_LAKI      = 'laki-laki';
    public const JK_PEREMPUAN = 'perempuan';
    public const JENIS_KELAMIN = [self::JK_LAKI => 'Laki-laki', self::JK_PEREMPUAN => 'Perempuan'];

    // Nilai kolom status_kartu_disabilitas.
    public const KARTU_TERVERIFIKASI = 'terverifikasi';

    // Nilai kolom status_verifikasi_sim (verifikasi SIM untuk Woman Parking).
    public const SIM_TERVERIFIKASI = 'terverifikasi';

    /**
     * Format nomor kartu disabilitas (SIMULASI, belum mengikuti format resmi):
     * 8-20 huruf, angka, atau tanda hubung.
     */
    public const FORMAT_KARTU_DISABILITAS = '/^[A-Za-z0-9-]{8,20}$/';

    protected $fillable = [
        'name',
        'email',
        'no_hp',
        'alamat',
        'nik',
        'role',
        'password',
        'status_verif',
        'jenis_kelamin',
        'nomor_kartu_disabilitas',
        'status_kartu_disabilitas',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'status_verif'      => 'boolean',
            'sim_diverifikasi_at' => 'datetime',
        ];
    }

    /**
     * Setelah SIM terverifikasi, jenis_kelamin terkunci: perubahan lewat jalur
     * mana pun (update massal, ->save()) dibatalkan. Satu-satunya jalur
     * penulisan adalah terapkanVerifikasiSim().
     */
    protected static function booted(): void
    {
        static::updating(function (User $user) {
            if (! $user->izinkanUbahJk
                && $user->getOriginal('status_verifikasi_sim') === self::SIM_TERVERIFIKASI
                && $user->isDirty('jenis_kelamin')) {
                $user->jenis_kelamin = $user->getOriginal('jenis_kelamin');
            }
        });
    }

    /** Penanda internal: hanya diset oleh terapkanVerifikasiSim(). */
    private bool $izinkanUbahJk = false;

    public function simTerverifikasi(): bool
    {
        return $this->status_verifikasi_sim === self::SIM_TERVERIFIKASI;
    }

    /**
     * Simpan hasil verifikasi SIM (hanya jenis kelamin yang terbaca). Bila sudah
     * terverifikasi, jenis_kelamin tidak berubah; hanya waktu verifikasi yang
     * diperbarui. Mengembalikan false bila hasil berbeda dari yang tersimpan.
     */
    public function terapkanVerifikasiSim(string $jenisKelamin): bool
    {
        if ($this->simTerverifikasi() && $this->jenis_kelamin !== $jenisKelamin) {
            return false;
        }

        $this->forceFill([
            'jenis_kelamin'         => $jenisKelamin, // sama dengan nilai lama bila verifikasi ulang
            'status_verifikasi_sim' => self::SIM_TERVERIFIKASI,
            'sim_diverifikasi_at'   => now(),
        ]);

        $this->izinkanUbahJk = true;
        $this->save();
        $this->izinkanUbahJk = false;

        return true;
    }

    /** Syarat Woman Parking: SIM terverifikasi DAN jenis kelamin (dari SIM) perempuan. */
    public function perempuan(): bool
    {
        return $this->simTerverifikasi() && $this->jenis_kelamin === self::JK_PEREMPUAN;
    }

    /** Syarat Priority Parking: kartu disabilitas terverifikasi. */
    public function kartuDisabilitasTerverifikasi(): bool
    {
        return $this->nomor_kartu_disabilitas !== null
            && $this->status_kartu_disabilitas === self::KARTU_TERVERIFIKASI;
    }
}
