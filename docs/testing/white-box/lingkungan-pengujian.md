# Lingkungan Pengujian White-Box

## Prasyarat

| No | Prasyarat | Verifikasi |
|----|-----------|------------|
| 1 | PHP ≥ 8.x & Composer terinstal | `php -v`, `composer -V` |
| 2 | Dependency terpasang | `composer install` |
| 3 | PHPUnit tersedia | `php artisan test --version` |
| 4 | File `.env` ada | `APP_KEY` ter-generate |
| 5 | Database uji dikonfigurasi | Lihat bagian Database di bawah |

## Konfigurasi Database Uji

Untuk test yang membutuhkan database (`LoanTraitTest`, `AuthenticatedAccessTest`, dll.), pilih salah satu opsi:

**Opsi A — SQLite in-memory (disarankan untuk isolasi)**

1. Pastikan ekstensi PHP `pdo_sqlite` aktif (Laragon: PHP → Quick settings → `pdo_sqlite`).
2. Uncomment baris di `phpunit.xml`:

```xml
<env name="DB_CONNECTION" value="sqlite"/>
<env name="DB_DATABASE" value=":memory:"/>
```

**Opsi B — MySQL terpisah** di `.env.testing`:

```env
DB_CONNECTION=mysql
DB_DATABASE=koperasi_swamitra_test
```

Jalankan migrasi sebelum test berbasis database:

```bash
php artisan migrate --env=testing
```

## Environment PHPUnit

Variabel default di `phpunit.xml`:

| Variabel | Nilai | Fungsi |
|----------|-------|--------|
| `APP_ENV` | `testing` | Mode aplikasi uji |
| `CACHE_DRIVER` | `array` | Cache in-memory |
| `SESSION_DRIVER` | `array` | Session in-memory |
| `MAIL_MAILER` | `array` | Email tidak terkirim |
| `BCRYPT_ROUNDS` | `4` | Hash lebih cepat |

## Area Kode yang Diuji

| Path | Komponen |
|------|----------|
| `app/Traits/LoanTrait.php` | Agregasi pembayaran pinjaman |
| `app/Http/Requests/StoreLoanRequest.php` | Validasi pinjaman |
| `app/Http/Requests/StoreDepositRequest.php` | Validasi simpanan |
| `app/Http/Requests/UpdateDepositRequest.php` | Validasi update simpanan |
| `app/Http/Controllers/DepositController.php` | Logika saldo & `paidLoan()` |
| `app/Http/Controllers/WithdrawalController.php` | Penurunan saldo |
| `app/Http/Controllers/CustomerController.php` | `currentBalanceByDeposit()` |
| `app/Http/Controllers/Controller.php` | `buildTransactionCode()` |
| `routes/web.php` | Route web & middleware `auth` |
| `app/Http/Controllers/Auth/LoginController.php` | Autentikasi username |

## Spesifikasi Bisnis — Perhitungan Pinjaman

Logika perhitungan angsuran saat ini diimplementasikan di view (JavaScript):

```javascript
// resources/views/pages/transaction/loan/create.blade.php
installment = parseInt(amount / period)
return_amount = period * installment
```

White-box test memvalidasi **spesifikasi rumus** ini melalui `InstallmentCalculationTest`, meskipun implementasi UI berada di frontend.

## Spesifikasi Bisnis — Pembayaran Pinjaman

`LoanTrait::paidLoan($id)`:

```
paid = SUM(deposits.amount) WHERE loan_id = $id AND type = 'wajib'
```

Field `loans.paid` di-update dengan hasil agregasi tersebut.

## Spesifikasi Bisnis — Saldo Simpanan

| Operasi | Rumus `current_balance` |
|---------|-------------------------|
| Setor simpanan | `previous_balance + amount` |
| Penarikan | `previous_balance - amount` |
| Saldo sukarela (API) | `SUM(sukarela) - SUM(penarikan)` |

## Perintah Berguna

```bash
# Coverage (jika xdebug/pcov tersedia)
php artisan test --coverage

# Filter method
php artisan test --filter=InstallmentCalculationTest

# Verbose
php artisan test -v
```
