<?php

namespace App\Http\Controllers;

use App\Models\LokasiParkir;
use App\Models\SlotParkir;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class HomeController extends Controller
{
    private const KOTA_DEFAULT = 'Surabaya';

    /** Label pendek untuk badge & chip filter tipe slot. */
    public const LABEL_PENDEK = [
        SlotParkir::TIPE_REGULER   => 'Normal',
        SlotParkir::TIPE_WANITA    => 'Woman',
        SlotParkir::TIPE_PRIORITAS => 'Priority',
        SlotParkir::TIPE_EV        => 'EV Charging',
    ];

    public function index(Request $request)
    {
        // Tabel belum dibuat (migration belum dijalankan): tampilkan beranda kosong, bukan error.
        if (! Schema::hasTable('lokasi_parkir') || ! Schema::hasTable('slot_parkir')) {
            return view('home', $this->dataKosong());
        }

        $daftarKota = LokasiParkir::query()->select('kota')->distinct()->orderBy('kota')->pluck('kota');

        // 1) Posisi GPS dari browser -> simpan di session, lalu redirect agar koordinat tidak tertinggal di URL.
        if ($request->has(['lat', 'lng'])) {
            $this->simpanPosisi($request);

            return redirect()->route('home', $request->except(['lat', 'lng', 'gps_ulang']));
        }

        // 2) Pilihan kota manual -> simpan di session.
        if ($request->filled('kota')) {
            if ($daftarKota->contains($request->query('kota'))) {
                session(['beranda.kota' => $request->query('kota'), 'beranda.kota_manual' => true]);
            }

            return redirect()->route('home', $request->except('kota'));
        }

        $kota = $this->kotaAktif($daftarKota);
        $posisi = session('beranda.posisi'); // [lat, lng] atau null
        $tipe = in_array($request->query('tipe'), SlotParkir::SEMUA_TIPE, true) ? $request->query('tipe') : null;
        $urut = $posisi && $request->query('urut') === 'jarak' ? 'jarak' : null;
        $q = mb_substr(trim((string) $request->query('q', '')), 0, 100);

        $mal = $kota ? $this->daftarMal($kota, $tipe, $q, $posisi, $urut) : collect();

        return view('home', [
            'tabelSiap'  => true,
            'daftarKota' => $daftarKota,
            'kota'       => $kota,
            'mal'        => $mal,
            'tipe'       => $tipe,
            'urut'       => $urut,
            'q'          => $q,
            'adaPosisi'  => (bool) $posisi,
            'labelTipe'  => self::LABEL_PENDEK,
            // Tabel booking belum ada. Nanti isi dengan booking terakhir milik auth()->user().
            'riwayat'    => collect(),
        ]);
    }

    /** Kartu mal untuk kota terpilih, sudah difilter, diberi jarak, dan diurutkan. */
    private function daftarMal(string $kota, ?string $tipe, string $q, ?array $posisi, ?string $urut): Collection
    {
        $hasil = LokasiParkir::kota($kota)
            ->denganJumlahSlot()
            ->when($tipe, fn ($query) => $query->tersediaTipe($tipe))
            ->when($q !== '', function ($query) use ($q) {
                $like = '%' . addcslashes($q, '%_\\') . '%';
                $query->where(fn ($w) => $w->where('nama', 'like', $like)->orWhere('area', 'like', $like));
            })
            ->get()
            ->map(function (LokasiParkir $l) use ($tipe, $posisi) {
                $ringkasan = $l->ringkasan_slot;

                return [
                    'id'        => $l->id,
                    'nama'      => $l->nama,
                    'area'      => $l->area,
                    'foto'      => $l->foto_url,
                    'buka'      => $l->sedang_buka,
                    'jam'       => $l->jam_operasional,
                    'jarak'     => $posisi && $l->latitude !== null && $l->longitude !== null
                        ? self::haversineKm($posisi[0], $posisi[1], $l->latitude, $l->longitude)
                        : null,
                    'kosong'    => $tipe
                        ? $ringkasan[$tipe]['tersedia']
                        : array_sum(array_column($ringkasan, 'tersedia')),
                    'tipeKosong' => array_keys(array_filter($ringkasan, fn ($r) => $r['tersedia'] > 0)),
                    'url'       => route('lokasi.tipe', $l->id),
                ];
            });

        return $urut === 'jarak'
            ? $hasil->sortBy(fn ($m) => $m['jarak'] ?? INF)->values()
            : $hasil->sortBy(fn ($m) => ($m['buka'] ? '0' : '1') . mb_strtolower($m['nama']))->values();
    }

    /** Kota dari session jika masih ada di data; jika tidak, Surabaya, lalu kota pertama. */
    private function kotaAktif(Collection $daftarKota): ?string
    {
        $pilihan = session('beranda.kota');
        if ($pilihan && $daftarKota->contains($pilihan)) {
            return $pilihan;
        }

        return $daftarKota->contains(self::KOTA_DEFAULT) ? self::KOTA_DEFAULT : $daftarKota->first();
    }

    /** Simpan posisi (dibulatkan ~100 m) dan, bila kota belum dipilih manual, pakai kota terdekat. */
    private function simpanPosisi(Request $request): void
    {
        $lat = filter_var($request->query('lat'), FILTER_VALIDATE_FLOAT);
        $lng = filter_var($request->query('lng'), FILTER_VALIDATE_FLOAT);
        if ($lat === false || $lng === false || abs($lat) > 90 || abs($lng) > 180) {
            return;
        }

        session(['beranda.posisi' => [round($lat, 3), round($lng, 3)]]);

        // Tombol "Gunakan lokasi saya": kota kembali mengikuti GPS.
        if ($request->boolean('gps_ulang')) {
            session()->forget('beranda.kota_manual');
        }

        if (! session('beranda.kota_manual') && ($kota = $this->kotaTerdekat($lat, $lng))) {
            session(['beranda.kota' => $kota]);
        }
    }

    /** Kota milik lahan parkir terdekat dari posisi pengguna. */
    private function kotaTerdekat(float $lat, float $lng): ?string
    {
        return LokasiParkir::query()
            ->whereNotNull('latitude')->whereNotNull('longitude')
            ->get(['kota', 'latitude', 'longitude'])
            ->sortBy(fn ($l) => self::haversineKm($lat, $lng, $l->latitude, $l->longitude))
            ->first()?->kota;
    }

    /** Jarak dua titik di permukaan bumi (km), rumus haversine. */
    private static function haversineKm(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $r = 6371.0;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return $r * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }

    private function dataKosong(): array
    {
        return [
            'tabelSiap' => false, 'daftarKota' => collect(), 'kota' => null, 'mal' => collect(),
            'tipe' => null, 'urut' => null, 'q' => '', 'adaPosisi' => false,
            'labelTipe' => self::LABEL_PENDEK, 'riwayat' => collect(),
        ];
    }
}
