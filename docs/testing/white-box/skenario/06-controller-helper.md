# Skenario 06 — Controller Helper

Pengujian fungsi utilitas internal pada base controller dan helper terkait.

**Referensi kode:**
- `app/Http/Controllers/Controller.php`
- `app/Http/Controllers/CustomerController.php` (`currentBalanceByDeposit`)
- `app/Http/Controllers/ForeclosureController.php` (kalkulasi total_amount di DataTables)

**Test script:** `tests/Unit/Controller/TransactionCodeTest.php`

---

## A. buildTransactionCode($id)

### WB-CTRL-01: Format kode transaksi

| Field | Nilai |
|-------|-------|
| **Method** | `Controller::buildTransactionCode($id)` |
| **Format** | `{code}-%05d` dengan `$code = "SK"` |
| **Input** | id = 7 |
| **Harapan** | `"SK-00007"` |
| **Area uji** | `sprintf($this->code . "-%05d", $id)` |

### WB-CTRL-02: Zero-padding ID besar

| Field | Nilai |
|-------|-------|
| **Input** | id = 12345 |
| **Harapan** | `"SK-12345"` (5 digit, tidak truncate) |

### WB-CTRL-03: Penggunaan di modul

| Controller | Variabel view |
|------------|---------------|
| LoanController | `$code` di show |
| DepositController | cetak PDF |
| InstallmentController | show, edit |

---

## B. buildTitle($section)

### WB-CTRL-04: Format judul halaman

| Field | Nilai |
|-------|-------|
| **Input** | `$this->title = 'Pinjaman'`, `$section = 'baru'` |
| **Harapan** | `"Pinjaman <span class=\"font-weight-light h5\">baru</span>"` |
| **Area uji** | HTML title di layout |

---

## C. Manajemen Gambar

| ID | Method | Kondisi | Harapan |
|----|--------|---------|---------|
| WB-CTRL-05 | `storeImage()` | File photo ada | Return nama file |
| WB-CTRL-06 | `storeImage()` | Tanpa file | Return null |
| WB-CTRL-07 | `deleteImage()` | Nama valid | Hapus dari storage |
| WB-CTRL-08 | `updateImage()` | File baru | Hapus lama, simpan baru |

---

## D. CustomerController — currentBalanceByDeposit

### WB-CTRL-09: Rumus saldo sukarela

| Field | Nilai |
|-------|-------|
| **Rumus** | `SUM(type=sukarela) - SUM(type=penarikan)` |
| **Setup** | sukarela 1.000.000; penarikan 200.000 |
| **Harapan** | current_balance = 800.000 |
| **Response** | JSON `{ status: 'success', data: {...} }` |

### WB-CTRL-10: Format currency

| Field | Nilai |
|-------|-------|
| **Field** | `current_balance_formatted` |
| **Format** | `'Rp' . number_format($balance, 2, ',', '.')` |
| **Harapan** | `"Rp800.000,00"` |

---

## E. ForeclosureController — total_amount (DataTables)

### WB-CTRL-11: Kalkulasi total dengan collateral

| Field | Nilai |
|-------|-------|
| **Rumus** | `collateral.value - remaining_amount` |
| **Kondisi** | `$row->collateral` exists |
| **Area uji** | Closure `addColumn('total_amount')` |

### WB-CTRL-12: Kalkulasi total tanpa collateral

| Field | Nilai |
|-------|-------|
| **Rumus** | `0 - remaining_amount` |
| **Kondisi** | collateral null |

---

## F. HomeController — truncate

### WB-CTRL-13: Reset data via Artisan

| Field | Nilai |
|-------|-------|
| **Method** | `HomeController::truncate()` |
| **Aksi** | `Artisan::call('migrate:fresh --seed')` + `Auth::logout()` |
| **Harapan** | Database fresh, user logout |
| **Catatan** | Hanya uji di environment testing |
