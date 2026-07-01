# Skenario 04 — Modul Kolektor

Pengujian UI dan alur bisnis penagihan: **Nasabah Bermasalah** dan **Penarikan Jaminan**.

**Login disarankan:** Kolektor (`kolektor` / `admin`), dengan verifikasi silang dari role lain.

---

## A. Nasabah Bermasalah (`/kolektor/nasabah-bermasalah`)

### BB-COL-01: Menampilkan daftar kunjungan

| Field | Nilai |
|-------|-------|
| **Langkah** | Sidebar → Kolektor → Nasabah Bermasalah |
| **Harapan** | Daftar record kunjungan/penagihan tampil |
| **Verifikasi UI** | Kolom kolektor, nasabah, tanggal terbaca |

### BB-COL-02: Menambah record nasabah bermasalah

| Field | Nilai |
|-------|-------|
| **Input** | Nasabah bermasalah, kolektor, tanggal/keterangan wajib |
| **Harapan** | Data tersimpan, notifikasi sukses |
| **Verifikasi UI** | Record baru di daftar |

### BB-COL-03: Validasi form kosong

| Field | Nilai |
|-------|-------|
| **Langkah** | Submit form tambah tanpa mengisi field wajib |
| **Harapan** | Validasi error tampil |
| **Verifikasi UI** | Tidak redirect sukses |

### BB-COL-04: Detail kunjungan kolektor

| Field | Nilai |
|-------|-------|
| **Langkah** | Klik detail salah satu record |
| **Harapan** | Informasi lengkap kunjungan dan nasabah |
| **Verifikasi UI** | Label "Kolektor" menampilkan nama benar |

### BB-COL-05: Edit record kunjungan

| Field | Nilai |
|-------|-------|
| **Langkah** | Edit → ubah keterangan/status → simpan |
| **Harapan** | Perubahan persisten |
| **Verifikasi UI** | Notifikasi sukses |

### BB-COL-06: Hapus record kunjungan

| Field | Nilai |
|-------|-------|
| **Langkah** | Hapus dengan konfirmasi SweetAlert |
| **Harapan** | Record hilang dari daftar |
| **Verifikasi UI** | Dialog konfirmasi muncul |

### BB-COL-07: Cetak laporan kolektor

| Field | Nilai |
|-------|-------|
| **Langkah** | Fitur cetak laporan nasabah bermasalah |
| **Harapan** | PDF terunduh dengan data kunjungan |
| **Verifikasi UI** | Header laporan dan nama manajer tampil |

---

## B. Penarikan Jaminan (`/kolektor/penarikan-jaminan`)

### BB-COL-08: Menampilkan daftar penarikan jaminan

| Field | Nilai |
|-------|-------|
| **Langkah** | Kolektor → Penarikan Jaminan |
| **Harapan** | Daftar foreclosure tampil |
| **Verifikasi UI** | Data nasabah dan jaminan terbaca |

### BB-COL-09: Menambah penarikan jaminan

| Field | Nilai |
|-------|-------|
| **Input** | Nasabah/pinjaman eligible, data jaminan lengkap |
| **Harapan** | Record tersimpan |
| **Verifikasi UI** | Notifikasi sukses |

### BB-COL-10: Validasi form penarikan jaminan

| Field | Nilai |
|-------|-------|
| **Langkah** | Submit dengan field wajib kosong |
| **Harapan** | Validasi error |
| **Verifikasi UI** | Form tidak lolos |

### BB-COL-11: Detail dan edit penarikan jaminan

| Field | Nilai |
|-------|-------|
| **Langkah** | Detail → edit → simpan |
| **Harapan** | Data terupdate |
| **Verifikasi UI** | Konsisten setelah reload |

### BB-COL-12: Cetak laporan penarikan jaminan

| Field | Nilai |
|-------|-------|
| **Langkah** | Gunakan fitur cetak |
| **Harapan** | PDF valid |
| **Verifikasi UI** | Format landscape/portrait sesuai template |

---

## C. Alur Bisnis Kolektor

### BB-COL-13: Alur pinjaman bermasalah → kunjungan → penarikan jaminan

| Langkah | Harapan |
|---------|---------|
| 1. Identifikasi nasabah/pinjaman bermasalah | Data tersedia di modul terkait |
| 2. Catat kunjungan kolektor | Record kunjungan tersimpan |
| 3. Ajukan penarikan jaminan | Record foreclosure tersimpan |
| **Verifikasi UI** | Alur logis antar modul |

### BB-COL-14: Dropdown kolektor hanya menampilkan user collector

| Field | Nilai |
|-------|-------|
| **Langkah** | Form tambah/edit kunjungan → field Kolektor |
| **Harapan** | Opsi hanya akun ber-role collector |
| **Verifikasi UI** | Tidak ada teller/manajer di dropdown |

### BB-COL-15: Akses modul kolektor dari role Teller/Manajer

| Field | Nilai |
|-------|-------|
| **Langkah** | Login Teller → akses modul Kolektor; ulangi untuk Manajer |
| **Harapan** | Catat apakah akses diizinkan sesuai kebijakan |
| **Verifikasi UI** | Tidak error fatal; bandingkan matriks hak akses |
