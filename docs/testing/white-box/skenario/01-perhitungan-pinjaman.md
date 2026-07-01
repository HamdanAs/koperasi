# Skenario 01 — Perhitungan Pinjaman

Pengujian logika perhitungan angsuran dan total pengembalian pinjaman.

**Referensi kode:**
- `resources/views/pages/transaction/loan/create.blade.php` (fungsi JS `setInstallment`, `setReturnAmount`)
- `resources/views/pages/transaction/loan/edit.blade.php`
- `app/Http/Requests/StoreLoanRequest.php`
- `app/Http/Requests/UpdateLoanRequest.php`
- `database/migrations/2022_06_18_164213_create_loans_table.php`

**Test script:** `tests/Unit/Loan/InstallmentCalculationTest.php`, `tests/Unit/Loan/LoanRequestValidationTest.php`

---

## A. Rumus Angsuran (Spesifikasi Bisnis)

### WB-LOAN-01: Perhitungan angsuran per bulan

| Field | Nilai |
|-------|-------|
| **Rumus** | `installment = int(amount / period)` |
| **Input** | amount = 1.200.000, period = 12 |
| **Harapan** | installment = 100.000 |
| **Area uji** | Spesifikasi JS `setInstallment()` |

### WB-LOAN-02: Total pengembalian pinjaman

| Field | Nilai |
|-------|-------|
| **Rumus** | `return_amount = period × installment` |
| **Input** | period = 12, installment = 100.000 |
| **Harapan** | return_amount = 1.200.000 |
| **Area uji** | Spesifikasi JS `setReturnAmount()` |

### WB-LOAN-03: Pembagian integer (truncation)

| Field | Nilai |
|-------|-------|
| **Input** | amount = 1.000.000, period = 3 |
| **Harapan** | installment = 333.333 (parseInt → 333333) — verifikasi perilaku `parseInt` |
| **Catatan** | `parseInt(1000000/3)` = 333333; dokumentasikan pembulatan ke bawah |

### WB-LOAN-04: Selisih return_amount vs amount akibat rounding

| Field | Nilai |
|-------|-------|
| **Input** | amount = 1.000.000, period = 3 |
| **Harapan** | return_amount = 999.999 (333333 × 3) ≠ amount asli |
| **Area uji** | Deteksi inkonsistensi bisnis potensial |

---

## B. Validasi StoreLoanRequest

### WB-LOAN-05: Nominal pinjaman harus > 0

| Field | Nilai |
|-------|-------|
| **Input** | amount = 0 atau negatif |
| **Harapan** | Validasi gagal, rule `gt:0` |
| **Area uji** | `StoreLoanRequest::rules()` |

### WB-LOAN-06: Nilai jaminan harus > nominal pinjaman

| Field | Nilai |
|-------|-------|
| **Input** | amount = 5.000.000, value = 4.000.000 |
| **Harapan** | Validasi gagal, rule `gt:amount` pada field `value` |
| **Area uji** | `StoreLoanRequest::rules()` |

### WB-LOAN-07: Jangka waktu harus > 0

| Field | Nilai |
|-------|-------|
| **Input** | period = 0 |
| **Harapan** | Validasi gagal |
| **Area uji** | `StoreLoanRequest::rules()` |

### WB-LOAN-08: Payload pinjaman valid lolos validasi

| Field | Nilai |
|-------|-------|
| **Input** | Semua field wajib terisi dengan nilai valid |
| **Harapan** | Validator tidak mengembalikan error |
| **Area uji** | `LoanRequestValidationTest` |

### WB-LOAN-09: Field jaminan wajib diisi

| Field | Nilai |
|-------|-------|
| **Input** | Tanpa `name`, `value`, `description` |
| **Harapan** | Validasi gagal pada field collateral |
| **Area uji** | `StoreLoanRequest` |

### WB-LOAN-10: customer_id wajib

| Field | Nilai |
|-------|-------|
| **Input** | Tanpa `customer_id` |
| **Harapan** | Validasi gagal |
| **Area uji** | `StoreLoanRequest` |

---

## C. Struktur Data Pinjaman (Migration)

### WB-LOAN-11: Kolom tabel loans

| Kolom | Tipe | Default | Keterangan |
|-------|------|---------|------------|
| `period` | unsignedSmallInteger | 12 | Jangka waktu (bulan) |
| `amount` | unsignedInteger | 0 | Nominal pinjaman |
| `installment` | unsignedInteger | 0 | Cicilan |
| `return_amount` | unsignedInteger | 0 | Total pengembalian |
| `paid` | unsignedInteger | 0 | Total terbayar (migration terpisah) |

Verifikasi via migrasi dan model `App\Models\Loan`.

---

## D. Catatan Implementasi

| Temuan | Dampak white-box |
|--------|------------------|
| Rumus bunga di frontend (JS) | Backend tidak re-validasi konsistensi `return_amount = period × installment` |
| Tidak ada model method kalkulasi | Unit test memegang spesifikasi bisnis sebagai kontrak |
| `Loan::create()` menerima input langsung | Validasi hanya via Form Request |

**Rekomendasi teknis (opsional):** Ekstrak kalkulasi ke service PHP dan uji di satu tempat.
