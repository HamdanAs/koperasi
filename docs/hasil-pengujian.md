# Hasil Pengujian Aplikasi Koperasi Swamitra

Dokumen ini berisi contoh hasil pengujian secara acak untuk skenario White-Box, Black-Box, dan Gray-Box pada aplikasi berbasis Laravel.

## 1. White-Box Testing

### Fokus
- Logika perhitungan pada source code aplikasi
- Validasi aturan bisnis pada modul pinjaman dan simpanan

### Hasil Pengujian

| No | Skenario | Area Uji | Hasil | Status | Catatan |
|---|---|---|---|---|---|
| 1 | Pengujian fungsi kalkulasi total pinjaman | app/Models/Loan.php | Total pinjaman dihitung sesuai rumus dasar | Lulus | Tidak ditemukan error logika pada nilai awal dan bunga |
| 2 | Pengujian fungsi perhitungan simpanan anggota | app/Models/Deposit.php | Nilai simpanan tersimpan sesuai input transaksi | Lulus | Hasil perhitungan konsisten pada beberapa data uji |
| 3 | Pengujian validasi batas nominal | app/Http/Controllers | Sistem menolak nominal di bawah batas minimum | Lulus | Pesan error tampil sesuai aturan |
| 4 | Pengujian alur status pinjaman | app/Traits/LoanTrait.php | Status pinjaman berubah sesuai proses pembayaran | Berhasil dengan catatan | Ada sedikit perbedaan pada tampilan status setelah refresh |

## 2. Black-Box Testing

### Fokus
- Fungsionalitas antarmuka web tanpa melihat kode backend
- Modul login, manajemen anggota, simpanan, dan pinjaman

### Hasil Pengujian

| No | Skenario | Modul | Hasil | Status | Catatan |
|---|---|---|---|---|---|
| 1 | Login menggunakan akun teller dengan password admin | Login | Berhasil masuk ke dashboard | Lulus | Session berhasil dibuat |
| 2 | Login menggunakan akun manajer dengan password admin | Login | Berhasil masuk ke dashboard | Lulus | Akses menu manajemen terlihat sesuai role |
| 3 | Login menggunakan akun kolektor dengan password admin | Login | Berhasil masuk ke dashboard | Lulus | Akses terbatas sesuai role kolektor |
| 4 | Menambah anggota baru melalui halaman web | Manajemen Anggota | Data anggota berhasil ditambahkan | Lulus | Data tampil pada daftar anggota |
| 5 | Menginput pelacakan pembayaran pinjaman | Pelacakan Pembayaran | Sistem menerima input dan menampilkan notifikasi sukses | Lulus | Status pembayaran berubah sesuai data input |
| 6 | Mengakses halaman dengan kredensial salah | Login | Sistem menolak akses | Lulus | Pesan error tampil dengan benar |

## 3. Gray-Box Testing

### Fokus
- Integrasi antara form web dengan database MySQL
- Kesesuaian data antara antarmuka dan tabel basis data

### Hasil Pengujian

| No | Skenario | Integrasi | Hasil | Status | Catatan |
|---|---|---|---|---|---|
| 1 | Input transaksi simpanan melalui form web | Form web -> database | Data transaksi tersimpan ke tabel simpanan | Lulus | Nilai nominal sesuai input pengguna |
| 2 | Input transaksi pinjaman melalui aplikasi web | Form web -> database | Data pinjaman tersimpan dengan status awal yang benar | Lulus | Hubungan antar tabel berjalan sesuai skema |
| 3 | Pengecekan data anggota setelah input | Form web -> tabel anggota | Data anggota muncul pada database | Lulus | ID anggota dan nama tersimpan konsisten |
| 4 | Validasi relasi status pembayaran | Form web -> tabel pinjaman | Status pembayaran terhubung dengan record pinjaman | Lulus | Tidak ada data yang terputus relasinya |
| 5 | Pengujian setelah penghapusan data sementara | Antarmuka -> database | Data yang dihapus tidak muncul di daftar aktif | Lulus | Record terkait tetap aman di history sistem |

## Kesimpulan

Secara umum, hasil pengujian simulasi menunjukkan bahwa sistem aplikasi Koperasi Swamitra memiliki performa yang cukup baik pada aspek fungsional, logika internal, dan integrasi data. Beberapa area masih perlu diperhatikan untuk peningkatan pengalaman pengguna, terutama pada konsistensi tampilan status setelah proses update.
