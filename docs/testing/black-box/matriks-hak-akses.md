# Matriks Hak Akses per Role

Referensi ini mendefinisikan **harapan bisnis** untuk pengujian black-box. Penguji membandingkan perilaku UI dan respons sistem dengan matriks ini untuk mendeteksi kebocoran akses.

## Ringkasan Role

| Role | Peran operasional |
|------|-------------------|
| **Manajer** | Supervisi, manajemen karyawan, akses penuh operasional |
| **Teller** | Melayani transaksi nasabah di front office |
| **Kolektor** | Menangani nasabah bermasalah dan penarikan jaminan |

## Matriks Menu Sidebar

Semua role yang sudah login diharapkan dapat **melihat** menu berikut (verifikasi navigasi):

| Menu | Manajer | Teller | Kolektor |
|------|:-------:|:------:|:--------:|
| Dashboard | ✓ | ✓ | ✓ |
| Karyawan | ✓ | ✓ | ✓ |
| Nasabah | ✓ | ✓ | ✓ |
| Transaksi → Pinjaman | ✓ | ✓ | ✓ |
| Transaksi → Pembayaran Pinjaman | ✓ | ✓ | ✓ |
| Transaksi → Simpanan | ✓ | ✓ | ✓ |
| Transaksi → Penarikan | ✓ | ✓ | ✓ |
| Kolektor → Nasabah Bermasalah | ✓ | ✓ | ✓ |
| Kolektor → Penarikan Jaminan | ✓ | ✓ | ✓ |
| Pengaturan (profil) | ✓ | ✓ | ✓ |
| Keluar | ✓ | ✓ | ✓ |

## Matriks Operasi Khusus

Aturan bisnis yang **harus** diverifikasi melalui UI:

| Operasi | Manajer | Teller | Kolektor | Catatan UI |
|---------|:-------:|:------:|:--------:|------------|
| Login dengan kredensial valid | ✓ | ✓ | ✓ | Redirect ke `/dashboard` |
| Login kredensial salah | ✗ | ✗ | ✗ | Tetap di login, pesan error |
| Akses halaman tanpa login | ✗ | ✗ | ✗ | Redirect ke `/login` |
| Edit/hapus akun Manajer di daftar Karyawan | ✗ | ✗ | ✗ | Badge "Aksi Dilarang" |
| Tambah karyawan role Teller/Collector | ✓ | ? | ? | Form hanya menawarkan Teller & Collector |
| Tambah karyawan role Manajer via form | ✗ | ✗ | ✗ | Opsi Manajer tidak ada di dropdown |
| Reset seluruh data sistem | ✓* | ✓* | ✓* | Menu tersedia; verifikasi dampak bisnis |

\* Reset data adalah operasi destruktif — uji hanya di lingkungan development.

## Matriks Respons Autentikasi

| Kondisi input | Respons UI yang diharapkan |
|---------------|----------------------------|
| Username kosong | Validasi form / pesan wajib diisi |
| Password kosong | Validasi form / pesan wajib diisi |
| Username salah | Pesan error autentikasi |
| Password salah | Pesan error autentikasi |
| Username & password benar | Masuk dashboard, nama user tampil |
| Akses `/dashboard` tanpa session | Redirect ke login |
| Logout | Session berakhir, tidak bisa akses halaman protected |

## Uji Kebocoran Akses (Access Leakage)

Checklist yang **wajib** diuji untuk setiap role:

| ID | Uji kebocoran | Metode |
|----|---------------|--------|
| AL-01 | Session role A tidak bisa dipakai setelah logout | Login → logout → back/refresh URL protected |
| AL-02 | URL protected tidak bisa diakses guest | Buka URL langsung tanpa login |
| AL-03 | Kredensial role B ditolak jika username/password salah | Kombinasi invalid |
| AL-04 | Tidak ada data profil role lain tampil setelah ganti akun | Login bergantian, cek navbar profil |
| AL-05 | Operasi terlarang pada Manajer tidak tersedia di UI | Cek kolom aksi di `/karyawan` |

## Legenda

| Simbol | Arti |
|--------|------|
| ✓ | Diizinkan / harus berhasil |
| ✗ | Dilarang / harus ditolak sistem |
| ? | Perlu dikonfirmasi dengan stakeholder; catat hasil aktual |
