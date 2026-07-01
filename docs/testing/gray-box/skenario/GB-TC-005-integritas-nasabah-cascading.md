# GB-TC-005 — Cascade Delete Nasabah & Data Terkait
## Gray-Box Test Case | Modul: Nasabah (Customer)

---

| Field | Detail |
|---|---|
| **ID Kasus Uji** | GB-TC-005 |
| **Judul** | Verifikasi Cascade Delete: Hapus Nasabah dan Dampaknya ke Semua Tabel Terkait |
| **Modul** | Manajemen Nasabah (`CustomerController`) |
| **Prioritas** | 🟠 Sedang |
| **Metode** | Gray-Box (cascade constraint inspection + orphan record detection) |
| **Tanggal Dibuat** | Juli 2026 |
| **Status** | Belum Diuji |

---

## Pengetahuan Internal yang Digunakan

### Relasi Cascade dari `customers`

Semua tabel yang berelasi ke `customers` menggunakan `cascadeOnDelete()`:

```php
// Dari semua migrasi yang mengacu customers:
$table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete()->cascadeOnUpdate();
```

| Tabel | Relasi | Cascade |
|---|---|---|
| `collaterals` | `customer_id → customers.id` | DELETE CASCADE |
| `deposits` | `customer_id → customers.id` | DELETE CASCADE |
| `loans` | `customer_id → customers.id` | DELETE CASCADE |
| `foreclosures` | `customer_id → customers.id` | DELETE CASCADE |
| `visits` | `customer_id → customers.id` | DELETE CASCADE |

### Cascade Berantai (Chain Cascade)

```
customers (hapus)
    │
    ├──► collaterals (cascade delete)
    │         │
    │         └──► loans (cascade delete, karena FK collateral_id)
    │                   │
    │                   ├──► deposits (cascade delete, karena FK loan_id)
    │                   └──► foreclosures (cascade delete, karena FK loan_id)
    │
    ├──► deposits (cascade delete langsung, karena FK customer_id)
    ├──► loans (cascade delete langsung, karena FK customer_id)
    ├──► foreclosures (cascade delete langsung, karena FK customer_id)
    └──► visits (cascade delete)
```

> ⚠️ **Perhatian**: `deposits` dan `foreclosures` bisa ter-cascade-delete **dua kali** — sekali dari `customer_id` cascade, sekali lagi dari `loan_id` cascade saat loan dihapus. MySQL harus menangani ini tanpa error.

---

## Prasyarat (Preconditions)

- [ ] **PENTING**: Gunakan nasabah data uji / dummy — **JANGAN hapus nasabah data real**
- [ ] Buat nasabah baru khusus untuk pengujian ini
- [ ] Pastikan nasabah uji memiliki: Simpanan, Pinjaman (dengan jaminan), Cicilan, Kunjungan

```sql
-- Catat semua data nasabah yang akan dihapus SEBELUM pengujian
SET @customer_id = [ID_NASABAH_UJI];

SELECT 
    (SELECT COUNT(*) FROM deposits WHERE customer_id = @customer_id) AS deposits_count,
    (SELECT COUNT(*) FROM loans WHERE customer_id = @customer_id) AS loans_count,
    (SELECT COUNT(*) FROM collaterals WHERE customer_id = @customer_id) AS collaterals_count,
    (SELECT COUNT(*) FROM foreclosures WHERE customer_id = @customer_id) AS foreclosures_count,
    (SELECT COUNT(*) FROM visits WHERE customer_id = @customer_id) AS visits_count;
```

---

## Skenario Pengujian

---

### Skenario 1: Hapus Nasabah — Verifikasi Cascade ke Semua Tabel

**Tujuan**: Memastikan penghapusan nasabah menghapus semua data terkait tanpa meninggalkan orphan records.

#### Langkah-Langkah

| No | Aksi | Verifikasi |
|---|---|---|
| 1 | Buat nasabah uji baru dengan data lengkap | — |
| 2 | Tambahkan simpanan, pinjaman, cicilan, kunjungan untuk nasabah ini | — |
| 3 | Catat semua ID data terkait nasabah | Query baseline di atas |
| 4 | Hapus nasabah melalui UI | — |
| 5 | Verifikasi semua data terkait terhapus | Query verifikasi di bawah |

#### Query Verifikasi Pasca-Hapus

```sql
-- Verifikasi tidak ada orphan records
SET @customer_id = [ID_NASABAH_YANG_DIHAPUS];

-- Ini semua harus mengembalikan 0
SELECT 
    'deposits'     AS tabel, COUNT(*) AS sisa_record FROM deposits     WHERE customer_id = @customer_id
UNION ALL
SELECT 
    'loans'        AS tabel, COUNT(*) AS sisa_record FROM loans        WHERE customer_id = @customer_id
UNION ALL
SELECT 
    'collaterals'  AS tabel, COUNT(*) AS sisa_record FROM collaterals  WHERE customer_id = @customer_id
UNION ALL
SELECT 
    'foreclosures' AS tabel, COUNT(*) AS sisa_record FROM foreclosures WHERE customer_id = @customer_id
UNION ALL
SELECT 
    'visits'       AS tabel, COUNT(*) AS sisa_record FROM visits       WHERE customer_id = @customer_id
UNION ALL
SELECT 
    'customers'    AS tabel, COUNT(*) AS sisa_record FROM customers    WHERE id = @customer_id;
```

#### Hasil yang Diharapkan

Semua query harus mengembalikan `sisa_record = 0`.

**Status**: ☐ PASS &nbsp;&nbsp; ☐ FAIL &nbsp;&nbsp; ☐ BLOCKED

**Catatan**:

---

### Skenario 2: Verifikasi Cascade Berantai (loans → deposits)

**Tujuan**: Memastikan ketika customer dihapus → loans dihapus → deposits yang ber-FK ke loans tersebut juga ikut terhapus.

#### Query Investigasi Pre-Delete

```sql
-- Catat deposits yang terhubung ke loans nasabah (bukan langsung ke customer)
SET @customer_id = [ID_NASABAH_UJI];

SELECT 
    d.id AS deposit_id,
    d.customer_id,
    d.loan_id,
    l.id AS loan_id_di_loans
FROM deposits d
JOIN loans l ON l.id = d.loan_id
WHERE d.customer_id = @customer_id
   OR l.customer_id = @customer_id;
```

#### Setelah Delete Nasabah

```sql
-- Verifikasi tidak ada deposits orphan dari loan yang sudah dihapus
SELECT d.* 
FROM deposits d
LEFT JOIN customers c ON c.id = d.customer_id
LEFT JOIN loans l ON l.id = d.loan_id
WHERE d.customer_id = [ID] 
   OR (d.loan_id IS NOT NULL AND l.id IS NULL);  -- Orphan: loan sudah dihapus
```

**Status**: ☐ PASS &nbsp;&nbsp; ☐ FAIL &nbsp;&nbsp; ☐ BLOCKED

**Catatan**:

---

### Skenario 3: Audit Global Orphan Records

**Tujuan**: Deteksi orphan records di seluruh database (bukan hanya untuk satu nasabah).

#### Query Audit Lengkap

```sql
-- Deteksi orphan deposits (customer sudah dihapus)
SELECT 'orphan_deposits_no_customer' AS jenis, COUNT(*) AS jumlah
FROM deposits d
LEFT JOIN customers c ON c.id = d.customer_id
WHERE c.id IS NULL

UNION ALL

-- Deteksi orphan deposits (loan sudah dihapus, tapi loan_id tidak null)
SELECT 'orphan_deposits_no_loan', COUNT(*)
FROM deposits d
LEFT JOIN loans l ON l.id = d.loan_id
WHERE d.loan_id IS NOT NULL AND l.id IS NULL

UNION ALL

-- Deteksi orphan loans (customer sudah dihapus)
SELECT 'orphan_loans_no_customer', COUNT(*)
FROM loans l
LEFT JOIN customers c ON c.id = l.customer_id
WHERE c.id IS NULL

UNION ALL

-- Deteksi orphan loans (collateral sudah dihapus)
SELECT 'orphan_loans_no_collateral', COUNT(*)
FROM loans l
LEFT JOIN collaterals co ON co.id = l.collateral_id
WHERE co.id IS NULL

UNION ALL

-- Deteksi orphan collaterals (customer sudah dihapus)
SELECT 'orphan_collaterals_no_customer', COUNT(*)
FROM collaterals co
LEFT JOIN customers c ON c.id = co.customer_id
WHERE c.id IS NULL

UNION ALL

-- Deteksi orphan foreclosures (customer sudah dihapus)
SELECT 'orphan_foreclosures_no_customer', COUNT(*)
FROM foreclosures f
LEFT JOIN customers c ON c.id = f.customer_id
WHERE c.id IS NULL

UNION ALL

-- Deteksi orphan visits (customer sudah dihapus)
SELECT 'orphan_visits_no_customer', COUNT(*)
FROM visits v
LEFT JOIN customers c ON c.id = v.customer_id
WHERE c.id IS NULL;
```

> Semua baris harus mengembalikan `jumlah = 0`.

**Status**: ☐ PASS &nbsp;&nbsp; ☐ FAIL &nbsp;&nbsp; ☐ BLOCKED

**Catatan**:

---

### Skenario 4: Edit Data Nasabah — Cascade Update Propagasi

**Tujuan**: Memastikan update `customers.id` (jika pernah terjadi) dipropagasi ke semua tabel terkait via `cascadeOnUpdate()`.

> Catatan: Dalam praktik normal Laravel, `id` primary key tidak pernah diupdate. Namun, `cascadeOnUpdate()` penting untuk kasus edge migrasi. Skenario ini berfokus pada update field lain (bukan PK).

#### Langkah-Langkah

| No | Aksi | Verifikasi |
|---|---|---|
| 1 | Edit data nasabah (nama, alamat, status) | — |
| 2 | Submit | — |
| 3 | Verifikasi perubahan tersimpan | `SELECT name, address, status FROM customers WHERE id=[ID]` |
| 4 | Verifikasi relasi masih valid | `SELECT COUNT(*) FROM loans WHERE customer_id=[ID]` → sama |

**Status**: ☐ PASS &nbsp;&nbsp; ☐ FAIL &nbsp;&nbsp; ☐ BLOCKED

**Catatan**:

---

## Ringkasan Hasil

| Skenario | Status | Tanggal Uji | Penguji |
|---|---|---|---|
| Skenario 1: Cascade ke Semua Tabel | — | — | — |
| Skenario 2: Cascade Berantai | — | — | — |
| Skenario 3: Audit Global Orphan | — | — | — |
| Skenario 4: Cascade Update | — | — | — |

**Kesimpulan Keseluruhan**: —
