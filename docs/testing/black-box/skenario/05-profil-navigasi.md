# Skenario 05 — Profil dan Navigasi

Pengujian navigasi umum, pengaturan profil, dan interaksi UI global.

---

## A. Dashboard

### BB-PROF-01: Tampilan dashboard setelah login

| Field | Nilai |
|-------|-------|
| **Langkah** | Login dengan masing-masing role → perhatikan dashboard |
| **Harapan** | Greeting `Hai, {username}`, logo dan teks selamat datang tampil |
| **Verifikasi UI** | Username sesuai akun yang login |

### BB-PROF-02: Navigasi sidebar — highlight menu aktif

| Field | Nilai |
|-------|-------|
| **Langkah** | Klik tiap menu sidebar |
| **Harapan** | Menu aktif ter-highlight (class active), submenu Transaksi/Kolektor expand |
| **Verifikasi UI** | Judul halaman (`<h1>`) sesuai modul |

### BB-PROF-03: Brand logo redirect

| Field | Nilai |
|-------|-------|
| **Langkah** | Klik logo SWAMITRA APP di sidebar |
| **Harapan** | Navigasi ke halaman utama/root |
| **Verifikasi UI** | Tidak error 404 |

---

## B. Profil Pengguna (`/pengaturan`)

### BB-PROF-04: Menampilkan data profil

| Field | Nilai |
|-------|-------|
| **Langkah** | Menu profil → Pengaturan |
| **Harapan** | Form menampilkan nama, username, role (readonly), kontak |
| **Verifikasi UI** | Role uppercase (MANAGER/TELLER/COLLECTOR) |

### BB-PROF-05: Update profil valid

| Field | Nilai |
|-------|-------|
| **Input** | Ubah nomor telepon atau alamat |
| **Harapan** | Notifikasi sukses, perubahan tampil di navbar profil |
| **Verifikasi UI** | Toast sukses SweetAlert |

### BB-PROF-06: Upload foto profil (opsional)

| Field | Nilai |
|-------|-------|
| **Input** | File gambar valid (jpeg/png, ≤ 2MB) |
| **Harapan** | Foto profil berubah di dropdown navbar |
| **Verifikasi UI** | Avatar/image terupdate |

### BB-PROF-07: Upload file invalid

| Field | Nilai |
|-------|-------|
| **Input** | File non-gambar atau > 2MB |
| **Harapan** | Validasi error |
| **Verifikasi UI** | Pesan error jelas |

---

## C. Logout dan Reset

### BB-PROF-08: Logout dengan konfirmasi

| Field | Nilai |
|-------|-------|
| **Langkah** | Profil → Keluar → konfirmasi "Iya, saya yakin!" |
| **Harapan** | Session berakhir |
| **Verifikasi UI** | SweetAlert warning sebelum logout |

### BB-PROF-09: Batalkan logout

| Field | Nilai |
|-------|-------|
| **Langkah** | Profil → Keluar → Batalkan |
| **Harapan** | Tetap login, tetap di halaman saat ini |
| **Verifikasi UI** | Tidak redirect ke login |

### BB-PROF-10: Reset data (lingkungan dev saja)

| Field | Nilai |
|-------|-------|
| **Langkah** | Profil → Reset Data → konfirmasi |
| **Harapan** | Database direset, user logout, notifikasi sukses |
| **Verifikasi UI** | **Hanya di development** — verifikasi akun seed kembali tersedia |

---

## D. Komponen UI Global

### BB-PROF-11: Notifikasi toast sukses/error

| Field | Nilai |
|-------|-------|
| **Langkah** | Trigger operasi sukses (simpan data) dan operasi gagal (validasi) |
| **Harapan** | Toast SweetAlert muncul kanan atas, auto-dismiss ~3 detik |
| **Verifikasi UI** | Icon success/error sesuai |

### BB-PROF-12: Konfirmasi hapus data

| Field | Nilai |
|-------|-------|
| **Langkah** | Klik tombol Hapus pada record manapun |
| **Harapan** | SweetAlert konfirmasi sebelum submit |
| **Verifikasi UI** | Tombol Batalkan membatalkan aksi |

### BB-PROF-13: Respons layout mobile (opsional)

| Field | Nilai |
|-------|-------|
| **Langkah** | Resize browser ≤ 768px atau device mobile |
| **Harapan** | Sidebar collapse, konten tetap dapat diakses |
| **Verifikasi UI** | Tombol hamburger menu berfungsi |

### BB-PROF-14: Toggle sidebar

| Field | Nilai |
|-------|-------|
| **Langkah** | Klik ikon bars di navbar |
| **Harapan** | Sidebar collapse/expand |
| **Verifikasi UI** | Konten menyesuaikan lebar |
