# GB-TC-008 — Anomali Data Pasca Migrasi Database
## Gray-Box Test Case | Modul: Sistem (Database Migration)

---

| Field | Detail |
|---|---|
| **ID Kasus Uji** | GB-TC-008 |
| **Judul** | Verifikasi Tidak Ada Anomali Data Setelah Eksekusi Migrasi Database |
| **Modul** | Sistem / Database (`database/migrations/`) |
| **Prioritas** | 🟠 Sedang |
| **Metode** | Gray-Box (skema migrasi inspection + data integrity post-migration) |
| **Tanggal Dibuat** | Juli 2026 |
| **Status** | Belum Diuji |

---

## Pengetahuan Internal yang Digunakan

### Daftar File Migrasi

```
database/migrations/
├── 2014_10_12_000000_create_users_table.php
├── 2014_10_12_100000_create_password_resets_table.php
├── 2019_08_19_000000_create_failed_jobs_table.php
├── 2019_12_14_000001_create_personal_access_tokens_table.php
├── 2022_06_15_114258_add_gender_columns_to_users_table.php
├── 2022_06_18_000725_create_customers_table.php
├── 2022_06_18_160457_create_collaterals_table.php
├── 2022_06_18_164213_create_loans_table.php
├── 2022_06_19_094908_create_deposits_table.php
├── 2022_06_25_062808_add_paid_column_to_loans_table.php
├── 2022_06_25_112144_create_visits_table.php
└── 2022_06_25_140614_create_foreclosures_table.php
```

### Perhatian Khusus

Migrasi `2022_06_25_062808_add_paid_column_to_loans_table.php` menambahkan kolom `paid` ke tabel `loans` **setelah** data mungkin sudah ada. Ini perlu diverifikasi.

---

## Skenario Pengujian

---

### Skenario 1: Verifikasi Migrasi `php artisan migrate:fresh` Berhasil Sempurna

**Tujuan**: Memastikan semua migrasi berjalan tanpa error dari awal.

#### Langkah-Langkah

```powershell
# Jalankan di direktori proyek
cd C:\laragon\www\koperasi-swamitra-karya-bersama

# Fresh migration (hapus semua tabel dan migrate ulang)
php artisan migrate:fresh
```

#### Hasil yang Diharapkan

```
Dropped all tables successfully.
Running migrations...
✅ 2014_10_12_000000_create_users_table
✅ 2014_10_12_100000_create_password_resets_table
✅ 2019_08_19_000000_create_failed_jobs_table
✅ 2019_12_14_000001_create_personal_access_tokens_table
✅ 2022_06_15_114258_add_gender_columns_to_users_table
✅ 2022_06_18_000725_create_customers_table
✅ 2022_06_18_160457_create_collaterals_table
✅ 2022_06_18_164213_create_loans_table
✅ 2022_06_19_094908_create_deposits_table
✅ 2022_06_25_062808_add_paid_column_to_loans_table
✅ 2022_06_25_112144_create_visits_table
✅ 2022_06_25_140614_create_foreclosures_table
```

**Output aktual**:
```
[Isi setelah menjalankan perintah]
```

**Status**: ☐ PASS &nbsp;&nbsp; ☐ FAIL &nbsp;&nbsp; ☐ BLOCKED

**Catatan**:

---

### Skenario 2: Verifikasi Skema Tabel Sesuai Migrasi

**Tujuan**: Memastikan struktur tabel di database konsisten dengan definisi migrasi.

#### Query Verifikasi Skema

```sql
-- Verifikasi kolom tabel customers
DESCRIBE customers;

-- Verifikasi kolom tabel loans
DESCRIBE loans;

-- Verifikasi kolom tabel deposits  
DESCRIBE deposits;

-- Verifikasi kolom tabel collaterals
DESCRIBE collaterals;

-- Verifikasi kolom tabel foreclosures
DESCRIBE foreclosures;

-- Verifikasi semua foreign key constraints terdaftar
SELECT 
    TABLE_NAME,
    COLUMN_NAME,
    CONSTRAINT_NAME,
    REFERENCED_TABLE_NAME,
    REFERENCED_COLUMN_NAME
FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
WHERE REFERENCED_TABLE_SCHEMA = DATABASE()
ORDER BY TABLE_NAME, COLUMN_NAME;
```

#### Checklist Verifikasi Kolom Kritis

| Tabel | Kolom | Tipe | Constraint | Hasil |
|---|---|---|---|---|
| `customers` | `nik` | VARCHAR(16) | UNIQUE | ☐ |
| `customers` | `number` | VARCHAR(255) | UNIQUE | ☐ |
| `customers` | `status` | ENUM('active','blacklist') | DEFAULT 'active' | ☐ |
| `loans` | `paid` | INT UNSIGNED | DEFAULT 0 | ☐ |
| `loans` | `collateral_id` | BIGINT | FK CASCADE | ☐ |
| `deposits` | `type` | ENUM(4 nilai) | DEFAULT 'sukarela' | ☐ |
| `deposits` | `loan_id` | BIGINT | FK NULLABLE | ☐ |
| `foreclosures` | `customer_id` | BIGINT | FK CASCADE | ☐ |
| `foreclosures` | `loan_id` | BIGINT | FK CASCADE | ☐ |
| `foreclosures` | `collateral_id` | BIGINT | FK CASCADE | ☐ |

**Status**: ☐ PASS &nbsp;&nbsp; ☐ FAIL &nbsp;&nbsp; ☐ BLOCKED

**Catatan**:

---

### Skenario 3: Verifikasi Kolom `loans.paid` Pasca Migrasi Alterasi

**Tujuan**: Memastikan migrasi `add_paid_column_to_loans_table` menambahkan kolom dengan default yang benar, dan tidak ada nilai `NULL` di kolom `paid`.

```sql
-- Cek apakah ada loans dengan paid = NULL (seharusnya tidak ada karena DEFAULT 0)
SELECT COUNT(*) AS loans_with_null_paid
FROM loans
WHERE paid IS NULL;

-- Cek distribusi nilai paid
SELECT 
    MIN(paid) AS min_paid,
    MAX(paid) AS max_paid,
    AVG(paid) AS avg_paid,
    COUNT(*) AS total_loans
FROM loans;
```

#### Hasil yang Diharapkan

- `loans_with_null_paid = 0`
- `min_paid >= 0` (tidak ada nilai negatif)

**Status**: ☐ PASS &nbsp;&nbsp; ☐ FAIL &nbsp;&nbsp; ☐ BLOCKED

**Catatan**:

---

### Skenario 4: Verifikasi Impor Data dari `swamitra.sql`

**Tujuan**: Jika ada file `docs/swamitra.sql` yang digunakan untuk impor data, verifikasi bahwa impor tidak menyebabkan anomali.

#### Langkah-Langkah

```bash
# Impor file SQL
mysql -u root -p koperasi_swamitra < docs/swamitra.sql
```

#### Query Verifikasi Pasca Impor

```sql
-- Cek jumlah record per tabel setelah impor
SELECT 'customers' AS tabel, COUNT(*) AS jumlah FROM customers
UNION ALL
SELECT 'collaterals', COUNT(*) FROM collaterals
UNION ALL
SELECT 'loans', COUNT(*) FROM loans
UNION ALL
SELECT 'deposits', COUNT(*) FROM deposits
UNION ALL
SELECT 'foreclosures', COUNT(*) FROM foreclosures
UNION ALL
SELECT 'visits', COUNT(*) FROM visits;

-- Cek semua enum values valid
SELECT DISTINCT status FROM customers;  -- Harus hanya 'active' dan 'blacklist'
SELECT DISTINCT type FROM deposits;     -- Harus hanya 4 nilai yang valid
SELECT DISTINCT gender FROM customers;  -- Harus hanya 'L' dan 'P'
```

**Status**: ☐ PASS &nbsp;&nbsp; ☐ FAIL &nbsp;&nbsp; ☐ BLOCKED

**Catatan**:

---

### Skenario 5: Verifikasi `php artisan migrate:rollback` Tidak Menyebabkan Data Corruption

**Tujuan**: Memastikan proses rollback migrasi tidak merusak integritas data yang tersisa.

> ⚠️ **PERHATIAN**: Jalankan hanya di **environment pengujian**, **BUKAN** di production!

```powershell
# Rollback 1 langkah (hanya migrasi terakhir)
php artisan migrate:rollback --step=1

# Cek state database
php artisan migrate:status
```

#### Hasil yang Diharapkan

- Rollback berhasil tanpa error
- Tabel yang seharusnya dihapus (dari migrasi terakhir) sudah tidak ada
- Tabel lain tetap utuh

```sql
-- Verifikasi tabel yang tersisa sesuai dengan yang diharapkan
SHOW TABLES;
```

**Status**: ☐ PASS &nbsp;&nbsp; ☐ FAIL &nbsp;&nbsp; ☐ BLOCKED

**Catatan**:

---

### Skenario 6: Deteksi Anomali Data — Nilai Tidak Wajar

**Tujuan**: Deteksi data yang tidak wajar yang mungkin masuk dari proses migrasi/impor.

#### Query Komprehensif Deteksi Anomali

```sql
-- 1. Nasabah dengan NIK tidak valid (bukan 16 digit)
SELECT id, name, nik FROM customers WHERE LENGTH(nik) != 16;

-- 2. Pinjaman dengan amount = 0 atau installment > return_amount
SELECT id, amount, installment, return_amount FROM loans
WHERE amount = 0 OR installment > return_amount;

-- 3. Pinjaman dengan paid > return_amount (lebih bayar ekstrim)
SELECT id, amount, return_amount, paid, (paid - return_amount) AS kelebihan
FROM loans WHERE paid > return_amount;

-- 4. Deposits dengan current_balance < previous_balance (bukan penarikan)
SELECT id, type, amount, previous_balance, current_balance
FROM deposits
WHERE type != 'penarikan' AND current_balance < previous_balance;

-- 5. Collateral dengan value = 0
SELECT id, name, value, customer_id FROM collaterals WHERE value = 0;

-- 6. Foreclosure dengan return_amount > collateral_amount
SELECT id, collateral_amount, remaining_amount, return_amount
FROM foreclosures WHERE return_amount > collateral_amount;

-- 7. Customers dengan joined_at di masa depan
SELECT id, name, joined_at FROM customers WHERE joined_at > NOW();
```

> Semua query di atas idealnya mengembalikan **0 baris** (tidak ada anomali).

**Status**: ☐ PASS &nbsp;&nbsp; ☐ FAIL &nbsp;&nbsp; ☐ BLOCKED

**Catatan**:

---

## Ringkasan Hasil

| Skenario | Status | Tanggal Uji | Penguji |
|---|---|---|---|
| Skenario 1: Fresh Migration | — | — | — |
| Skenario 2: Verifikasi Skema | — | — | — |
| Skenario 3: Kolom `loans.paid` | — | — | — |
| Skenario 4: Impor `swamitra.sql` | — | — | — |
| Skenario 5: Rollback Migrasi | — | — | — |
| Skenario 6: Deteksi Anomali Data | — | — | — |

**Kesimpulan Keseluruhan**: —
