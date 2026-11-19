# Markir.In

Aplikasi web (dan mobile) untuk mencari, memesan, dan membayar slot parkir secara digital.
Proyek mata kuliah PPPL (ES234526), Kelompok 5, Departemen Sistem Informasi ITS.

## Cakupan (penting)
- Hanya ada SATU aktor: **Penyewa**. Tidak ada peran pemilik lahan; jangan buat fitur/halaman untuk pemilik lahan.
- Ketersediaan slot ditampilkan real-time.

## Tipe slot parkir
1. Normal Parking: slot reguler untuk semua pengguna.
2. Woman Parking: khusus wanita, dekat pintu masuk/lift, lebih lebar, dalam jangkauan CCTV.
3. Priority Parking: penyandang disabilitas, dengan verifikasi kartu disabilitas.
4. EV Charging Parking: slot dengan stasiun pengisian kendaraan listrik.

## Alur utama (urutan yang benar)
Registrasi + verifikasi akun -> Login -> Cari Lokasi Parkir -> Pilih Tipe Slot -> Cari Slot -> Pesan Slot
-> **Bayar (QRIS)** -> **Check-in** -> **Check-out (scan barcode)** -> Histori Booking.

- Pembayaran dilakukan SEBELUM check-in (bukan pelunasan saat check-out; tidak ada konsep down payment).
- Check-in: pengguna konfirmasi kedatangan di aplikasi; catat waktu masuk, status slot jadi "terisi".
- Check-out: scan barcode booking; validasi barcode, catat waktu keluar, tampilkan durasi dan total biaya.
- Barcode unik dibuat per booking.
- Histori booking menampilkan lokasi, jenis layanan, waktu, status, bukti pembayaran.

## Aturan per use case (ringkas)
- Registrasi: nama, email, nomor HP, password + konfirmasi. Email/HP harus unik. Akun baru berstatus "belum terverifikasi" sampai kode verifikasi benar dan belum kedaluwarsa. Ada "Kirim Ulang Kode".
- Login: email + password; akun harus "terverifikasi", jika belum arahkan ke halaman verifikasi.
- Cari Lokasi: urut jarak (jika GPS diizinkan), ada pencarian nama lokasi, lokasi di luar jam operasional ditolak ("Lokasi parkir sedang tutup").
- Pesan Slot: cek ulang ketersediaan saat menekan "Pesan"; jika sudah diambil pengguna lain tampilkan "Slot telah dipesan pengguna lain". Hindari double booking.
- Pesan error memakai teks Bahasa Indonesia seperti di dokumen CDR.

## Teknis
- Laravel 12, PHP 8.2, Vite (jalankan `npm run dev` bersama `php artisan serve`).
- Database: MySQL di Aiven (dipakai bersama satu tim). Koneksi butuh SSL: `MYSQL_ATTR_SSL_CA` di `.env` harus menunjuk ke `storage/ca.pem` di mesin masing-masing.
- Jangan pernah menjalankan `migrate:fresh`, `migrate:rollback`, atau perintah yang menghapus data (DB dipakai bersama).
- Jangan commit `.env`, `ca.pem`, atau password apa pun.
- Fitur yang sudah ada: registrasi dan login (AuthController). Tambahkan fitur baru tanpa merusak yang ada.

## Konvensi kerja
- `git pull` sebelum mulai, kerjakan di branch sendiri, commit kecil dan sering.
- Ikuti gaya kode dan struktur folder yang sudah ada.
- Bahasa UI dan pesan: Bahasa Indonesia.

## Dokumen acuan
Dokumen lengkap (CDR, use case, robustness/sequence diagram, storyboard) ada di Google Docs tim, tab "Critical Design Review (UTS)".
