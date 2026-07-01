# Skenario 03 — Status Pinjaman (LoanTrait)

Pengujian logika internal pembaruan status pembayaran pinjaman.

**Referensi kode:**
- `app/Traits/LoanTrait.php`
- `app/Http/Controllers/DepositController.php` (uses `LoanTrait`)
- `app/Http/Controllers/InstallmentController.php` (uses `LoanTrait`)
- `database/migrations/2022_06_25_062808_add_paid_column_to_loans_table.php`

**Test script:** `tests/Unit/Loan/LoanTraitTest.php`

---

## A. Method paidLoan($id)

### WB-TRAIT-01: Agregasi deposit type wajib

| Field | Nilai |
|-------|-------|
| **Logika** | `SUM(amount) FROM deposits WHERE loan_id = ? AND type = 'wajib'` |
| **Setup** | Loan id=1; deposit wajib 100.000 + 150.000 |
| **Harapan** | `loans.paid` = 250.000 |
| **Area uji** | `LoanTrait::paidLoan()` |

### WB-TRAIT-02: Deposit non-wajib diabaikan

| Field | Nilai |
|-------|-------|
| **Setup** | Loan id=1; wajib 100.000; sukarela 500.000; pokok 50.000 |
| **Harapan** | `paid` = 100.000 (hanya wajib) |
| **Area uji** | Query `where('type', 'wajib')` |

### WB-TRAIT-03: Update kolom paid pada model Loan

| Field | Nilai |
|-------|-------|
| **Precondition** | Loan dengan paid = 0 |
| **Aksi** | Panggil `paidLoan($id)` setelah deposit wajib |
| **Harapan** | Method return true; kolom `paid` terupdate di DB |
| **Area uji** | `Loan::find($id)->update(['paid' => $total_paid])` |

### WB-TRAIT-04: paidLoan dengan nol deposit wajib

| Field | Nilai |
|-------|-------|
| **Setup** | Loan tanpa deposit wajib |
| **Harapan** | `paid` = 0 |
| **Area uji** | `sum()` pada collection kosong |

### WB-TRAIT-05: paidLoan setelah hapus deposit wajib

| Field | Nilai |
|-------|-------|
| **Langkah** | 1. Buat 2 deposit wajib<br>2. Hapus satu deposit<br>3. Panggil paidLoan |
| **Harapan** | `paid` = sisa SUM deposit wajib |
| **Area uji** | `DepositController::destroy()` memanggil paidLoan |

---

## B. Integrasi Controller

### WB-TRAIT-06: DepositController memanggil paidLoan

| Trigger | Kondisi |
|---------|---------|
| `store()` | type wajib + loan_id + ada deposit sebelumnya |
| `update()` | loan_id pada request |
| `destroy()` | deposit bertype wajib dengan loan_id |

### WB-TRAIT-07: InstallmentController memanggil paidLoan

| Field | Nilai |
|-------|-------|
| **Catatan** | InstallmentController menggunakan StoreDepositRequest yang sama |
| **Harapan** | Logika paidLoan identik dengan DepositController |
| **Area uji** | `InstallmentController::store()` baris 131–132 |

---

## C. Alur Status Pinjaman (End-to-End Internal)

```
[Pinjaman dibuat] → paid = 0
       ↓
[Deposit/Pembayaran type=wajib] → paidLoan() → paid = SUM(wajib)
       ↓
[Edit/Hapus deposit wajib]       → paidLoan() → paid di-recalculate
```

### WB-TRAIT-08: paid mendekati return_amount

| Field | Nilai |
|-------|-------|
| **Input** | return_amount = 1.200.000; bayar wajib total 1.200.000 |
| **Harapan** | paid = 1.200.000 |
| **Catatan** | Tidak ada field `status` enum; status implisit dari nilai `paid` vs `return_amount` |

---

## D. Edge Case

| ID | Skenario | Harapan |
|----|----------|---------|
| WB-TRAIT-09 | paidLoan dengan loan_id tidak valid | Handle null/error — catat perilaku aktual |
| WB-TRAIT-10 | Multiple deposit wajib same loan | paid = total semua |
