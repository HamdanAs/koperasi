# Lingkungan Pengujian Black-Box

## Prasyarat

| No | Prasyarat | Cara verifikasi |
|----|-----------|-----------------|
| 1 | Aplikasi Laravel berjalan di browser | Buka URL aplikasi, halaman login tampil |
| 2 | Database ter-seed dengan akun default | Jalankan `php artisan migrate:fresh --seed` |
| 3 | Browser modern (Chrome/Firefox/Edge) | Versi terbaru, cache bersih jika perlu |
| 4 | Resolusi layar ≥ 1280×720 | Sidebar dan tabel terbaca penuh |

## Akun Uji Administratif

Akun berikut disediakan oleh `DatabaseSeeder` dengan password **`admin`**:

| Role | Nama | Username | Password | Keterangan |
|------|------|----------|----------|------------|
| Manajer | Manajer | `manajer` | `admin` | Akun penuh operasional manajemen |
| Teller | Teller | `teller` | `admin` | Akun operasional teller |
| Kolektor | Collector | `kolektor` | `admin` | Akun operasional penagihan |

> **Catatan keamanan:** Password `admin` hanya untuk lingkungan pengujian/development. Jangan gunakan di produksi.

## URL Penting

| Halaman | URL | Route name |
|---------|-----|------------|
| Login | `/login` | `login` |
| Dashboard | `/dashboard` | `home` |
| Karyawan | `/karyawan` | `user.index` |
| Nasabah | `/nasabah` | `customer.index` |
| Pinjaman | `/transaksi/pinjaman` | `transaction.loan.index` |
| Pembayaran Pinjaman | `/transaksi/pembayaran` | `transaction.installment.index` |
| Simpanan | `/transaksi/simpanan` | `transaction.deposit.index` |
| Penarikan | `/transaksi/penarikan` | `transaction.withdrawal.index` |
| Nasabah Bermasalah | `/kolektor/nasabah-bermasalah` | `collection.visit.index` |
| Penarikan Jaminan | `/kolektor/penarikan-jaminan` | `collection.foreclosure.index` |
| Pengaturan | `/pengaturan` | `profile.show` |

## Data Uji Contoh (Opsional)

Gunakan data fiktif konsisten agar skenario dapat diulang:

| Entitas | Contoh nilai |
|---------|--------------|
| Nasabah baru | Nama: `Budi Santoso`, NIK unik, alamat valid |
| Transaksi simpanan | Nominal: `500000`, nasabah aktif |
| Transaksi pinjaman | Plafon sesuai form, nasabah aktif |
| Kunjungan kolektor | Nasabah bermasalah yang sudah ada |

## Reset Data

Jika pengujian membutuhkan kondisi awal bersih:

1. Login sebagai role manapun.
2. Menu profil → **Reset Data** (konfirmasi SweetAlert).
3. Atau jalankan perintah: `php artisan migrate:fresh --seed`.

Setelah reset, semua akun uji kembali tersedia dengan password `admin`.

## Perangkat Uji

| Aspek | Rekomendasi |
|-------|-------------|
| Browser | Chrome / Firefox / Edge (satu browser utama + satu alternatif untuk regresi) |
| Mode | Normal (bukan incognito wajib, kecuali uji isolasi session) |
| Jaringan | Lokal (Laragon/XAMPP) atau staging |
