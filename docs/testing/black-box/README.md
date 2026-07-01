# Kerangka Black-Box Testing

Dokumen ini menjadi panduan pengujian **black-box** aplikasi Koperasi Swamitra Karya Bersama. Pengujian dilakukan dari perspektif pengguna akhir tanpa melihat implementasi kode backend.

## Tujuan

1. Memverifikasi **antarmuka pengguna (UI)** menampilkan elemen, pesan, dan alur navigasi yang benar.
2. Memvalidasi **alur bisnis** utama koperasi (nasabah, transaksi, kolektor) berjalan sesuai kebutuhan operasional.
3. Memastikan modul **login** merespons input dengan tepat dan **hak akses per role** (Manajer, Teller, Kolektor) terjaga tanpa kebocoran akses.

## Ruang Lingkup

| Area | Cakupan |
|------|---------|
| Autentikasi | Login, logout, proteksi halaman, validasi kredensial |
| Hak akses | Perilaku UI dan operasi per role |
| Data master | Karyawan, Nasabah |
| Transaksi | Pinjaman, Pembayaran Pinjaman, Simpanan, Penarikan |
| Kolektor | Nasabah Bermasalah, Penarikan Jaminan |
| Profil | Pengaturan akun, reset data |

## Struktur Dokumen

```
docs/testing/black-box/
├── README.md                      ← Panduan utama (dokumen ini)
├── lingkungan-pengujian.md        ← Prasyarat, akun uji, URL
├── matriks-hak-akses.md           ← Referensi hak akses per role
├── template-hasil-pengujian.md    ← Format pencatatan hasil
└── skenario/
    ├── 01-login-dan-hak-akses.md  ← Prioritas: login & RBAC
    ├── 02-master-data.md
    ├── 03-transaksi.md
    ├── 04-kolektor.md
    └── 05-profil-navigasi.md
```

## Metodologi

### Prinsip black-box

- Penguji hanya berinteraksi melalui **browser** (UI web).
- Tidak memeriksa source code, query database, atau log server kecuali untuk verifikasi tambahan di luar scope black-box murni.
- Fokus pada **input → respons sistem → output yang terlihat pengguna**.

### Tahapan pengujian

1. **Persiapan** — Siapkan lingkungan uji dan reset data jika diperlukan (lihat [lingkungan-pengujian.md](./lingkungan-pengujian.md)).
2. **Eksekusi skenario** — Jalankan kasus uji berurutan per modul di folder `skenario/`.
3. **Verifikasi UI** — Periksa halaman, notifikasi, pesan error, dan navigasi sidebar.
4. **Verifikasi hak akses** — Bandingkan perilaku aktual dengan [matriks-hak-akses.md](./matriks-hak-akses.md).
5. **Pencatatan** — Isi [template-hasil-pengujian.md](./template-hasil-pengujian.md).

### Kode identifikasi kasus uji

Format: `BB-<MODUL>-<NOMOR>`

| Prefix | Modul |
|--------|-------|
| `BB-LOGIN` | Login & autentikasi |
| `BB-ACCESS` | Hak akses & kebocoran akses |
| `BB-MASTER` | Data master (Karyawan, Nasabah) |
| `BB-TRX` | Transaksi |
| `BB-COL` | Modul Kolektor |
| `BB-PROF` | Profil & navigasi |

### Status hasil

| Status | Keterangan |
|--------|------------|
| **Lulus** | Hasil aktual sesuai harapan |
| **Gagal** | Hasil aktual tidak sesuai harapan |
| **Diblokir** | Kasus tidak dapat dijalankan (lingkungan/error prasyarat) |
| **Catatan** | Lulus dengan temuan minor yang perlu dicatat |

## Prioritas Pengujian

Pengujian dimulai dari modul dengan risiko keamanan tertinggi:

1. **[01-login-dan-hak-akses.md](./skenario/01-login-dan-hak-akses.md)** — Login tiga role + uji kebocoran akses
2. **[02-master-data.md](./skenario/02-master-data.md)** — CRUD nasabah & karyawan
3. **[03-transaksi.md](./skenario/03-transaksi.md)** — Alur bisnis transaksi
4. **[04-kolektor.md](./skenario/04-kolektor.md)** — Modul penagihan
5. **[05-profil-navigasi.md](./skenario/05-profil-navigasi.md)** — Pengaturan & navigasi umum

## Relasi dengan Dokumen Lain

| Dokumen | Perbedaan fokus |
|---------|-----------------|
| [black-box-testing.md](../black-box-testing.md) | Ringkasan hasil pengujian (legacy) |
| [gray-box-testing.md](../gray-box-testing.md) | Integrasi UI ↔ database |
| [white-box-testing.md](../white-box-testing.md) | Logika internal & perhitungan |
