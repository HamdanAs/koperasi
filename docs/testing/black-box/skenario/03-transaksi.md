# Skenario 03 — Transaksi

Pengujian alur bisnis modul transaksi koperasi melalui antarmuka web.

**Precondition:** Minimal satu nasabah aktif tersedia di sistem.

---

## A. Pinjaman (`/transaksi/pinjaman`)

### BB-TRX-01: Menampilkan daftar pinjaman

| Field | Nilai |
|-------|-------|
| **Langkah** | Sidebar → Transaksi → Pinjaman |
| **Harapan** | Daftar pinjaman tampil dengan status masing-masing |
| **Verifikasi UI** | Kolom nominal, nasabah, status terbaca |

### BB-TRX-02: Membuat pinjaman baru

| Field | Nilai |
|-------|-------|
| **Input** | Nasabah valid, nominal/plafon sesuai form, field wajib terisi |
| **Harapan** | Pinjaman tercatat, notifikasi sukses |
| **Verifikasi UI** | Record baru muncul di daftar |

### BB-TRX-03: Validasi form pinjaman tidak lengkap

| Field | Nilai |
|-------|-------|
| **Langkah** | Submit form pinjaman dengan field wajib kosong |
| **Harapan** | Validasi error, data tidak tersimpan |
| **Verifikasi UI** | Pesan error per field |

### BB-TRX-04: Detail dan edit pinjaman

| Field | Nilai |
|-------|-------|
| **Langkah** | Buka detail → edit field yang diizinkan → simpan |
| **Harapan** | Perubahan tersimpan atau ditolak sesuai aturan form |
| **Verifikasi UI** | Konsistensi data setelah reload |

### BB-TRX-05: Cetak laporan pinjaman

| Field | Nilai |
|-------|-------|
| **Langkah** | Gunakan fitur cetak |
| **Harapan** | PDF terunduh dengan data pinjaman |
| **Verifikasi UI** | Tanda tangan/nama manajer tampil di PDF |

---

## B. Pembayaran Pinjaman (`/transaksi/pembayaran`)

### BB-TRX-06: Menampilkan daftar pembayaran

| Field | Nilai |
|-------|-------|
| **Langkah** | Transaksi → Pembayaran Pinjaman |
| **Harapan** | Daftar pembayaran/angsuran tampil |
| **Verifikasi UI** | Relasi ke pinjaman terlihat (nama nasabah / ID pinjaman) |

### BB-TRX-07: Input pelacakan pembayaran pinjaman

| Field | Nilai |
|-------|-------|
| **Input** | Pinjaman aktif, nominal pembayaran valid |
| **Harapan** | Sistem menerima input, notifikasi sukses |
| **Verifikasi UI** | Status pinjaman/pembayaran berubah di UI |

### BB-TRX-08: Pembayaran melebihi sisa (jika applicable)

| Field | Nilai |
|-------|-------|
| **Input** | Nominal tidak wajar / melebihi plafon |
| **Harapan** | Sistem menolak atau memperingatkan |
| **Verifikasi UI** | Pesan error jelas |

### BB-TRX-09: Edit dan hapus pembayaran

| Field | Nilai |
|-------|-------|
| **Langkah** | Edit record → simpan; atau hapus dengan konfirmasi |
| **Harapan** | Operasi sesuai respons UI (sukses/error) |
| **Verifikasi UI** | SweetAlert konfirmasi pada hapus |

---

## C. Simpanan (`/transaksi/simpanan`)

### BB-TRX-10: Menampilkan daftar simpanan

| Field | Nilai |
|-------|-------|
| **Langkah** | Transaksi → Simpanan |
| **Harapan** | Riwayat simpanan tampil |
| **Verifikasi UI** | Nominal dan nasabah konsisten |

### BB-TRX-11: Input transaksi simpanan

| Field | Nilai |
|-------|-------|
| **Input** | Nasabah aktif, nominal valid (mis. 500000) |
| **Harapan** | Transaksi tersimpan, notifikasi sukses |
| **Verifikasi UI** | Data muncul di daftar |

### BB-TRX-12: Validasi nominal simpanan invalid

| Field | Nilai |
|-------|-------|
| **Input** | Nominal kosong, nol, atau di bawah minimum (jika ada) |
| **Harapan** | Form ditolak dengan pesan error |
| **Verifikasi UI** | Tidak ada record baru |

---

## D. Penarikan (`/transaksi/penarikan`)

### BB-TRX-13: Menampilkan daftar penarikan

| Field | Nilai |
|-------|-------|
| **Langkah** | Transaksi → Penarikan |
| **Harapan** | Daftar penarikan tampil |
| **Verifikasi UI** | Kolom data lengkap |

### BB-TRX-14: Input penarikan simpanan

| Field | Nilai |
|-------|-------|
| **Input** | Nasabah dengan saldo mencukupi, nominal valid |
| **Harapan** | Penarikan tercatat |
| **Verifikasi UI** | Notifikasi sukses |

### BB-TRX-15: Penarikan melebihi saldo (jika applicable)

| Field | Nilai |
|-------|-------|
| **Input** | Nominal lebih besar dari saldo |
| **Harapan** | Sistem menolak transaksi |
| **Verifikasi UI** | Pesan error bisnis tampil |

---

## E. Alur Bisnis End-to-End

### BB-TRX-16: Siklus simpanan → penarikan

| Langkah | Harapan |
|---------|---------|
| 1. Setor simpanan | Saldo/nominal tercatat |
| 2. Lihat detail nasabah/transaksi | Nominal konsisten |
| 3. Ajukan penarikan sebagian | Berhasil jika saldo cukup |
| **Verifikasi UI** | Urutan data logis di daftar |

### BB-TRX-17: Siklus pinjaman → pembayaran

| Langkah | Harapan |
|---------|---------|
| 1. Buat pinjaman | Status awal pinjaman tampil |
| 2. Input pembayaran angsuran | Status/pembayaran terupdate |
| 3. Lihat detail pinjaman | Riwayat pembayaran terlihat |
| **Verifikasi UI** | Status berubah sesuai input |

### BB-TRX-18: Konsistensi UI transaksi per role

| Field | Nilai |
|-------|-------|
| **Langkah** | Jalankan BB-TRX-02, BB-TRX-07, BB-TRX-11 sebagai Manajer, Teller, Kolektor |
| **Harapan** | Catat perbedaan akses/tombol per role |
| **Verifikasi UI** | Bandingkan dengan matriks hak akses |
