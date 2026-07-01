# GB-TC-002 — Integritas Relasi Pinjaman & Jaminan
## Gray-Box Test Case | Modul: Pinjaman (Loan) & Jaminan (Collateral)

---

| Field | Detail |
|---|---|
| **ID Kasus Uji** | GB-TC-002 |
| **Judul** | Integritas Relasi antara Tabel `loans` dan `collaterals` saat Transaksi Pinjaman |
| **Modul** | Transaksi Pinjaman (`LoanController`) |
| **Prioritas** | 🔴 Tinggi |
| **Metode** | Gray-Box (DB Transaction inspection + relational integrity) |
| **Tanggal Dibuat** | Juli 2026 |
| **Status** | Belum Diuji |

---

## Pengetahuan Internal yang Digunakan

**Controller**: `app/Http/Controllers/LoanController.php`

Logika kritis yang diuji (`store()` — baris 115-133):
```php
DB::beginTransaction();

// Langkah 1: Buat jaminan dulu
$collateral = Collateral::create($request->only(['customer_id', 'name', 'value', 'description']));

// Langkah 2: Buat pinjaman dengan referensi ke jaminan
$data = $request->except(['name', 'value', 'description']);
$data['collateral_id'] = $collateral->id;
Loan::create($data);

DB::commit();
```

**Relasi Database**:
```
loans.collateral_id → collaterals.id (FK, cascadeOnDelete, cascadeOnUpdate)
loans.customer_id   → customers.id  (FK, cascadeOnDelete, cascadeOnUpdate)
```

---

## Prasyarat (Preconditions)

- [ ] Minimal **1 nasabah aktif** tersedia
- [ ] Akses database untuk verifikasi
- [ ] Catat baseline data sebelum pengujian:

```sql
SELECT 
    (SELECT COUNT(*) FROM loans) AS total_loans,
    (SELECT COUNT(*) FROM collaterals) AS total_collaterals;
```

---

## Skenario Pengujian

---

### Skenario 1: Buat Pinjaman Baru — Verifikasi Atomicity (2 Tabel)

**Tujuan**: Memastikan pembuatan pinjaman secara **atomik** — baik `collaterals` maupun `loans` tersimpan bersama-sama atau tidak sama sekali.

#### Langkah-Langkah

| No | Aksi di UI | Verifikasi DB |
|---|---|---|
| 1 | Navigasi ke **Transaksi → Pinjaman → Tambah Pinjaman** | — |
| 2 | Pilih nasabah aktif | — |
| 3 | Isi data jaminan: Nama jaminan, Nilai jaminan, Deskripsi | — |
| 4 | Isi data pinjaman: Jumlah, Cicilan, Jangka Waktu, Total Kembali | — |
| 5 | Klik **Simpan** | — |
| 6 | Verifikasi notifikasi sukses | — |
| 7 | — | Cek record baru di `collaterals` |
| 8 | — | Cek record baru di `loans` dengan `collateral_id` yang benar |
| 9 | — | Pastikan `loans.collateral_id` = ID yang baru dibuat di `collaterals` |

#### Data Uji

| Field | Nilai |
|---|---|
| Nasabah | Nasabah aktif |
| Nama Jaminan | `Sertifikat Tanah A` |
| Nilai Jaminan | `15000000` |
| Jumlah Pinjaman | `5000000` |
| Jangka Waktu | `12` (bulan) |
| Cicilan/Bulan | `450000` |
| Total Kembali | `5400000` |

#### Query Verifikasi

```sql
-- Verifikasi collateral baru
SELECT * FROM collaterals ORDER BY id DESC LIMIT 1;

-- Verifikasi loan baru dan relasi ke collateral
SELECT 
    l.id AS loan_id,
    l.amount,
    l.period,
    l.collateral_id,
    c.name AS collateral_name,
    c.value AS collateral_value,
    l.customer_id,
    cu.name AS customer_name
FROM loans l
JOIN collaterals c ON c.id = l.collateral_id
JOIN customers cu ON cu.id = l.customer_id
ORDER BY l.id DESC
LIMIT 1;
```

#### Hasil yang Diharapkan

| Verifikasi | Nilai yang Diharapkan |
|---|---|
| Record `collaterals` baru ada | ✅ Ada dengan data sesuai input |
| Record `loans` baru ada | ✅ Ada dengan data sesuai input |
| `loans.collateral_id` | = `id` dari `collaterals` yang baru dibuat |
| `loans.customer_id` | = ID nasabah yang dipilih |
| `collaterals.customer_id` | = ID nasabah yang sama |

**Status**: ☐ PASS &nbsp;&nbsp; ☐ FAIL &nbsp;&nbsp; ☐ BLOCKED

**Catatan**:

---

### Skenario 2: Edit Pinjaman — Sinkronisasi Update ke 2 Tabel

**Tujuan**: Memastikan edit pinjaman memperbarui baik tabel `loans` maupun tabel `collaterals` secara bersamaan.

**Referensi kode** (`update()` — baris 172-184):
```php
DB::beginTransaction();
$pinjaman->update($request->except(['name', 'value', 'description']));
$pinjaman->collateral()->update($request->only(['name', 'value', 'description']));
DB::commit();
```

#### Data Uji

| Field | Nilai Lama | Nilai Baru |
|---|---|---|
| Nama Jaminan | `Sertifikat Tanah A` | `Sertifikat Tanah B` |
| Nilai Jaminan | `15000000` | `18000000` |
| Jumlah Pinjaman | `5000000` | `5000000` (tidak berubah) |

#### Query Verifikasi

```sql
-- Sebelum edit, catat nilai saat ini
SELECT l.amount, c.name, c.value 
FROM loans l JOIN collaterals c ON c.id = l.collateral_id 
WHERE l.id = [ID_PINJAMAN];

-- Setelah edit, verifikasi perubahan
SELECT l.amount, c.name, c.value 
FROM loans l JOIN collaterals c ON c.id = l.collateral_id 
WHERE l.id = [ID_PINJAMAN];
```

#### Hasil yang Diharapkan

| Kolom | Nilai yang Diharapkan |
|---|---|
| `collaterals.name` | `Sertifikat Tanah B` |
| `collaterals.value` | `18000000` |
| `loans.amount` | Tidak berubah (`5000000`) |

**Status**: ☐ PASS &nbsp;&nbsp; ☐ FAIL &nbsp;&nbsp; ☐ BLOCKED

**Catatan**:

---

### Skenario 3: Hapus Pinjaman — Verifikasi Cascade ke Deposits

**Tujuan**: Memastikan saat pinjaman dihapus, semua deposits terkait juga terhapus (baik via manual delete maupun cascade).

**Referensi kode** (`destroy()` — baris 192-201):
```php
// ⚠️ TIDAK ADA DB::beginTransaction() di sini!
$pinjaman->deposits()->delete();  // hapus manual
$pinjaman->delete();
```

#### Prasyarat Tambahan

```sql
-- Catat deposits yang terhubung ke pinjaman ini
SELECT COUNT(*) AS deposit_count 
FROM deposits 
WHERE loan_id = [ID_PINJAMAN];
```

#### Query Verifikasi

```sql
-- Setelah hapus pinjaman, cek apakah deposits terkait juga terhapus
SELECT COUNT(*) AS orphan_deposits 
FROM deposits 
WHERE loan_id = [ID_PINJAMAN];  -- Harus = 0

-- Cek apakah loan sudah hilang
SELECT COUNT(*) FROM loans WHERE id = [ID_PINJAMAN];  -- Harus = 0

-- ⚠️ PENTING: Cek apakah collateral masih ada (seharusnya masih)
-- karena collateral dimiliki customer, bukan loan
SELECT COUNT(*) FROM collaterals WHERE id = [ID_COLLATERAL];
```

#### Hasil yang Diharapkan

| Verifikasi | Nilai yang Diharapkan |
|---|---|
| `deposits` dengan `loan_id=[ID]` | `0` (semua terhapus) |
| `loans` dengan `id=[ID]` | `0` (terhapus) |
| `collaterals` dengan `id=[ID_COLL]` | Bergantung pada cascade — **perlu dicatat** |

> ⚠️ **Titik Risiko**: Karena tidak ada `DB::beginTransaction()` di `destroy()`, jika `$pinjaman->delete()` gagal setelah `deposits()->delete()`, data deposit akan hilang permanen tanpa rollback.

**Status**: ☐ PASS &nbsp;&nbsp; ☐ FAIL &nbsp;&nbsp; ☐ BLOCKED

**Catatan**:

---

### Skenario 4: Integritas Constraint — Coba Buat Pinjaman tanpa Nasabah Valid

**Tujuan**: Memastikan foreign key constraint `loans.customer_id` mencegah pinjaman dengan nasabah tidak valid.

#### Aksi Simulasi (via SQL langsung)

```sql
-- Coba insert loan dengan customer_id yang tidak ada
-- Ini HARUS gagal karena FK constraint
INSERT INTO loans (period, amount, installment, return_amount, customer_id, collateral_id)
VALUES (12, 5000000, 450000, 5400000, 99999, 1);
-- Expected: ERROR 1452 (23000): Cannot add or update a child row: a foreign key constraint fails
```

#### Hasil yang Diharapkan

MySQL menolak INSERT dengan error FK constraint.

**Status**: ☐ PASS &nbsp;&nbsp; ☐ FAIL &nbsp;&nbsp; ☐ BLOCKED

**Catatan**:

---

### Skenario 5: Verifikasi Kode Transaksi (Format PI-XXXXX)

**Tujuan**: Memastikan kode transaksi yang ditampilkan di UI konsisten dengan ID di database.

#### Langkah-Langkah

| No | Aksi | Verifikasi |
|---|---|---|
| 1 | Buka halaman daftar pinjaman | Catat kode transaksi yang tampil (misal: `PI-00001`) |
| 2 | — | `SELECT id FROM loans ORDER BY id DESC` |
| 3 | Bandingkan ID dari DB dengan kode di UI | Kode `PI-XXXXX` harus = format `PI-` + `sprintf('%05d', id)` |

**Status**: ☐ PASS &nbsp;&nbsp; ☐ FAIL &nbsp;&nbsp; ☐ BLOCKED

**Catatan**:

---

## Ringkasan Hasil

| Skenario | Status | Tanggal Uji | Penguji |
|---|---|---|---|
| Skenario 1: Atomicity 2 Tabel | — | — | — |
| Skenario 2: Edit — Update 2 Tabel | — | — | — |
| Skenario 3: Hapus — Cascade Deposits | — | — | — |
| Skenario 4: FK Constraint | — | — | — |
| Skenario 5: Konsistensi Kode Transaksi | — | — | — |

**Kesimpulan Keseluruhan**: —

---

## Temuan / Defect

| ID Defect | Deskripsi | Skenario | Severity |
|---|---|---|---|
| — | — | — | — |
