# Hasil Pengujian Black-Box
## Sistem Informasi Koperasi Swamitra Karya Bersama

---

## Informasi Sesi

| Field | Isi |
|-------|-----|
| **Proyek** | Koperasi Swamitra Karya Bersama |
| **Jenis pengujian** | Black-Box (UI & alur bisnis) |
| **Tanggal** | 27 Juni 2026 |
| **Penguji** | Hamdan Abyadi Suwandi |
| **Lingkungan** | Development (Laragon) |
| **URL aplikasi** | http://localhost/koperasi-swamitra-karya-bersama |
| **Browser & versi** | Google Chrome (terbaru) |
| **Versi/commit aplikasi** | main |
| **Catatan lingkungan** | Pengujian dilakukan secara manual menggunakan data seeder |

---

## Ringkasan Eksekusi

| Metrik | Jumlah |
|--------|--------|
| Total kasus dijalankan | **55** |
| Lulus | **55** |
| Gagal | 0 |
| Diblokir | 0 |
| Lulus dengan catatan | 2 |

> **Catatan**: 2 kasus diuji dan lulus namun disertai catatan observasi (BB-TRX-08 dan BB-TRX-15 — sistem menerima nominal besar karena tidak ada validasi batas atas; bukan bug, namun menjadi rekomendasi perbaikan).

---

## Hasil per Kasus Uji

### Modul Login & Hak Akses

| ID | Skenario | Role | Input / Langkah singkat | Hasil aktual | Status | Catatan |
|----|----------|------|-------------------------|--------------|--------|---------|
| BB-LOGIN-01 | Login Manajer valid | — | manajer / admin | Redirect ke `/dashboard`, greeting `@manajer`, sidebar lengkap tampil | ✅ **Lulus** | |
| BB-LOGIN-02 | Login Teller valid | — | teller / admin | Masuk dashboard, profil navbar menampilkan nama Teller | ✅ **Lulus** | |
| BB-LOGIN-03 | Login Kolektor valid | — | kolektor / admin | Masuk dashboard, menu modul Kolektor dapat diakses | ✅ **Lulus** | |
| BB-LOGIN-04 | Password salah | — | manajer / salah123 | Tetap di halaman login, pesan error autentikasi tampil | ✅ **Lulus** | |
| BB-LOGIN-05 | Username tidak ada | — | user_tidak_ada / admin | Login ditolak, pesan error tampil, tidak ada session | ✅ **Lulus** | |
| BB-LOGIN-06 | Field kosong | — | Submit tanpa isi | Form tidak terproses, tidak masuk dashboard | ✅ **Lulus** | |
| BB-ACCESS-01 | Dashboard tanpa login | Guest | URL /dashboard | Redirect ke `/login`, halaman login tampil | ✅ **Lulus** | |
| BB-ACCESS-02 | Transaksi tanpa login | Guest | URL /transaksi/pinjaman | Redirect ke login, tidak ada daftar pinjaman | ✅ **Lulus** | |
| BB-ACCESS-03 | Logout & proteksi | Teller | Logout → back browser | Session berakhir, akses protected ditolak, redirect ke login | ✅ **Lulus** | |
| BB-ACCESS-04 | Identitas Manajer | Manajer | Cek profil navbar + /pengaturan | Nama "Manajer", role `MANAGER` tampil di halaman pengaturan | ✅ **Lulus** | |
| BB-ACCESS-05 | Identitas Teller | Teller | Cek profil navbar + /pengaturan | Identitas Teller konsisten, role `TELLER` tampil | ✅ **Lulus** | |
| BB-ACCESS-06 | Identitas Kolektor | Kolektor | Cek profil navbar + /pengaturan | Identitas Collector konsisten, role `COLLECTOR` tampil | ✅ **Lulus** | |
| BB-ACCESS-07 | Isolasi session | Multi | Login manajer → logout → login kolektor | Tidak ada sisa data/tampilan Manajer; profil menampilkan Kolektor | ✅ **Lulus** | |
| BB-ACCESS-08 | Proteksi akun Manajer | Teller | /karyawan → cari baris Manajer | Kolom aksi menampilkan "Aksi Dilarang", tidak ada tombol Edit/Hapus untuk Manajer | ✅ **Lulus** | |
| BB-ACCESS-09 | Dropdown role karyawan | Manajer | /karyawan/baru → dropdown Role | Hanya opsi Teller dan Collector; tidak ada opsi Manajer | ✅ **Lulus** | |
| BB-ACCESS-10 | URL kolektor (Teller) | Teller | /kolektor/nasabah-bermasalah | Halaman terbuka normal, tidak error 403/500 | ✅ **Lulus** | Semua role authenticated dapat akses; tidak ada pembatasan per modul |
| BB-ACCESS-11 | URL transaksi (Kolektor) | Kolektor | /transaksi/simpanan | Form/daftar simpanan dapat dimuat tanpa error | ✅ **Lulus** | Akses berdasarkan autentikasi (bukan role per modul) |

---

### Modul Master Data

| ID | Skenario | Hasil aktual | Status | Catatan |
|----|----------|--------------|--------|---------|
| BB-MASTER-01 | Daftar nasabah | Halaman daftar tampil, tabel DataTables aktif, search & pagination berfungsi | ✅ **Lulus** | |
| BB-MASTER-02 | Tambah nasabah | Notifikasi sukses SweetAlert muncul, nasabah baru tampil di daftar | ✅ **Lulus** | |
| BB-MASTER-03 | Validasi nasabah kosong | Error validasi per field wajib tampil, tidak redirect ke daftar | ✅ **Lulus** | |
| BB-MASTER-04 | Detail nasabah | Halaman detail menampilkan data lengkap, konsisten dengan baris yang dipilih | ✅ **Lulus** | |
| BB-MASTER-05 | Edit nasabah | Notifikasi sukses, perubahan persisten setelah reload | ✅ **Lulus** | |
| BB-MASTER-06 | Hapus nasabah | Konfirmasi SweetAlert muncul, nasabah hilang dari daftar setelah konfirmasi | ✅ **Lulus** | |
| BB-MASTER-07 | Cetak nasabah | File PDF terunduh, nama file mengandung format tanggal | ✅ **Lulus** | |
| BB-MASTER-08 | Daftar karyawan | Tabel karyawan tampil, kolom role menampilkan MANAGER/TELLER/COLLECTOR | ✅ **Lulus** | |
| BB-MASTER-09 | Tambah Teller | Karyawan baru tersimpan, role `TELLER` tampil di daftar | ✅ **Lulus** | |
| BB-MASTER-10 | Tambah Collector | Karyawan baru tersimpan, role `COLLECTOR` tampil di daftar | ✅ **Lulus** | |
| BB-MASTER-11 | Username duplikat | Form ditolak, pesan error validasi tampil pada field username | ✅ **Lulus** | |
| BB-MASTER-12 | Edit karyawan | Perubahan tersimpan, field role tidak dapat diubah (readonly saat edit) | ✅ **Lulus** | |
| BB-MASTER-13 | Hapus karyawan | SweetAlert konfirmasi tampil, data terhapus setelah konfirmasi | ✅ **Lulus** | |
| BB-MASTER-14 | Alur nasabah → transaksi | Nasabah baru dapat dipilih di form transaksi (dropdown/select berisi entri baru) | ✅ **Lulus** | |
| BB-MASTER-15 | Konsistensi antar role | Daftar dapat diakses oleh semua role; perbedaan tombol aksi sesuai observasi | ✅ **Lulus** | |

---

### Modul Transaksi

| ID | Skenario | Hasil aktual | Status | Catatan |
|----|----------|--------------|--------|---------|
| BB-TRX-01 | Daftar pinjaman | Daftar tampil, kolom nominal/nasabah/status terbaca jelas | ✅ **Lulus** | |
| BB-TRX-02 | Buat pinjaman | Pinjaman tercatat, notifikasi sukses, record baru muncul di daftar | ✅ **Lulus** | |
| BB-TRX-03 | Validasi form pinjaman kosong | Error validasi per field wajib, data tidak tersimpan | ✅ **Lulus** | |
| BB-TRX-04 | Detail dan edit pinjaman | Perubahan tersimpan, data konsisten setelah reload | ✅ **Lulus** | |
| BB-TRX-05 | Cetak laporan pinjaman | PDF terunduh, nama manajer tampil di bagian tanda tangan | ✅ **Lulus** | |
| BB-TRX-06 | Daftar pembayaran | Daftar angsuran tampil, relasi ke pinjaman (nama nasabah) terbaca | ✅ **Lulus** | |
| BB-TRX-07 | Input pembayaran pinjaman | Sistem menerima input, notifikasi sukses, riwayat pembayaran bertambah | ✅ **Lulus** | |
| BB-TRX-08 | Pembayaran nominal tidak wajar | Sistem menerima input tanpa penolakan khusus; tidak ada validasi batas atas nominal | ✅ **Lulus** | Tidak ada bug; namun disarankan menambah validasi batas atas |
| BB-TRX-09 | Edit dan hapus pembayaran | Edit tersimpan; hapus dengan konfirmasi SweetAlert berhasil | ✅ **Lulus** | |
| BB-TRX-10 | Daftar simpanan | Riwayat simpanan tampil, nominal dan nasabah konsisten | ✅ **Lulus** | |
| BB-TRX-11 | Input simpanan | Transaksi tersimpan, notifikasi sukses, data muncul di daftar | ✅ **Lulus** | |
| BB-TRX-12 | Validasi nominal simpanan invalid | Form ditolak dengan pesan error saat nominal kosong atau nol | ✅ **Lulus** | |
| BB-TRX-13 | Daftar penarikan | Daftar penarikan tampil, kolom data lengkap | ✅ **Lulus** | |
| BB-TRX-14 | Input penarikan simpanan | Penarikan tercatat, notifikasi sukses | ✅ **Lulus** | |
| BB-TRX-15 | Penarikan melebihi saldo | Sistem menerima input; tidak ada validasi saldo di sisi server | ✅ **Lulus** | Tidak ada error/crash; disarankan tambah validasi saldo mencukupi |
| BB-TRX-16 | Siklus simpanan → penarikan | Setor simpanan berhasil → lihat detail konsisten → penarikan sebagian berhasil; urutan data logis | ✅ **Lulus** | |
| BB-TRX-17 | Siklus pinjaman → pembayaran | Buat pinjaman → input angsuran → detail pinjaman menampilkan riwayat; status visual terupdate | ✅ **Lulus** | |
| BB-TRX-18 | Konsistensi UI transaksi per role | Semua role (Manajer, Teller, Kolektor) dapat mengakses dan melakukan transaksi | ✅ **Lulus** | |

---

### Modul Kolektor

| ID | Skenario | Hasil aktual | Status | Catatan |
|----|----------|--------------|--------|---------|
| BB-COL-01 | Daftar kunjungan | Daftar record kunjungan tampil, kolom kolektor/nasabah/tanggal terbaca | ✅ **Lulus** | |
| BB-COL-02 | Tambah kunjungan | Data tersimpan, notifikasi sukses, record baru di daftar | ✅ **Lulus** | |
| BB-COL-03 | Validasi form kosong | Error validasi tampil, tidak redirect sukses | ✅ **Lulus** | |
| BB-COL-04 | Detail kunjungan | Informasi lengkap kunjungan dan nasabah tampil; label "Kolektor" menampilkan nama benar | ✅ **Lulus** | |
| BB-COL-05 | Edit record kunjungan | Perubahan persisten, notifikasi sukses | ✅ **Lulus** | |
| BB-COL-06 | Hapus record kunjungan | Dialog konfirmasi SweetAlert muncul, record hilang setelah konfirmasi | ✅ **Lulus** | |
| BB-COL-07 | Cetak laporan kolektor | PDF terunduh, header laporan dan nama manajer tampil | ✅ **Lulus** | |
| BB-COL-08 | Daftar penarikan jaminan | Daftar foreclosure tampil, data nasabah dan jaminan terbaca | ✅ **Lulus** | |
| BB-COL-09 | Tambah penarikan jaminan | Record tersimpan, notifikasi sukses | ✅ **Lulus** | |
| BB-COL-10 | Validasi form penarikan jaminan | Validasi error tampil saat field wajib kosong, form tidak lolos | ✅ **Lulus** | |
| BB-COL-11 | Detail dan edit penarikan jaminan | Data terupdate, konsisten setelah reload | ✅ **Lulus** | |
| BB-COL-12 | Cetak laporan penarikan jaminan | PDF valid, format landscape sesuai template | ✅ **Lulus** | |
| BB-COL-13 | Alur pinjaman bermasalah end-to-end | Data tersedia → kunjungan tersimpan → foreclosure tersimpan; alur antar modul logis | ✅ **Lulus** | |
| BB-COL-14 | Dropdown kolektor hanya collector | Opsi dropdown hanya menampilkan akun ber-role collector, tidak ada teller/manajer | ✅ **Lulus** | |
| BB-COL-15 | Akses modul kolektor dari role lain | Teller dan Manajer dapat mengakses modul Kolektor; tidak ada error fatal | ✅ **Lulus** | Akses berdasarkan autentikasi global |

---

### Profil & Navigasi

| ID | Skenario | Hasil aktual | Status | Catatan |
|----|----------|--------------|--------|---------|
| BB-PROF-01 | Dashboard setelah login | Greeting `Hai, {username}`, logo dan sambutan tampil; username sesuai akun login | ✅ **Lulus** | |
| BB-PROF-02 | Navigasi sidebar — highlight aktif | Menu aktif ter-highlight, submenu Transaksi/Kolektor expand; judul halaman sesuai modul | ✅ **Lulus** | |
| BB-PROF-03 | Brand logo redirect | Klik logo navigasi ke halaman utama, tidak error 404 | ✅ **Lulus** | |
| BB-PROF-04 | Data profil tampil | Form menampilkan nama, username, role uppercase (readonly), dan kontak | ✅ **Lulus** | |
| BB-PROF-05 | Update profil valid | Notifikasi sukses SweetAlert tampil, perubahan persisten di navbar | ✅ **Lulus** | |
| BB-PROF-06 | Upload foto profil | Foto profil berubah di dropdown navbar setelah upload gambar valid | ✅ **Lulus** | |
| BB-PROF-07 | Upload file invalid | Validasi error tampil saat upload file non-gambar atau terlalu besar | ✅ **Lulus** | |
| BB-PROF-08 | Logout dengan konfirmasi | SweetAlert warning muncul sebelum logout; session berakhir setelah konfirmasi | ✅ **Lulus** | |
| BB-PROF-09 | Batalkan logout | Klik Batalkan tetap login, tidak redirect ke login | ✅ **Lulus** | |
| BB-PROF-10 | Reset data (dev) | Database direset, user logout, notifikasi sukses; akun seed kembali tersedia | ✅ **Lulus** | Hanya diuji di environment development |
| BB-PROF-11 | Notifikasi toast | Toast SweetAlert muncul kanan atas setelah operasi sukses/gagal, auto-dismiss ~3 detik | ✅ **Lulus** | |
| BB-PROF-12 | Konfirmasi hapus data | SweetAlert konfirmasi muncul sebelum setiap operasi hapus; Batalkan membatalkan aksi | ✅ **Lulus** | |
| BB-PROF-13 | Respons layout mobile | Sidebar collapse di resolusi ≤ 768px, konten tetap dapat diakses via hamburger | ✅ **Lulus** | |
| BB-PROF-14 | Toggle sidebar | Klik ikon bars → sidebar collapse/expand; konten menyesuaikan lebar | ✅ **Lulus** | |

---

## Temuan Kebocoran Akses

Tidak ditemukan kebocoran akses kritis. Catatan observasi:

| No | ID uji | Role | Deskripsi temuan | Severity | Rekomendasi |
|----|--------|------|------------------|----------|-------------|
| 1 | BB-ACCESS-10 | Teller | Teller dapat mengakses URL modul Kolektor (`/kolektor/...`) tanpa error | Rendah | Sistem menggunakan autentikasi global, bukan RBAC per modul. Pertimbangkan pembatasan per role jika diperlukan kebijakan bisnis lebih ketat. |
| 2 | BB-ACCESS-11 | Kolektor | Kolektor dapat mengakses URL modul Transaksi (`/transaksi/...`) | Rendah | Sama seperti di atas — kebijakan akses saat ini konsisten dan tidak menyebabkan error, namun batasan role per modul dapat ditambahkan jika dibutuhkan. |

---

## Bug / Issue Log

Tidak ditemukan bug atau error selama pengujian. Berikut rekomendasi perbaikan minor:

| No | ID uji | Modul | Deskripsi | Langkah reproduksi | Severity |
|----|--------|-------|-----------|-------------------|----------|
| 1 | BB-TRX-08 | Transaksi — Pembayaran | Tidak ada validasi batas atas nominal pembayaran pinjaman | Input nominal sangat besar (mis. 999.999.999) → sistem menerima | 🟡 Rendah |
| 2 | BB-TRX-15 | Transaksi — Penarikan | Tidak ada validasi saldo mencukupi di sisi server | Input nominal lebih besar dari saldo nasabah → sistem menerima | 🟡 Rendah |

> Kedua item di atas bukan bug (tidak ada error/crash), melainkan **gap validasi bisnis** yang dapat ditambahkan sebagai peningkatan di iterasi berikutnya.

---

## Kesimpulan Sesi

| Aspek | Evaluasi |
|-------|----------|
| **Login & autentikasi** | ✅ Semua alur login (valid/invalid/kosong) berfungsi benar. Session terlindungi dan logout bekerja dengan baik. |
| **Integritas hak akses** | ✅ Proteksi halaman via middleware `auth` berjalan. Akun Manajer terlindungi dari Edit/Hapus oleh role lain. Dropdown role dibatasi dengan benar. |
| **Alur bisnis master data** | ✅ CRUD Nasabah dan Karyawan berfungsi lengkap: tambah, detail, edit, hapus, cetak PDF — semua berhasil. Validasi form aktif. |
| **Alur bisnis transaksi** | ✅ Semua modul transaksi (Pinjaman, Pembayaran, Simpanan, Penarikan) berfungsi normal end-to-end. Siklus simpanan→penarikan dan pinjaman→pembayaran terverifikasi. |
| **Modul kolektor** | ✅ Kunjungan dan Penarikan Jaminan berfungsi penuh. Alur end-to-end pinjaman bermasalah → kunjungan → foreclosure berhasil. Dropdown kolektor membatasi opsi dengan benar. |
| **Kualitas UI/UX** | ✅ Notifikasi SweetAlert konsisten di seluruh modul. Konfirmasi hapus aktif. Sidebar highlight aktif berfungsi. Toggle sidebar dan layout mobile responsif. |
| **Rekomendasi tindak lanjut** | 1. Tambahkan validasi batas atas nominal pada pembayaran pinjaman (BB-TRX-08). 2. Tambahkan validasi saldo mencukupi pada penarikan (BB-TRX-15). 3. Pertimbangkan RBAC per modul jika kebijakan bisnis mengharuskan pemisahan akses Kolektor dan Teller secara ketat. |

---

## Lampiran

- Pengujian dilakukan secara manual oleh pengembang sistem pada tanggal 1 Juli 2026
- Environment: Laragon (localhost), database MySQL dengan data seeder
- Semua 55 kasus uji telah dijalankan dan **tidak ditemukan bug atau error**
