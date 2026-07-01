# Skenario 01 — Login dan Hak Akses

Modul prioritas utama. Memastikan autentikasi berfungsi dan integritas hak akses per role terjaga.

**Akun uji:** lihat [lingkungan-pengujian.md](../lingkungan-pengujian.md)

---

## A. Pengujian Login — Respons Input

### BB-LOGIN-01: Login Manajer dengan kredensial valid

| Field | Nilai |
|-------|-------|
| **Precondition** | Belum login, berada di `/login` |
| **Input** | Username: `manajer`, Password: `admin` |
| **Langkah** | 1. Isi username dan password<br>2. Klik **Masuk** |
| **Harapan** | Redirect ke `/dashboard`, greeting menampilkan `@manajer`, sidebar lengkap tampil |
| **Verifikasi UI** | URL berubah, tidak ada pesan error login |

### BB-LOGIN-02: Login Teller dengan kredensial valid

| Field | Nilai |
|-------|-------|
| **Precondition** | Belum login |
| **Input** | Username: `teller`, Password: `admin` |
| **Langkah** | Submit form login |
| **Harapan** | Masuk dashboard, profil navbar menampilkan nama Teller |
| **Verifikasi UI** | Session aktif, menu navigasi dapat diklik |

### BB-LOGIN-03: Login Kolektor dengan kredensial valid

| Field | Nilai |
|-------|-------|
| **Precondition** | Belum login |
| **Input** | Username: `kolektor`, Password: `admin` |
| **Langkah** | Submit form login |
| **Harapan** | Masuk dashboard, profil menampilkan Collector |
| **Verifikasi UI** | Menu modul Kolektor dapat diakses |

### BB-LOGIN-04: Login dengan password salah

| Field | Nilai |
|-------|-------|
| **Precondition** | Belum login |
| **Input** | Username: `manajer`, Password: `salah123` |
| **Langkah** | Submit form login |
| **Harapan** | Tetap di halaman login, pesan error autentikasi tampil |
| **Verifikasi UI** | Tidak redirect ke dashboard, field password/error state jelas |

### BB-LOGIN-05: Login dengan username tidak terdaftar

| Field | Nilai |
|-------|-------|
| **Precondition** | Belum login |
| **Input** | Username: `user_tidak_ada`, Password: `admin` |
| **Langkah** | Submit form login |
| **Harapan** | Login ditolak, pesan error tampil |
| **Verifikasi UI** | Tidak ada session aktif |

### BB-LOGIN-06: Login dengan field kosong

| Field | Nilai |
|-------|-------|
| **Precondition** | Belum login |
| **Input** | Username kosong dan/atau password kosong |
| **Langkah** | Klik **Masuk** tanpa mengisi |
| **Harapan** | Form tidak diproses atau validasi HTML/browser mencegah submit |
| **Verifikasi UI** | Tidak masuk dashboard |

---

## B. Pengujian Session dan Proteksi Halaman

### BB-ACCESS-01: Akses dashboard tanpa login

| Field | Nilai |
|-------|-------|
| **Precondition** | Belum login / session cleared |
| **Input** | Buka langsung `/dashboard` |
| **Harapan** | Redirect ke `/login` |
| **Verifikasi UI** | Halaman login tampil, tidak ada data dashboard |

### BB-ACCESS-02: Akses modul transaksi tanpa login

| Field | Nilai |
|-------|-------|
| **Precondition** | Guest / belum login |
| **Input** | Buka `/transaksi/pinjaman` |
| **Harapan** | Redirect ke login |
| **Verifikasi UI** | Tidak ada daftar pinjaman |

### BB-ACCESS-03: Logout dan proteksi session

| Field | Nilai |
|-------|-------|
| **Precondition** | Login sebagai `teller` |
| **Langkah** | 1. Klik menu profil → **Keluar** → konfirmasi<br>2. Tekan tombol Back browser atau akses `/dashboard` |
| **Harapan** | Session berakhir, akses halaman protected ditolak |
| **Verifikasi UI** | Kembali ke login atau redirect ke login |

---

## C. Pengujian Hak Akses per Role

Jalankan setelah login masing-masing role. Bandingkan dengan [matriks-hak-akses.md](../matriks-hak-akses.md).

### BB-ACCESS-04: Verifikasi identitas session Manajer

| Field | Nilai |
|-------|-------|
| **Role** | Manajer (`manajer` / `admin`) |
| **Langkah** | Login → buka dropdown profil navbar |
| **Harapan** | Nama "Manajer", username `@manajer`, nomor telepon tampil benar |
| **Verifikasi UI** | Buka `/pengaturan`, field role menampilkan `MANAGER` (readonly) |

### BB-ACCESS-05: Verifikasi identitas session Teller

| Field | Nilai |
|-------|-------|
| **Role** | Teller (`teller` / `admin`) |
| **Langkah** | Login → cek profil navbar dan `/pengaturan` |
| **Harapan** | Identitas Teller konsisten di seluruh UI |
| **Verifikasi UI** | Role `TELLER` di halaman pengaturan |

### BB-ACCESS-06: Verifikasi identitas session Kolektor

| Field | Nilai |
|-------|-------|
| **Role** | Kolektor (`kolektor` / `admin`) |
| **Langkah** | Login → cek profil navbar dan `/pengaturan` |
| **Harapan** | Identitas Collector konsisten |
| **Verifikasi UI** | Role `COLLECTOR` di halaman pengaturan |

### BB-ACCESS-07: Isolasi session antar role

| Field | Nilai |
|-------|-------|
| **Langkah** | 1. Login `manajer` → catat nama di navbar<br>2. Logout<br>3. Login `kolektor` |
| **Harapan** | Tidak ada sisa data/tampilan Manajer; profil menampilkan Kolektor |
| **Verifikasi UI** | Dashboard greeting sesuai username kolektor |

### BB-ACCESS-08: Proteksi akun Manajer di daftar Karyawan

| Field | Nilai |
|-------|-------|
| **Precondition** | Login sebagai Teller atau Kolektor |
| **Langkah** | Buka `/karyawan`, cari baris akun Manajer |
| **Harapan** | Kolom aksi menampilkan **Aksi Dilarang**, bukan tombol Edit/Hapus |
| **Verifikasi UI** | Tidak dapat menghapus/mengedit Manajer dari UI |

### BB-ACCESS-09: Pembatasan role saat tambah karyawan

| Field | Nilai |
|-------|-------|
| **Precondition** | Login sebagai role yang diizinkan menambah karyawan |
| **Langkah** | Buka `/karyawan/create`, periksa dropdown Role |
| **Harapan** | Hanya opsi Teller dan Collector; tidak ada opsi Manajer |
| **Verifikasi UI** | Dropdown sesuai aturan bisnis |

### BB-ACCESS-10: Uji akses langsung URL modul Kolektor (Teller)

| Field | Nilai |
|-------|-------|
| **Precondition** | Login sebagai `teller` |
| **Langkah** | Akses `/kolektor/nasabah-bermasalah` via sidebar dan URL langsung |
| **Harapan** | Halaman terbuka (authenticated); catat apakah sesuai kebijakan bisnis |
| **Verifikasi UI** | Tidak error 403/500; data tampil normal |

### BB-ACCESS-11: Uji akses langsung URL transaksi (Kolektor)

| Field | Nilai |
|-------|-------|
| **Precondition** | Login sebagai `kolektor` |
| **Langkah** | Akses `/transaksi/simpanan` |
| **Harapan** | Halaman terbuka untuk user terautentikasi; catat hasil vs kebijakan |
| **Verifikasi UI** | Form/daftar simpanan dapat dimuat |

---

## D. Checklist Cepat Login & RBAC

| ID | Skenario | Manajer | Teller | Kolektor |
|----|----------|:-------:|:------:|:--------:|
| BB-LOGIN-01 s/d 03 | Login valid | ☐ | ☐ | ☐ |
| BB-LOGIN-04 s/d 06 | Login invalid | ☐ | ☐ | ☐ |
| BB-ACCESS-01 s/d 03 | Proteksi session | ☐ | ☐ | ☐ |
| BB-ACCESS-04 s/d 07 | Identitas & isolasi | ☐ | ☐ | ☐ |
| BB-ACCESS-08 s/d 09 | Aturan karyawan | ☐ | ☐ | ☐ |
| BB-ACCESS-10 s/d 11 | Akses modul silang | — | ☐ | ☐ |

Isi hasil di [template-hasil-pengujian.md](../template-hasil-pengujian.md).
