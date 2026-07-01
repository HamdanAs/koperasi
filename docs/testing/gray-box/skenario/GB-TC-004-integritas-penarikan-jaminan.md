# GB-TC-004 — Integritas Data Penarikan Jaminan
## Gray-Box Test Case | Modul: Penarikan Jaminan (Foreclosure)

---

| Field | Detail |
|---|---|
| **ID Kasus Uji** | GB-TC-004 |
| **Judul** | Integritas Data & Perubahan Status Nasabah saat Penarikan Jaminan |
| **Modul** | Kolektor — Penarikan Jaminan (`ForeclosureController`) |
| **Prioritas** | 🟠 Sedang |
| **Metode** | Gray-Box (multi-tabel transaction + status update) |
| **Tanggal Dibuat** | Juli 2026 |
| **Status** | Belum Diuji |

---

## Pengetahuan Internal yang Digunakan

**Controller**: `app/Http/Controllers/ForeclosureController.php`

Logika kritis yang diuji (`store()` — baris 130-141):
```php
DB::beginTransaction();

// Langkah 1: Ubah status nasabah menjadi blacklist
Customer::find($request->customer_id)->update(['status' => 'blacklist']);

// Langkah 2: Buat record penarikan jaminan
Foreclosure::create($request->only([
    'date', 'collateral_amount', 'remaining_amount', 
    'return_amount', 'customer_id', 'loan_id', 'collateral_id'
]));

DB::commit();
```

**Relasi Database** (`foreclosures`):
```
foreclosures.customer_id   → customers.id   (FK, CASCADE)
foreclosures.loan_id       → loans.id        (FK, CASCADE)
foreclosures.collateral_id → collaterals.id  (FK, CASCADE)
```

---

## Prasyarat (Preconditions)

- [ ] Ada nasabah dengan status `active` yang memiliki pinjaman aktif dan jaminan
- [ ] Pinjaman nasabah tersebut harus sudah ada di tabel `loans`
- [ ] Jaminan nasabah tersebut harus sudah ada di tabel `collaterals`

```sql
-- Identifikasi kandidat nasabah untuk uji penarikan jaminan
SELECT 
    cu.id AS customer_id,
    cu.name,
    cu.status,
    l.id AS loan_id,
    l.amount,
    l.paid,
    co.id AS collateral_id,
    co.name AS collateral_name,
    co.value AS collateral_value
FROM customers cu
JOIN loans l ON l.customer_id = cu.id
JOIN collaterals co ON co.id = l.collateral_id
WHERE cu.status = 'active'
LIMIT 5;
```

---

## Skenario Pengujian

---

### Skenario 1: Penarikan Jaminan Berhasil — Verifikasi Atomicity

**Tujuan**: Memastikan penarikan jaminan menciptakan record `foreclosures` DAN mengubah status nasabah menjadi `blacklist` secara atomik.

#### Langkah-Langkah

| No | Aksi di UI | Verifikasi DB |
|---|---|---|
| 1 | Navigasi ke **Kolektor → Penarikan Jaminan → Tambah** | — |
| 2 | Pilih nasabah (status harus `active`) | `SELECT status FROM customers WHERE id=[ID]` → `active` |
| 3 | Pilih pinjaman nasabah tersebut | — |
| 4 | Isi tanggal, nilai jaminan, sisa hutang, nilai kembalian | — |
| 5 | Submit form | — |
| 6 | — | `SELECT status FROM customers WHERE id=[ID]` → harus `blacklist` |
| 7 | — | `SELECT * FROM foreclosures ORDER BY id DESC LIMIT 1` |

#### Data Uji

| Field | Nilai |
|---|---|
| Nasabah | Nasabah aktif dengan pinjaman |
| Tanggal | Hari ini |
| Nilai Jaminan | `15000000` |
| Sisa Hutang | `3000000` |
| Nilai Kembalian | `12000000` |

#### Hasil yang Diharapkan

| Verifikasi | Nilai yang Diharapkan |
|---|---|
| `customers.status` | `blacklist` |
| Record `foreclosures` baru | Ada, dengan semua field sesuai input |
| `foreclosures.customer_id` | ID nasabah yang dipilih |
| `foreclosures.loan_id` | ID pinjaman yang dipilih |
| `foreclosures.collateral_id` | ID jaminan yang sesuai |

**Status**: ☐ PASS &nbsp;&nbsp; ☐ FAIL &nbsp;&nbsp; ☐ BLOCKED

**Catatan**:

---

### Skenario 2: Verifikasi Integritas 3-Way FK pada `foreclosures`

**Tujuan**: Memastikan `foreclosures` menyimpan referensi yang valid ke `customers`, `loans`, dan `collaterals` sekaligus.

#### Query Verifikasi

```sql
-- Verifikasi bahwa semua foreign key di foreclosures valid
SELECT 
    f.id AS foreclosure_id,
    f.date,
    f.collateral_amount,
    f.remaining_amount,
    f.return_amount,
    -- Customer FK
    f.customer_id,
    cu.name AS customer_name,
    cu.status AS customer_status,
    -- Loan FK
    f.loan_id,
    l.amount AS loan_amount,
    -- Collateral FK
    f.collateral_id,
    co.name AS collateral_name,
    co.value AS collateral_value,
    -- Validasi konsistensi
    CASE 
        WHEN f.collateral_amount <= co.value THEN '✅ OK'
        ELSE '⚠️ collateral_amount > collateral.value'
    END AS amount_check,
    CASE
        WHEN cu.id = l.customer_id THEN '✅ OK'
        ELSE '❌ Mismatch: nasabah foreclosure ≠ nasabah pinjaman'
    END AS customer_consistency
FROM foreclosures f
LEFT JOIN customers cu ON cu.id = f.customer_id
LEFT JOIN loans l ON l.id = f.loan_id
LEFT JOIN collaterals co ON co.id = f.collateral_id
ORDER BY f.id DESC
LIMIT 10;
```

**Status**: ☐ PASS &nbsp;&nbsp; ☐ FAIL &nbsp;&nbsp; ☐ BLOCKED

**Catatan**:

---

### Skenario 3: Nasabah Blacklist Tidak Bisa Dapat Pinjaman Baru

**Tujuan**: Memastikan nasabah yang sudah berstatus `blacklist` tidak dapat membuat pinjaman baru (validasi di UI dan logika sistem).

#### Langkah-Langkah

| No | Aksi | Verifikasi |
|---|---|---|
| 1 | Navigasi ke **Transaksi → Pinjaman → Tambah** | — |
| 2 | Buka dropdown nasabah | Nasabah `blacklist` seharusnya **tidak muncul** |
| 3 | — | `SELECT * FROM customers WHERE status='active'` — hanya ini yang tampil di form |

> **Referensi kode**: `LoanController::create()` menggunakan `Customer::where('status', 'active')->get()` — nasabah blacklist tidak ditampilkan.

**Status**: ☐ PASS &nbsp;&nbsp; ☐ FAIL &nbsp;&nbsp; ☐ BLOCKED

**Catatan**:

---

### Skenario 4: Hapus Data Penarikan Jaminan

**Tujuan**: Memastikan penghapusan penarikan jaminan hanya menghapus record `foreclosures` tanpa memulihkan status nasabah secara otomatis.

> ⚠️ **Perhatian**: Berdasarkan kode `destroy()`, tidak ada logika untuk mengembalikan `customers.status` ke `active` saat data penarikan jaminan dihapus.

#### Langkah-Langkah

| No | Aksi | Verifikasi |
|---|---|---|
| 1 | Catat ID nasabah dari penarikan jaminan | — |
| 2 | Hapus data penarikan jaminan dari UI | — |
| 3 | Cek record foreclosures | `SELECT * FROM foreclosures WHERE id=[ID]` → harus **tidak ada** |
| 4 | Cek status nasabah | `SELECT status FROM customers WHERE id=[ID_NASABAH]` → masih `blacklist`? |

#### Hasil yang Diharapkan

| Verifikasi | Nilai yang Diharapkan | Catatan |
|---|---|---|
| Record `foreclosures` | Terhapus | ✅ |
| `customers.status` | Tetap `blacklist` | ⚠️ Perlu keputusan bisnis: apakah ini perilaku yang benar? |

**Status**: ☐ PASS &nbsp;&nbsp; ☐ FAIL &nbsp;&nbsp; ☐ BLOCKED

**Catatan**:

---

### Skenario 5: Kalkulasi Nilai Kembalian Nasabah

**Tujuan**: Memverifikasi kalkulasi `return_amount` yang ditampilkan di UI konsisten dengan rumus yang diharapkan.

**Rumus yang digunakan di UI** (dari `ForeclosureController::index()`):
```php
// total_amount = collateral.value - remaining_amount
return 'Rp' . number_format($row->collateral->value - $row->remaining_amount, 2, ',', '.');
```

#### Query Verifikasi

```sql
-- Verifikasi kalkulasi total_amount di setiap penarikan jaminan
SELECT 
    f.id,
    co.value AS collateral_value,
    f.remaining_amount,
    (co.value - f.remaining_amount) AS expected_total,
    f.return_amount,
    CASE 
        WHEN f.return_amount <= (co.value - f.remaining_amount) THEN '✅ Logis'
        ELSE '⚠️ return_amount > (nilai jaminan - sisa hutang)'
    END AS logic_check
FROM foreclosures f
JOIN collaterals co ON co.id = f.collateral_id;
```

**Status**: ☐ PASS &nbsp;&nbsp; ☐ FAIL &nbsp;&nbsp; ☐ BLOCKED

**Catatan**:

---

## Ringkasan Hasil

| Skenario | Status | Tanggal Uji | Penguji |
|---|---|---|---|
| Skenario 1: Atomicity Status + Record | — | — | — |
| Skenario 2: Integritas 3-Way FK | — | — | — |
| Skenario 3: Blacklist di Form Pinjaman | — | — | — |
| Skenario 4: Hapus & Status Nasabah | — | — | — |
| Skenario 5: Kalkulasi Kembalian | — | — | — |

**Kesimpulan Keseluruhan**: —
