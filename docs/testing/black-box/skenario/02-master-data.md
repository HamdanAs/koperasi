# Skenario 02 — Data Master

Pengujian UI dan alur bisnis modul **Karyawan** dan **Nasabah**.

**Login disarankan:** Manajer atau Teller (sesuai matriks hak akses).

---

## A. Modul Nasabah (`/nasabah`)

### BB-MASTER-01: Menampilkan daftar nasabah

| Field | Nilai |
|-------|-------|
| **Langkah** | Login → sidebar **Nasabah** |
| **Harapan** | Halaman daftar tampil dengan tabel DataTables, kolom data terbaca |
| **Verifikasi UI** | Judul halaman, tombol tambah (jika ada), pagination/search berfungsi |

### BB-MASTER-02: Menambah nasabah baru

| Field | Nilai |
|-------|-------|
| **Langkah** | Klik tambah → isi form wajib → submit |
| **Harapan** | Notifikasi sukses, nasabah muncul di daftar |
| **Verifikasi UI** | Toast SweetAlert sukses, data baru terlihat setelah refresh |

### BB-MASTER-03: Validasi form nasabah kosong

| Field | Nilai |
|-------|-------|
| **Langkah** | Buka form tambah → submit tanpa mengisi |
| **Harapan** | Validasi error tampil per field wajib |
| **Verifikasi UI** | Tidak redirect ke daftar, pesan error jelas |

### BB-MASTER-04: Melihat detail nasabah

| Field | Nilai |
|-------|-------|
| **Langkah** | Dari daftar → klik **Detail** pada satu baris |
| **Harapan** | Halaman detail menampilkan data lengkap nasabah |
| **Verifikasi UI** | Data konsisten dengan baris yang dipilih |

### BB-MASTER-05: Mengedit data nasabah

| Field | Nilai |
|-------|-------|
| **Langkah** | Detail/Edit → ubah field (mis. alamat) → simpan |
| **Harapan** | Notifikasi sukses, perubahan tampil di daftar |
| **Verifikasi UI** | Nilai baru persisten setelah reload |

### BB-MASTER-06: Menghapus nasabah

| Field | Nilai |
|-------|-------|
| **Langkah** | Klik **Hapus** → konfirmasi SweetAlert |
| **Harapan** | Nasabah hilang dari daftar aktif |
| **Verifikasi UI** | Konfirmasi dialog muncul sebelum hapus |

### BB-MASTER-07: Cetak laporan nasabah

| Field | Nilai |
|-------|-------|
| **Langkah** | Gunakan fitur cetak dari halaman nasabah |
| **Harapan** | File PDF terunduh atau preview tampil |
| **Verifikasi UI** | Nama file PDF sesuai format tanggal |

---

## B. Modul Karyawan (`/karyawan`)

### BB-MASTER-08: Menampilkan daftar karyawan

| Field | Nilai |
|-------|-------|
| **Langkah** | Sidebar **Karyawan** |
| **Harapan** | Tabel karyawan dengan kolom role (MANAGER/TELLER/COLLECTOR) |
| **Verifikasi UI** | Tiga akun seed tampil |

### BB-MASTER-09: Menambah karyawan Teller

| Field | Nilai |
|-------|-------|
| **Input** | Data lengkap, role: Teller |
| **Harapan** | Karyawan baru tersimpan, muncul di daftar |
| **Verifikasi UI** | Role tampil `TELLER` |

### BB-MASTER-10: Menambah karyawan Collector

| Field | Nilai |
|-------|-------|
| **Input** | Data lengkap, role: Collector |
| **Harapan** | Karyawan baru tersimpan |
| **Verifikasi UI** | Role tampil `COLLECTOR` |

### BB-MASTER-11: Username duplikat ditolak

| Field | Nilai |
|-------|-------|
| **Input** | Username sudah dipakai (mis. `teller`) |
| **Harapan** | Form ditolak, pesan error validasi |
| **Verifikasi UI** | Field username menandai error |

### BB-MASTER-12: Edit karyawan (bukan Manajer)

| Field | Nilai |
|-------|-------|
| **Langkah** | Edit karyawan Teller/Collector → simpan |
| **Harapan** | Perubahan tersimpan |
| **Verifikasi UI** | Field role readonly/disabled saat edit |

### BB-MASTER-13: Hapus karyawan (bukan Manajer)

| Field | Nilai |
|-------|-------|
| **Langkah** | Hapus karyawan non-manajer dengan konfirmasi |
| **Harapan** | Data terhapus dari daftar |
| **Verifikasi UI** | SweetAlert konfirmasi sebelum hapus |

---

## C. Alur Bisnis Master Data

### BB-MASTER-14: Alur end-to-end nasabah → transaksi

| Field | Nilai |
|-------|-------|
| **Langkah** | 1. Tambah nasabah<br>2. Buka modul Simpanan<br>3. Pilih nasabah baru di form transaksi |
| **Harapan** | Nasabah baru dapat dipilih untuk transaksi |
| **Verifikasi UI** | Dropdown/select nasabah berisi entri baru |

### BB-MASTER-15: Konsistensi UI antar role

| Field | Nilai |
|-------|-------|
| **Langkah** | Ulangi BB-MASTER-01 dan BB-MASTER-08 untuk Manajer, Teller, Kolektor |
| **Harapan** | Daftar dapat diakses; catat perbedaan tombol aksi per role |
| **Verifikasi UI** | Bandingkan dengan matriks hak akses |
