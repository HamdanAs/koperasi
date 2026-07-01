# Struktur Test Script

Pemetaan antara skenario dokumentasi white-box dan file PHPUnit di repository.

## Diagram Organisasi

```
tests/
├── TestCase.php
├── CreatesApplication.php
├── Unit/                          ← Logika terisolasi, tanpa HTTP penuh
│   ├── Loan/
│   │   ├── InstallmentCalculationTest.php    → WB-LOAN-01 s/d 04
│   │   └── LoanTraitTest.php                 → WB-TRAIT-01 s/d 03
│   ├── Deposit/
│   │   └── DepositRequestValidationTest.php  → WB-DEP-01 s/d 06, WB-REQ-*
│   ├── Loan/
│   │   └── LoanRequestValidationTest.php     → WB-LOAN-05 s/d 10
│   └── Controller/
│       └── TransactionCodeTest.php           → WB-CTRL-01 s/d 02
└── Feature/                       ← HTTP, route, middleware
    ├── Routes/
    │   └── WebRoutesTest.php                 → WB-ROUTE-01 s/d 08
    └── Auth/
        └── AuthenticatedAccessTest.php       → WB-ROUTE-09 s/d 11
```

## Pemetaan Skenario → Test Script

### 01 — Perhitungan Pinjaman

| ID Skenario | File Test | Method Test |
|-------------|-----------|-------------|
| WB-LOAN-01 | `InstallmentCalculationTest` | `test_installment_equals_amount_divided_by_period` |
| WB-LOAN-02 | `InstallmentCalculationTest` | `test_return_amount_equals_period_times_installment` |
| WB-LOAN-03 | `InstallmentCalculationTest` | `test_installment_uses_integer_division` |
| WB-LOAN-04 | `InstallmentCalculationTest` | `test_return_amount_may_differ_from_amount_due_to_rounding` |
| WB-LOAN-05 | `LoanRequestValidationTest` | `test_amount_must_be_greater_than_zero` |
| WB-LOAN-06 | `LoanRequestValidationTest` | `test_collateral_value_must_exceed_loan_amount` |
| WB-LOAN-07 | `LoanRequestValidationTest` | `test_period_must_be_greater_than_zero` |
| WB-LOAN-08 | `LoanRequestValidationTest` | `test_valid_loan_payload_passes_validation` |

### 02 — Validasi Simpanan

| ID Skenario | File Test | Method Test |
|-------------|-----------|-------------|
| WB-DEP-01 | `DepositRequestValidationTest` | `test_amount_must_be_greater_than_zero` |
| WB-DEP-02 | `DepositRequestValidationTest` | `test_loan_id_required_when_type_is_wajib` |
| WB-DEP-03 | `DepositRequestValidationTest` | `test_loan_id_not_required_for_sukarela` |
| WB-DEP-04 | `DepositRequestValidationTest` | `test_customer_id_is_required` |
| WB-DEP-05 | `DepositRequestValidationTest` | `test_update_requires_balance_fields` |
| WB-DEP-06 | `DepositRequestValidationTest` | `test_valid_deposit_payload_passes` |

### 03 — LoanTrait

| ID Skenario | File Test | Method Test |
|-------------|-----------|-------------|
| WB-TRAIT-01 | `LoanTraitTest` | `test_paid_loan_sums_wajib_deposits_only` |
| WB-TRAIT-02 | `LoanTraitTest` | `test_paid_loan_ignores_non_wajib_deposits` |
| WB-TRAIT-03 | `LoanTraitTest` | `test_paid_loan_updates_loan_paid_column` |

### 05 — Route & Middleware

| ID Skenario | File Test | Method Test |
|-------------|-----------|-------------|
| WB-ROUTE-01 | `WebRoutesTest` | `test_home_route_is_registered` |
| WB-ROUTE-02 | `WebRoutesTest` | `test_transaction_routes_are_registered` |
| WB-ROUTE-03 | `WebRoutesTest` | `test_collection_routes_are_registered` |
| WB-ROUTE-04 | `WebRoutesTest` | `test_resource_verbs_use_indonesian_create_path` |
| WB-ROUTE-05 | `WebRoutesTest` | `test_login_route_is_accessible` |
| WB-ROUTE-06 | `AuthenticatedAccessTest` | `test_guest_is_redirected_from_dashboard` |
| WB-ROUTE-07 | `AuthenticatedAccessTest` | `test_guest_is_redirected_from_protected_routes` |
| WB-ROUTE-08 | `AuthenticatedAccessTest` | `test_authenticated_user_can_access_dashboard` |
| WB-ROUTE-09 | `WebRoutesTest` | `test_print_routes_exist_for_modules` |

### 06 — Controller Helper

| ID Skenario | File Test | Method Test |
|-------------|-----------|-------------|
| WB-CTRL-01 | `TransactionCodeTest` | `test_build_transaction_code_format` |
| WB-CTRL-02 | `TransactionCodeTest` | `test_build_transaction_code_zero_pads_id` |

## Konvensi Penamaan

| Aturan | Contoh |
|--------|--------|
| Folder = modul/domain | `tests/Unit/Loan/` |
| File = `{Subjek}Test.php` | `LoanTraitTest.php` |
| Method = `test_{perilaku}_dalam_kondisi_{kondisi}` | `test_paid_loan_sums_wajib_deposits_only` |
| Feature test = aksi HTTP | `test_guest_is_redirected_from_dashboard` |

## Menambah Test Baru

1. Tentukan ID skenario (`WB-XXX-NN`) di file skenario terkait.
2. Buat atau perluas class test di folder yang sesuai.
3. Update tabel pemetaan di dokumen ini.
4. Jalankan `php artisan test` sebelum commit.

## Coverage Target (Rekomendasi)

| Modul | Target |
|-------|--------|
| `LoanTrait` | 100% method |
| Form Request pinjaman/simpanan | Semua rules |
| Route named (web) | Route kritikal terdaftar |
| Middleware `auth` | Guest redirect |
