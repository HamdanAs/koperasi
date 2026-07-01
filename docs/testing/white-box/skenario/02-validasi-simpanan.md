# Skenario 02 — Validasi Simpanan

Pengujian aturan validasi input dan logika saldo pada modul simpanan, pembayaran, dan penarikan.

**Referensi kode:**
- `app/Http/Requests/StoreDepositRequest.php`
- `app/Http/Requests/UpdateDepositRequest.php`
- `app/Http/Controllers/DepositController.php` (method `store`, `update`)
- `app/Http/Controllers/WithdrawalController.php` (method `store`)
- `app/Http/Controllers/InstallmentController.php` (method `store`)
- `database/migrations/2022_06_19_094908_create_deposits_table.php`

**Test script:** `tests/Unit/Deposit/DepositRequestValidationTest.php`

---

## A. Validasi StoreDepositRequest

### WB-DEP-01: Nominal harus lebih besar dari nol

| Field | Nilai |
|-------|-------|
| **Input** | amount = 0, -100, atau non-numeric |
| **Harapan** | Validasi gagal, rule `gt:0` |
| **Area uji** | `StoreDepositRequest::rules()['amount']` |

### WB-DEP-02: loan_id wajib jika type = wajib

| Field | Nilai |
|-------|-------|
| **Input** | type = `wajib`, loan_id = null |
| **Harapan** | Validasi gagal, rule `required_if:type,wajib` |
| **Area uji** | `StoreDepositRequest` |

### WB-DEP-03: loan_id opsional untuk type sukarela

| Field | Nilai |
|-------|-------|
| **Input** | type = `sukarela`, loan_id = null, customer_id valid |
| **Harapan** | Validasi lolos (asumsi field lain valid) |
| **Area uji** | `StoreDepositRequest` |

### WB-DEP-04: customer_id wajib

| Field | Nilai |
|-------|-------|
| **Input** | Tanpa customer_id |
| **Harapan** | Validasi gagal |
| **Area uji** | `StoreDepositRequest` |

### WB-DEP-05: type wajib diisi

| Field | Nilai |
|-------|-------|
| **Input** | Tanpa field type |
| **Harapan** | Validasi gagal |
| **Area uji** | `StoreDepositRequest` |

---

## B. Validasi UpdateDepositRequest

### WB-DEP-06: Field saldo wajib saat update

| Field | Nilai |
|-------|-------|
| **Input** | Update tanpa `current_balance` atau `previous_balance` |
| **Harapan** | Validasi gagal |
| **Area uji** | `UpdateDepositRequest::rules()` |

### WB-DEP-07: amount update harus > 0

| Field | Nilai |
|-------|-------|
| **Input** | amount = 0 |
| **Harapan** | Validasi gagal |
| **Area uji** | `UpdateDepositRequest` |

---

## C. Logika Saldo (DepositController)

### WB-DEP-08: Perhitungan saldo setor simpanan

| Field | Nilai |
|-------|-------|
| **Rumus** | `current_balance = previous_balance + amount` |
| **Precondition** | Deposit sebelumnya dengan current_balance = 500.000 |
| **Input** | Setor 200.000 |
| **Harapan** | current_balance = 700.000 |
| **Area uji** | `DepositController::store()` baris 131–132 |

### WB-DEP-09: previous_balance default nol

| Field | Nilai |
|-------|-------|
| **Precondition** | Nasabah belum pernah setor tipe tersebut |
| **Input** | Setor pertama 100.000 |
| **Harapan** | previous_balance = 0, current_balance = 100.000 |
| **Area uji** | `$simpanan->current_balance ?? 0` |

---

## D. Logika Penarikan (WithdrawalController)

### WB-DEP-10: Perhitungan saldo penarikan

| Field | Nilai |
|-------|-------|
| **Rumus** | `current_balance = previous_balance - amount` |
| **Precondition** | previous_balance = 1.000.000 |
| **Input** | Penarikan 300.000 |
| **Harapan** | current_balance = 700.000 |
| **Area uji** | `WithdrawalController::store()` |

---

## E. Enum Tipe Deposit

| Nilai `type` | Penggunaan |
|--------------|------------|
| `pokok` | Simpanan pokok |
| `sukarela` | Simpanan sukarela |
| `wajib` | Angsuran pinjaman / pembayaran wajib |
| `penarikan` | Record penarikan |

Verifikasi enum di migration sesuai dengan query di controller (`where('type', 'wajib')`, dll.).

---

## F. Trigger paidLoan

### WB-DEP-11: paidLoan dipanggil saat simpanan wajib

| Field | Nilai |
|-------|-------|
| **Kondisi** | `$request->type == 'wajib'` AND `$request->loan_id` terisi AND ada deposit sebelumnya |
| **Harapan** | `LoanTrait::paidLoan()` dieksekusi |
| **Area uji** | `DepositController::store()` baris 134–136 |

### WB-DEP-12: paidLoan tidak dipanggil untuk sukarela

| Field | Nilai |
|-------|-------|
| **Input** | type = `sukarela` |
| **Harapan** | `paidLoan()` tidak dipanggil |
| **Area uji** | `DepositController::store()` |
