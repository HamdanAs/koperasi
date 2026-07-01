# 📋 Kerangka Pengujian Gray-Box
## Sistem Informasi Koperasi Swamitra Karya Bersama

---

## Tentang Metode Gray-Box

Pengujian **Gray-Box** adalah pendekatan hybrid yang menggabungkan perspektif Black-Box (pengujian fungsional dari antarmuka pengguna) dengan pengetahuan parsial tentang struktur internal sistem (White-Box), khususnya skema basis data dan logika controller.

Pada sistem informasi koperasi ini, metode gray-box difokuskan pada:
1. **Sinkronisasi data** antara antarmuka (front-end Blade) dan basis data MySQL.
2. **Integritas relasi** antar tabel (foreign key, cascade, constraint).
3. **Konsistensi transaksi** saat operasi multi-tabel berlangsung dalam DB Transaction.
4. **Anomali data** yang mungkin muncul akibat migrasi atau kondisi edge-case.

---

## Struktur Dokumen Pengujian

```
docs/testing/gray-box/
│
├── README.md                          ← Dokumen ini (indeks utama)
│
├── 01-rencana-pengujian.md            ← Master Test Plan
├── 02-peta-relasi-database.md         ← Peta relasi tabel & titik uji kritis
│
├── skenario/
│   ├── GB-TC-001-sinkronisasi-simpanan.md
│   ├── GB-TC-002-integritas-pinjaman-jaminan.md
│   ├── GB-TC-003-sinkronisasi-pembayaran-cicilan.md
│   ├── GB-TC-004-integritas-penarikan-jaminan.md
│   ├── GB-TC-005-integritas-nasabah-cascading.md
│   ├── GB-TC-006-konsistensi-saldo-simpanan.md
│   ├── GB-TC-007-transaksi-db-rollback.md
│   └── GB-TC-008-migrasi-anomali-data.md
│
└── hasil/
    └── template-laporan-hasil.md
```

---

## Ringkasan Modul yang Diuji

| Modul | Controller | Tabel Terkait | Prioritas |
|---|---|---|---|
| Simpanan | `DepositController` | `deposits`, `customers`, `loans` | 🔴 Tinggi |
| Pinjaman | `LoanController` | `loans`, `collaterals`, `customers` | 🔴 Tinggi |
| Pembayaran Cicilan | `InstallmentController` | `deposits`, `loans`, `customers` | 🔴 Tinggi |
| Penarikan Jaminan | `ForeclosureController` | `foreclosures`, `loans`, `collaterals`, `customers` | 🟠 Sedang |
| Nasabah | `CustomerController` | `customers` | 🟠 Sedang |
| Kunjungan | `VisitController` | `visits`, `customers` | 🟡 Rendah |

---

## Konvensi Penamaan Kasus Uji

```
GB-TC-[NNN]-[nama-modul]
```
- `GB` = Gray-Box
- `TC` = Test Case
- `NNN` = Nomor urut 3 digit

---

## Tim & Periode Pengujian

| Item | Detail |
|---|---|
| **Aplikasi** | Sistem Informasi Koperasi Swamitra Karya Bersama |
| **Framework** | Laravel (PHP), MySQL |
| **Metode** | Gray-Box Testing |
| **Periode** | Juli 2026 |
| **Versi Dokumen** | 1.0.0 |
