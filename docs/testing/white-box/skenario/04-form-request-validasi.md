# Skenario 04 — Form Request Validasi

Pengujian unit aturan validasi Laravel Form Request di seluruh modul.

**Referensi kode:** `app/Http/Requests/*.php`

**Test script:** `tests/Unit/Deposit/DepositRequestValidationTest.php`, `tests/Unit/Loan/LoanRequestValidationTest.php`

---

## A. Pinjaman

| ID | Request | Field | Rule | Input invalid | Harapan |
|----|---------|-------|------|---------------|---------|
| WB-REQ-01 | StoreLoanRequest | amount | gt:0 | 0 | Fail |
| WB-REQ-02 | StoreLoanRequest | installment | gt:0 | -1 | Fail |
| WB-REQ-03 | StoreLoanRequest | return_amount | gt:0 | 0 | Fail |
| WB-REQ-04 | StoreLoanRequest | value | gt:amount | value ≤ amount | Fail |
| WB-REQ-05 | UpdateLoanRequest | value | gte:amount | value < amount | Fail |

---

## B. Simpanan & Pembayaran

| ID | Request | Field | Rule | Input invalid | Harapan |
|----|---------|-------|------|---------------|---------|
| WB-REQ-10 | StoreDepositRequest | amount | gt:0 | 0 | Fail |
| WB-REQ-11 | StoreDepositRequest | loan_id | required_if:type,wajib | wajib tanpa loan_id | Fail |
| WB-REQ-12 | UpdateDepositRequest | current_balance | required | missing | Fail |
| WB-REQ-13 | UpdateDepositRequest | previous_balance | required | missing | Fail |

---

## C. Nasabah

| ID | Request | Field | Rule | Referensi |
|----|---------|-------|------|-----------|
| WB-REQ-20 | StoreCustomerRequest | amount | gt:0 | Simpanan awal nasabah |
| WB-REQ-21 | StoreCustomerRequest | nik | unique | Duplikasi NIK |

---

## D. Karyawan

| ID | Request | Field | Rule | Referensi |
|----|---------|-------|------|-----------|
| WB-REQ-30 | StoreUserRequest | role | in:manager,teller,collector | Role invalid |
| WB-REQ-31 | StoreUserRequest | username | unique | Username duplikat |
| WB-REQ-32 | StoreUserRequest | phone | unique | Telepon duplikat |

---

## E. Kolektor

| ID | Request | Field | Rule | Referensi |
|----|---------|-------|------|-----------|
| WB-REQ-40 | StoreVisitRequest | remaining_amount | gte:0 | Sisa negatif |
| WB-REQ-41 | StoreForeclosureRequest | collateral_amount | gte:0 | Nominal jaminan |
| WB-REQ-42 | StoreForeclosureRequest | return_amount | gte:0 | Pengembalian |

---

## F. Metode Pengujian Unit

```php
// Pola standar validasi Form Request
$validator = Validator::make($payload, (new StoreDepositRequest())->rules());
$this->assertTrue($validator->passes()); // atau assertFalse + assertArrayHasKey
```

### WB-REQ-50: authorize() selalu true (Form Request)

| Field | Nilai |
|-------|-------|
| **Observasi** | Semua Store/Update Request mengembalikan `authorize(): true` |
| **Implikasi** | Autorisasi role tidak di Form Request — uji black-box/RBAC terpisah |
| **Area uji** | `authorize()` method tiap Request class |

---

## G. Checklist Coverage Form Request

| File | Diuji |
|------|:-----:|
| StoreLoanRequest | ☐ |
| UpdateLoanRequest | ☐ |
| StoreDepositRequest | ☐ |
| UpdateDepositRequest | ☐ |
| StoreCustomerRequest | ☐ |
| StoreUserRequest | ☐ |
| StoreVisitRequest | ☐ |
| StoreForeclosureRequest | ☐ |
| UpdateProfileRequest | ☐ |
