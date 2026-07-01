# Kerangka White-Box Testing

Dokumen ini menjadi panduan pengujian **white-box** aplikasi Koperasi Swamitra Karya Bersama. Pengujian memvalidasi **logika internal sistem**, spesifikasi teknis, dan perilaku modul Laravel tanpa bergantung pada interaksi UI pengguna akhir.

## Tujuan

1. Memverifikasi **fungsi-fungsi krusial** (perhitungan pinjaman, saldo simpanan, status pembayaran) beroperasi sesuai aturan bisnis.
2. Menguji **validasi Form Request** pada input transaksi dan data master.
3. Memastikan **konfigurasi route, middleware, dan controller** sesuai spesifikasi teknis framework Laravel.
4. Menyimpan **test script PHPUnit** secara terorganisir di folder `tests/` untuk regresi otomatis.

## Ruang Lingkup

| Area | Target kode | Jenis uji |
|------|-------------|-----------|
| Perhitungan pinjaman | `resources/views/pages/transaction/loan/*.blade.php` (JS), `StoreLoanRequest` | Unit / spesifikasi |
| Validasi simpanan | `StoreDepositRequest`, `UpdateDepositRequest`, `DepositController` | Unit / Feature |
| Status pinjaman | `App\Traits\LoanTrait::paidLoan()` | Unit (database) |
| Saldo nasabah | `CustomerController::currentBalanceByDeposit()` | Feature |
| Form Request | `app/Http/Requests/*` | Unit |
| Route & middleware | `routes/web.php`, `LoginController` | Feature |
| Helper controller | `Controller::buildTransactionCode()` | Unit |

## Struktur Dokumen

```
docs/testing/white-box/
├── README.md                          ← Panduan utama (dokumen ini)
├── lingkungan-pengujian.md            ← Prasyarat PHPUnit & database uji
├── struktur-test-script.md            ← Pemetaan skenario → file test
├── template-hasil-pengujian.md        ← Format pencatatan hasil
└── skenario/
    ├── 01-perhitungan-pinjaman.md
    ├── 02-validasi-simpanan.md
    ├── 03-status-pinjaman-loan-trait.md
    ├── 04-form-request-validasi.md
    ├── 05-route-dan-middleware.md
    └── 06-controller-helper.md
```

## Struktur Test Script

```
tests/
├── Unit/
│   ├── Loan/
│   │   ├── InstallmentCalculationTest.php
│   │   └── LoanTraitTest.php
│   ├── Deposit/
│   │   └── DepositRequestValidationTest.php
│   ├── Loan/
│   │   └── LoanRequestValidationTest.php
│   └── Controller/
│       └── TransactionCodeTest.php
└── Feature/
    ├── Routes/
    │   └── WebRoutesTest.php
    └── Auth/
        └── AuthenticatedAccessTest.php
```

Detail pemetaan: [struktur-test-script.md](./struktur-test-script.md).

## Metodologi

### Prinsip white-box

- Penguji **memahami struktur kode** (model, trait, request, route, controller).
- Fokus pada **cabang logika, rumus, validasi, dan side effect** (update `paid`, saldo `current_balance`).
- Test script dijalankan via **PHPUnit**, bukan manual di browser.

### Tahapan pengujian

1. **Persiapan** — Konfigurasi environment testing ([lingkungan-pengujian.md](./lingkungan-pengujian.md)).
2. **Unit test** — Fungsi terisolasi (validasi, rumus, trait).
3. **Feature test** — Route, middleware, response HTTP.
4. **Eksekusi** — `php artisan test` atau `./vendor/bin/phpunit`.
5. **Pencatatan** — Isi [template-hasil-pengujian.md](./template-hasil-pengujian.md).

### Kode identifikasi kasus uji

Format: `WB-<MODUL>-<NOMOR>`

| Prefix | Modul |
|--------|-------|
| `WB-LOAN` | Perhitungan & validasi pinjaman |
| `WB-DEP` | Simpanan & penarikan |
| `WB-TRAIT` | LoanTrait / status pinjaman |
| `WB-REQ` | Form Request validation |
| `WB-ROUTE` | Route & middleware |
| `WB-CTRL` | Helper & controller |

### Menjalankan test

```bash
# Semua test
php artisan test

# Satu suite
php artisan test --testsuite=Unit
php artisan test --testsuite=Feature

# Satu file
php artisan test tests/Unit/Loan/InstallmentCalculationTest.php
```

## Relasi dengan Dokumen Lain

| Dokumen | Perbedaan fokus |
|---------|-----------------|
| [black-box/](../black-box/README.md) | UI & alur bisnis tanpa melihat kode |
| [gray-box-testing.md](../gray-box-testing.md) | Integrasi form ↔ database |
| [white-box-testing.md](../white-box-testing.md) | Ringkasan hasil pengujian (legacy) |
