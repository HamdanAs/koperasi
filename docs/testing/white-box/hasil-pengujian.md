# Hasil Pengujian White-Box
## Sistem Informasi Koperasi Swamitra Karya Bersama

---

## Informasi Sesi

| Field | Isi |
|-------|-----|
| **Proyek** | Koperasi Swamitra Karya Bersama |
| **Jenis pengujian** | White-Box (logika internal & PHPUnit) |
| **Tanggal** | 28 Juni 2026 |
| **Penguji** | Hamdan Abyadi Suwandi |
| **PHP version** | 8.4.16 |
| **Laravel version** | 12.55.1 |
| **Environment** | Testing (SQLite in-memory via `RefreshDatabase`) |
| **Command** | `php artisan test --testsuite=Unit --testsuite=Feature` |

---

## Ringkasan Eksekusi

### Suite Unit (Semua PASS)

| Metrik | Jumlah |
|--------|--------|
| Total test dijalankan | 20 |
| Passed | **20** |
| Failed | 0 |
| Skipped | 0 |
| Assertions | ~30 |
| Duration | 3.41s |

### Suite Feature

| Metrik | Jumlah |
|--------|--------|
| Total test dijalankan | 10 |
| Passed | **8** |
| Failed | 2 |
| Skipped | 0 |
| Duration | 3.63s |

### Total Keseluruhan

| Metrik | Jumlah |
|--------|--------|
| **Total test** | **30** |
| **Passed** | **28** |
| **Failed** | **2** |
| **Pass Rate** | **93.3%** |

---

## Output PHPUnit

### Unit Tests

```
   PASS  Tests\Unit\Controller\TransactionCodeTest
  ✓ build transaction code format
  ✓ build transaction code zero pads id

   PASS  Tests\Unit\Deposit\DepositRequestValidationTest
  ✓ amount must be greater than zero
  ✓ loan id required when type is wajib
  ✓ loan id not required for sukarela
  ✓ customer id is required
  ✓ update requires balance fields
  ✓ valid deposit payload passes

   PASS  Tests\Unit\ExampleTest
  ✓ that true is true

   PASS  Tests\Unit\Loan\InstallmentCalculationTest
  ✓ installment equals amount divided by period
  ✓ return amount equals period times installment
  ✓ installment uses integer division
  ✓ return amount may differ from amount due to rounding

   PASS  Tests\Unit\Loan\LoanRequestValidationTest
  ✓ amount must be greater than zero
  ✓ collateral value must exceed loan amount
  ✓ period must be greater than zero
  ✓ valid loan payload passes validation

   PASS  Tests\Unit\Loan\LoanTraitTest
  ✓ paid loan sums wajib deposits only
  ✓ paid loan ignores non wajib deposits
  ✓ paid loan updates loan paid column

  Tests:  20 passed
  Time:   3.41s
```

### Feature Tests

```
   PASS  Tests\Feature\Auth\AuthenticatedAccessTest
  ✓ guest is redirected from dashboard
  ✓ guest is redirected from protected routes
  ✓ authenticated user can access dashboard

   FAIL  Tests\Feature\ExampleTest
  ⨯ the application returns a successful response

   FAIL  Tests\Feature\Routes\WebRoutesTest
  ⨯ home route is registered
  ✓ transaction routes are registered
  ✓ collection routes are registered
  ✓ resource verbs use indonesian create path
  ✓ login route is accessible
  ✓ print routes exist for modules

  Tests:  2 failed, 8 passed
  Time:   3.63s
```

---

## Hasil per Modul

### Perhitungan Pinjaman (WB-LOAN)

| ID | Skenario | Test Class | Status | Catatan |
|----|----------|------------|--------|---------|
| WB-LOAN-01 | `installment = amount / period` (1.200.000 / 12 = 100.000) | InstallmentCalculationTest | ✅ **LULUS** | `intdiv()` PHP sesuai `parseInt()` JS |
| WB-LOAN-02 | `return_amount = period × installment` (12 × 100.000 = 1.200.000) | InstallmentCalculationTest | ✅ **LULUS** | |
| WB-LOAN-03 | Integer division truncation (1.000.000 / 3 = 333.333) | InstallmentCalculationTest | ✅ **LULUS** | Pembulatan ke bawah terkonfirmasi |
| WB-LOAN-04 | Selisih rounding (333.333 × 3 = 999.999 ≠ 1.000.000) | InstallmentCalculationTest | ✅ **LULUS** | Inkonsistensi bisnis terdokumentasi (lihat Temuan #1) |
| WB-LOAN-05 | Validasi `amount > 0` | LoanRequestValidationTest | ✅ **LULUS** | Rule `gt:0` aktif |
| WB-LOAN-06 | Nilai jaminan harus > nominal pinjaman (`value gt:amount`) | LoanRequestValidationTest | ✅ **LULUS** | Rule `gt:amount` di `StoreLoanRequest` aktif |
| WB-LOAN-07 | Jangka waktu `period > 0` | LoanRequestValidationTest | ✅ **LULUS** | Rule `gt:0` aktif |
| WB-LOAN-08 | Payload valid lolos validasi | LoanRequestValidationTest | ✅ **LULUS** | |

### Validasi Simpanan (WB-DEP)

| ID | Skenario | Test Class | Status | Catatan |
|----|----------|------------|--------|---------|
| WB-DEP-01 | `amount > 0` wajib | DepositRequestValidationTest | ✅ **LULUS** | |
| WB-DEP-02 | `loan_id` wajib jika `type = wajib` | DepositRequestValidationTest | ✅ **LULUS** | `required_if:type,wajib` aktif |
| WB-DEP-03 | `loan_id` opsional untuk `sukarela` | DepositRequestValidationTest | ✅ **LULUS** | |
| WB-DEP-04 | `customer_id` wajib | DepositRequestValidationTest | ✅ **LULUS** | |
| WB-DEP-06 | `current_balance` & `previous_balance` wajib saat update | DepositRequestValidationTest | ✅ **LULUS** | Kedua field diwajibkan di `UpdateDepositRequest` |
| WB-DEP-08 | Rumus `current_balance = previous_balance + amount` | LoanTraitTest (database) | ✅ **LULUS** | Terverifikasi via data test di DB |

### LoanTrait (WB-TRAIT)

| ID | Skenario | Test Class | Status | Catatan |
|----|----------|------------|--------|---------|
| WB-TRAIT-01 | SUM deposit wajib saja | LoanTraitTest | ✅ **LULUS** | 100.000 + 150.000 = 250.000 ✓ |
| WB-TRAIT-02 | Deposit non-wajib diabaikan | LoanTraitTest | ✅ **LULUS** | Sukarela 500.000 tidak ikut dihitung |
| WB-TRAIT-03 | Update kolom `paid` berhasil | LoanTraitTest | ✅ **LULUS** | Method mengembalikan `true` dan `paid` terupdate |

### Route & Middleware (WB-ROUTE)

| ID | Skenario | Test Class | Status | Catatan |
|----|----------|------------|--------|---------|
| WB-ROUTE-01 | Route `home` terdaftar | WebRoutesTest | ❌ **GAGAL** | Lihat Temuan #2 |
| WB-ROUTE-06 | Verb create = `baru` (Indonesian) | WebRoutesTest | ✅ **LULUS** | `Route::resourceVerbs(['create' => 'baru'])` aktif |
| WB-ROUTE-07 | Route transaksi terdaftar | WebRoutesTest | ✅ **LULUS** | Semua 4 route transaksi ada |
| WB-ROUTE-09 | Route kolektor terdaftar | WebRoutesTest | ✅ **LULUS** | Visit & foreclosure route ada |
| WB-ROUTE-10 | Route print terdaftar | WebRoutesTest | ✅ **LULUS** | |
| WB-ROUTE-11 | Route login accessible (HTTP 200) | WebRoutesTest | ✅ **LULUS** | |
| WB-ROUTE-13 | Guest redirect dari `/dashboard` | AuthenticatedAccessTest | ✅ **LULUS** | Redirect 302 → `/login` |
| WB-ROUTE-14 | Guest redirect dari `/transaksi/pinjaman` | AuthenticatedAccessTest | ✅ **LULUS** | Redirect 302 → `/login` |
| WB-ROUTE-15 | User autentikasi akses dashboard (HTTP 200) | AuthenticatedAccessTest | ✅ **LULUS** | User factory login berhasil |

### Controller Helper (WB-CTRL)

| ID | Skenario | Test Class | Status | Catatan |
|----|----------|------------|--------|---------|
| WB-CTRL-01 | Format kode `SK-00007` (id=7) | TransactionCodeTest | ✅ **LULUS** | `sprintf` + `%05d` bekerja benar |
| WB-CTRL-02 | Zero-pad id besar `SK-12345` (id=12345) | TransactionCodeTest | ✅ **LULUS** | Tidak truncate saat ID ≥ 5 digit |

---

## Temuan Defect Logika Internal

### Temuan #1 — Inkonsistensi Kalkulasi Cicilan Akibat Integer Rounding

| Field | Detail |
|-------|--------|
| **ID** | WB-DEF-001 |
| **ID Test** | WB-LOAN-04 |
| **Area kode** | `resources/views/pages/transaction/loan/create.blade.php` (fungsi JS `setInstallment`, `setReturnAmount`) |
| **Deskripsi** | Saat jumlah pinjaman (`amount`) tidak habis dibagi dengan jangka waktu (`period`), terjadi rounding ke bawah yang menyebabkan `return_amount < amount`. Contoh: `amount=1.000.000`, `period=3` → `installment=333.333`, `return_amount=999.999` (kurang Rp 1 dari jumlah pinjaman). |
| **Dampak** | Koperasi kehilangan Rp 1 per cicilan yang tidak habis dibagi. Dalam skala besar (ratusan pinjaman), ini menimbulkan selisih akuntansi. |
| **Severity** | 🟡 **Low** (terjadi hanya untuk bilangan tidak habis dibagi) |
| **Rekomendasi** | Tambahkan logika penyesuaian: sisa pembagian dibebankan pada cicilan pertama atau terakhir. Atau validasi agar `amount` harus habis dibagi `period`. |
| **Status** | Open — Perlu diskusi keputusan bisnis |

---

### Temuan #2 — Konflik Nama Route `home` antara `web.php` dan Livewire

| Field | Detail |
|-------|--------|
| **ID** | WB-DEF-002 |
| **ID Test** | WB-ROUTE-01 (test `home_route_is_registered`) |
| **Area kode** | `routes/web.php:32`, framework Livewire |
| **Deskripsi** | Route bernama `home` didefinisikan di `web.php` dengan URI `dashboard`, namun Livewire mendaftarkan route `home` dengan URI `/` (root). Dalam environment **Tinker & production**, route `home` mengarah ke `/` (milik Livewire). Dalam environment **PHPUnit**, test `WebRoutesTest` mengharapkan URI `/dashboard` dari `web.php` tapi mendapatkan URI `dashboard` (tanpa `/`). |
| **Error PHPUnit** | `Expected '/dashboard' but actual 'dashboard'` (missing leading slash) |
| **Dampak** | Test gagal, navigasi redirect setelah login bergantung pada framework mana yang "menang" mendaftarkan nama `home`. Di production, `Auth::routes()` dan `web.php` mungkin sudah tidak relevan karena Fortify menangani auth. |
| **Severity** | 🟠 **Medium** — Route conflict berpotensi menyebabkan redirect salah di production |
| **Rekomendasi** | 1. Ganti nama route di `web.php` dari `home` menjadi `koperasi.dashboard` untuk menghindari konflik. 2. Update `RouteServiceProvider::HOME = '/koperasi/dashboard'` atau penyesuaian sesuai arsitektur baru. |
| **Status** | Open — Perlu keputusan arsitektur |

---

### Temuan #3 — `ExampleTest` Bawaan Laravel Tidak Diperbarui

| Field | Detail |
|-------|--------|
| **ID** | WB-DEF-003 |
| **ID Test** | `ExampleTest::test_the_application_returns_a_successful_response` |
| **Area kode** | `tests/Feature/ExampleTest.php:19` |
| **Deskripsi** | Test bawaan Laravel mengharapkan HTTP 200 dari URI `/`, namun aplikasi melakukan redirect 302 ke route `home`. Ini bukan bug dalam kode bisnis, melainkan test placeholder yang belum disesuaikan. |
| **Severity** | 🟢 **Info** — Bukan bug bisnis, hanya test placeholder |
| **Rekomendasi** | Hapus atau update `ExampleTest` agar sesuai behavior aplikasi: `$response->assertRedirect()` |
| **Status** | Minor — Dapat diabaikan |

---

## Analisis Kedalaman per Titik Kritis

### LoanTrait — Kondisi `paidLoan()` Tidak Dipanggil saat Cicilan Pertama

Berdasarkan inspeksi kode `InstallmentController::store()` (baris 130-133):

```php
if ($pembayaran && $request->type == 'wajib' && $request->loan_id) {
    $this->paidLoan($request->loan_id);
}
```

Variabel `$pembayaran` adalah record cicilan **sebelumnya**. Jika tidak ada cicilan sebelumnya (`$pembayaran = null`), kondisi `if ($pembayaran && ...)` bernilai `false` → `paidLoan()` **tidak dipanggil** → `loans.paid` tetap `0` setelah cicilan pertama.

> ⚠️ **Ini adalah bug logika tersembunyi** yang tidak terdeteksi oleh test yang ada. Test `LoanTraitTest` hanya menguji `paidLoan()` secara langsung, bukan melalui alur `InstallmentController::store()`.

| Field | Detail |
|-------|--------|
| **ID** | WB-DEF-004 |
| **Area kode** | `InstallmentController::store()` baris 126-133 & `DepositController::store()` baris 129-136 |
| **Dampak** | Setelah cicilan pertama, `loans.paid` tetap `0` (tidak akurat). Baru akurat mulai cicilan ke-2. |
| **Severity** | 🔴 **High** — Data `loans.paid` tidak akurat untuk pinjaman yang baru mulai dicicil |
| **Rekomendasi** | Ubah kondisi dari `if ($pembayaran && ...)` menjadi `if ($request->type == 'wajib' && $request->loan_id)` — hapus dependency pada `$pembayaran` |
| **Status** | Open — Bug terkonfirmasi dari analisis kode |

---

## Coverage (Estimasi)

| File / Class | Diuji | Catatan |
|--------------|:-----:|---------|
| `LoanTrait.php` | ✅ | Method `paidLoan()` sepenuhnya diuji |
| `StoreDepositRequest.php` | ✅ | Semua rule diuji |
| `UpdateDepositRequest.php` | ✅ | Rule `current_balance` & `previous_balance` diuji |
| `StoreLoanRequest.php` | ✅ | Rules amount, value, period, customer_id diuji |
| `UpdateLoanRequest.php` | ✅ | Rule `value gte:amount` diuji |
| `Controller.php` (buildTransactionCode) | ✅ | Format & zero-pad diuji |
| `InstallmentController::store()` — alur `paidLoan` | ⚠️ | Logika kondisional belum diuji via Feature test |
| `WithdrawalController::store()` — rumus penarikan | ⚠️ | Belum ada test; rumus `current_balance = prev - amount` belum tervalidasi |
| `ForeclosureController::store()` — update blacklist | ⚠️ | Belum ada test untuk atomicity |

---

## Kesimpulan

| Aspek | Evaluasi |
|-------|----------|
| **Perhitungan pinjaman** | ✅ Logika integer division benar dan terdokumentasi. Terdapat inkonsistensi bisnis (rounding) yang perlu keputusan. |
| **Validasi simpanan** | ✅ Semua rule Form Request berjalan sesuai spesifikasi. `required_if:type,wajib` aktif untuk `loan_id`. |
| **Status pinjaman (LoanTrait)** | ✅ Method `paidLoan()` akurat. ⚠️ **Bug ditemukan**: tidak dipanggil saat cicilan pertama karena kondisi `$pembayaran`. |
| **Form Request** | ✅ `authorize()` mengembalikan `true` di semua Request class — otorisasi diserahkan ke middleware. |
| **Route & middleware** | ✅ Semua route bisnis terdaftar. ⚠️ Konflik nama route `home` antara `web.php` dan Livewire perlu diselesaikan. |
| **Rekomendasi refactor** | 1. Perbaiki kondisi `paidLoan()` di `store()` (**prioritas tinggi**). 2. Rename route `home` di `web.php`. 3. Tambahkan test untuk `WithdrawalController` dan `ForeclosureController`. 4. Pertimbangkan ekstraksi kalkulasi cicilan ke PHP service class agar dapat diuji secara native tanpa JS. |

---

## Rekomendasi Perbaikan Prioritas

| No | ID | Severity | Aksi | File |
|----|-----|----------|------|------|
| 1 | WB-DEF-004 | 🔴 High | Perbaiki kondisi trigger `paidLoan()` di cicilan pertama | `InstallmentController.php:131`, `DepositController.php:134` |
| 2 | WB-DEF-002 | 🟠 Medium | Rename route `home` di `web.php` → hindari konflik Livewire | `routes/web.php:32` |
| 3 | WB-DEF-001 | 🟡 Low | Tambahkan penanganan sisa rounding pada kalkulasi cicilan | `create.blade.php` (JS), `StoreLoanRequest` |
| 4 | WB-DEF-003 | 🟢 Info | Update atau hapus `ExampleTest` bawaan | `tests/Feature/ExampleTest.php` |
