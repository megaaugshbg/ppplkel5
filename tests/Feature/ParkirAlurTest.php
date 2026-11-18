<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\SlotParkir;
use App\Models\User;
use Database\Seeders\LokasiParkirSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ParkirAlurTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(LokasiParkirSeeder::class);
    }

    private function slotGalaxy(string $kode): SlotParkir
    {
        return SlotParkir::whereHas('lokasi', fn ($q) => $q->where('nama', 'Parkir Mall Galaxy'))
            ->where('kode', $kode)
            ->firstOrFail();
    }

    private function pesan(User $user, string $kode = 'W-01'): Booking
    {
        $this->actingAs($user)->post('/booking', [
            'slot_parkir_id' => $this->slotGalaxy($kode)->id,
            'plat_nomor'     => 'L 1234 AB',
        ])->assertRedirect('/check-out');

        return Booking::firstOrFail();
    }

    public function test_register_menyimpan_user_dan_langsung_login(): void
    {
        $this->post('/register', [
            'nama' => 'Mega', 'email' => 'mega@example.com', 'no_hp' => '081234567891',
            'password' => 'rahasia123', 'password_confirmation' => 'rahasia123',
        ])->assertRedirect('/cari-parkir');

        $this->assertDatabaseHas('users', ['name' => 'Mega', 'email' => 'mega@example.com', 'no_hp' => '081234567891']);
        $this->assertAuthenticated();
    }

    public function test_register_menolak_email_yang_sudah_terdaftar(): void
    {
        User::factory()->create(['email' => 'mega@example.com']);

        $this->post('/register', [
            'nama' => 'Mega', 'email' => 'mega@example.com', 'no_hp' => '081234567891',
            'password' => 'rahasia123', 'password_confirmation' => 'rahasia123',
        ])->assertSessionHasErrors('email');
    }

    public function test_cari_parkir_mengurutkan_terdekat_dan_memilih_filter_woman_parking(): void
    {
        $this->get('/cari-parkir?tipe=wanita')
            ->assertOk()
            ->assertSeeInOrder(['Parkir Mall Galaxy', 'Parkir Kampus ITS', 'Parkir Pakuwon Mall'])
            ->assertSee('value="wanita" selected', false);
    }

    public function test_halaman_penyewa_membutuhkan_login(): void
    {
        $this->get('/check-out')->assertRedirect('/login');
        $this->get('/lokasi/1/slot')->assertRedirect('/login');
    }

    public function test_booking_tersimpan_dan_slot_yang_sama_tidak_bisa_dipesan_dua_kali(): void
    {
        $booking = $this->pesan(User::factory()->create());

        $this->assertSame('dipesan', $booking->status);
        $this->assertSame('L 1234 AB', $booking->plat_nomor);
        $this->assertMatchesRegularExpression('/^MKR-\d{3}$/', $booking->kode);

        $this->actingAs(User::factory()->create())->post('/booking', [
            'slot_parkir_id' => $booking->slot_parkir_id,
            'plat_nomor'     => 'B 9999 XY',
        ])->assertSessionHasErrors('slot_parkir_id');

        $this->assertSame(1, Booking::count());
    }

    public function test_alur_check_in_check_out_dan_pembayaran(): void
    {
        $user    = User::factory()->create();
        $booking = $this->pesan($user);

        // belum check-in: check-out ditolak
        $this->postJson('/check-out', ['kode' => $booking->kode])
            ->assertStatus(422)
            ->assertJson(['pesan' => 'Anda belum melakukan check-in, sehingga check-out tidak dapat dilakukan.']);

        $this->post("/booking/{$booking->kode}/check-in")->assertRedirect('/check-out');
        $this->assertSame('parkir', $booking->fresh()->status);

        // 90 menit dibulatkan ke 2 jam x Rp 5.000
        $this->travel(90)->minutes();
        $this->postJson('/check-out', ['kode' => $booking->kode])
            ->assertOk()
            ->assertJson(['durasi' => 2, 'total' => 10000]);
        $this->assertSame('menunggu_pembayaran', $booking->fresh()->status);

        // check-out kedua ditolak
        $this->postJson('/check-out', ['kode' => $booking->kode])->assertStatus(422);

        $this->get("/pembayaran/{$booking->kode}")->assertOk()->assertSee('Belum dibayar');

        $this->postJson("/pembayaran/{$booking->kode}", ['metode' => 'QRIS'])->assertOk();
        $this->assertDatabaseHas('pembayaran', ['booking_id' => $booking->id, 'metode' => 'QRIS', 'jumlah' => 10000]);
        $this->assertSame('lunas', $booking->fresh()->status);

        // bayar kedua kali ditolak
        $this->postJson("/pembayaran/{$booking->kode}", ['metode' => 'QRIS'])->assertStatus(422);
    }

    public function test_kode_tidak_valid_dan_booking_milik_orang_lain_ditolak(): void
    {
        $booking = $this->pesan(User::factory()->create());
        $lain    = User::factory()->create();

        $this->actingAs($lain)->postJson('/check-out', ['kode' => 'abc'])->assertStatus(422);
        $this->actingAs($lain)->postJson('/check-out', ['kode' => $booking->kode])->assertStatus(404);
        $this->actingAs($lain)->get("/pembayaran/{$booking->kode}")->assertNotFound();
    }
}
