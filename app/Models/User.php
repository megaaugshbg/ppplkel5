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
        ];
    }

    /** Syarat Woman Parking: jenis kelamin perempuan. */
    public function perempuan(): bool
    {
        return $this->jenis_kelamin === self::JK_PEREMPUAN;
    }

    /** Syarat Priority Parking: kartu disabilitas terverifikasi. */
    public function kartuDisabilitasTerverifikasi(): bool
    {
        return $this->nomor_kartu_disabilitas !== null
            && $this->status_kartu_disabilitas === self::KARTU_TERVERIFIKASI;
    }
}
