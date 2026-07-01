# 02 — Peta Relasi Database & Titik Uji Kritis
## Sistem Informasi Koperasi Swamitra Karya Bersama

---

## 1. Diagram Relasi Entitas (ERD Tekstual)

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                           KOPERASI SWAMITRA                                 │
│                          Database Relationship Map                          │
└─────────────────────────────────────────────────────────────────────────────┘

                    ┌──────────────┐
                    │   users      │
                    ├──────────────┤
                    │ id (PK)      │
                    │ name         │
                    │ email        │
                    │ role         │
                    │ gender       │
                    └──────────────┘

┌──────────────────────┐
│      customers       │◄──────────────────────────────────────┐
├──────────────────────┤                                        │
│ id (PK)              │                                        │
│ nik (UNIQUE)         │                                        │
│ name                 │                                        │
│ number (UNIQUE)      │                                        │
│ gender               │                                        │
│ birth                │                                        │
│ address              │                                        │
│ phone (UNIQUE)       │                                        │
│ last_education       │                                        │
│ profession           │                                        │
│ status               │  ◄── enum('active','blacklist')        │
│ photo                │                                        │
│ joined_at            │                                        │
└──────────┬───────────┘                                        │
           │ 1                                                  │
           │ hasMany                                            │
    ┌──────┴──────┬──────────────┬──────────────┬──────────────┤
    │             │              │              │              │
    │ N           │ N            │ N            │ N            │
    ▼             ▼              ▼              ▼              │
┌───────────┐ ┌──────────┐ ┌──────────────┐ ┌──────────┐     │
│collaterals│ │ deposits │ │ foreclosures │ │  visits  │     │
├───────────┤ ├──────────┤ ├──────────────┤ ├──────────┤     │
│id (PK)    │ │id (PK)   │ │id (PK)       │ │id (PK)   │     │
│name       │ │type      │ │date          │ │date      │     │
│value      │ │amount    │ │collateral_   │ │notes     │     │
│description│ │previous_ │ │  amount      │ │customer_ │     │
│customer_id│ │  balance │ │remaining_    │ │  id (FK) │     │
│(FK→cust.) │ │current_  │ │  amount      │ └──────────┘     │
└─────┬─────┘ │  balance │ │return_amount │                  │
      │       │customer_ │ │customer_id   │──────────────────┘
      │       │  id (FK) │ │  (FK→cust.)  │
      │       │loan_id   │ │loan_id       │
      │       │  (FK,NUL)│ │  (FK→loans)  │
      │       └────┬─────┘ │collateral_id │
      │            │       │  (FK→coll.)  │
      │            │       └──────────────┘
      │            │
      │       ┌────┴─────────────────┐
      │       │        loans         │
      │       ├──────────────────────┤
      └──────►│ id (PK)              │
      FK      │ period               │
              │ amount               │
              │ installment          │
              │ return_amount        │
              │ paid    ◄────────────│── Diupdate oleh LoanTrait
              │ customer_id (FK)     │
              │ collateral_id (FK)   │◄── FK ke collaterals
              └──────────────────────┘
```

---

## 2. Skema Tabel Lengkap

### 2.1 Tabel `customers`

| Kolom | Tipe | Constraint | Keterangan |
|---|---|---|---|
| `id` | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Primary Key |
| `nik` | VARCHAR(16) | UNIQUE, NOT NULL | Nomor Induk Kependudukan |
| `name` | VARCHAR(255) | NOT NULL | Nama nasabah |
| `number` | VARCHAR(255) | UNIQUE, NOT NULL | Nomor rekening koperasi |
| `gender` | ENUM('L','P') | DEFAULT 'L' | Jenis kelamin |
| `birth` | DATE | NULLABLE | Tanggal lahir |
| `address` | VARCHAR(255) | NULLABLE | Alamat |
| `phone` | VARCHAR(255) | UNIQUE, NULLABLE | Nomor telepon |
| `last_education` | VARCHAR(255) | NULLABLE | Pendidikan terakhir |
| `profession` | VARCHAR(255) | NULLABLE | Pekerjaan |
| `status` | ENUM('active','blacklist') | DEFAULT 'active' | Status keanggotaan |
| `photo` | VARCHAR(255) | NULLABLE | Path foto |
| `joined_at` | DATETIME | NULLABLE | Tanggal bergabung |
| `created_at` | TIMESTAMP | NULLABLE | — |
| `updated_at` | TIMESTAMP | NULLABLE | — |

### 2.2 Tabel `collaterals`

| Kolom | Tipe | Constraint | Keterangan |
|---|---|---|---|
| `id` | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Primary Key |
| `name` | VARCHAR(255) | NOT NULL | Nama jaminan |
| `value` | INT UNSIGNED | DEFAULT 0 | Nilai taksiran jaminan |
| `description` | VARCHAR(255) | NULLABLE | Deskripsi jaminan |
| `customer_id` | BIGINT UNSIGNED | FK → customers, CASCADE | Pemilik jaminan |
| `created_at` | TIMESTAMP | NULLABLE | — |
| `updated_at` | TIMESTAMP | NULLABLE | — |

### 2.3 Tabel `loans`

| Kolom | Tipe | Constraint | Keterangan |
|---|---|---|---|
| `id` | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Primary Key |
| `period` | SMALLINT UNSIGNED | DEFAULT 12 | Jangka waktu (bulan) |
| `amount` | INT UNSIGNED | DEFAULT 0 | Jumlah pinjaman |
| `installment` | INT UNSIGNED | DEFAULT 0 | Jumlah cicilan/bulan |
| `return_amount` | INT UNSIGNED | DEFAULT 0 | Total pengembalian |
| `paid` | INT UNSIGNED | DEFAULT 0 | Total yang sudah dibayar |
| `customer_id` | BIGINT UNSIGNED | FK → customers, CASCADE | Nasabah peminjam |
| `collateral_id` | BIGINT UNSIGNED | FK → collaterals, CASCADE | Jaminan pinjaman |
| `created_at` | TIMESTAMP | NULLABLE | — |
| `updated_at` | TIMESTAMP | NULLABLE | — |

> ⚠️ **Titik Risiko**: `collateral_id` menggunakan `cascadeOnDelete` — hapus customer akan menghapus collateral, yang selanjutnya akan menghapus loans (cascade berantai).

### 2.4 Tabel `deposits`

| Kolom | Tipe | Constraint | Keterangan |
|---|---|---|---|
| `id` | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Primary Key |
| `type` | ENUM('wajib','sukarela','pokok','penarikan') | DEFAULT 'sukarela' | Jenis simpanan |
| `amount` | INT UNSIGNED | NOT NULL | Jumlah simpanan |
| `previous_balance` | INT UNSIGNED | DEFAULT 0 | Saldo sebelum transaksi |
| `current_balance` | INT UNSIGNED | DEFAULT 0 | Saldo setelah transaksi |
| `customer_id` | BIGINT UNSIGNED | FK → customers, CASCADE | Pemilik simpanan |
| `loan_id` | BIGINT UNSIGNED | FK → loans, CASCADE, **NULLABLE** | Referensi pinjaman (opsional) |
| `created_at` | TIMESTAMP | NULLABLE | — |
| `updated_at` | TIMESTAMP | NULLABLE | — |

> ⚠️ **Titik Risiko**: `loan_id` nullable — simpanan bisa ada tanpa pinjaman terkait (tipe `sukarela`/`pokok`). Saat tipe `wajib`, sebaiknya `loan_id` terisi.

### 2.5 Tabel `foreclosures`

| Kolom | Tipe | Constraint | Keterangan |
|---|---|---|---|
| `id` | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Primary Key |
| `date` | DATETIME | NOT NULL | Tanggal penarikan |
| `collateral_amount` | INT UNSIGNED | DEFAULT 0 | Nilai jaminan yang ditarik |
| `remaining_amount` | INT UNSIGNED | DEFAULT 0 | Sisa hutang |
| `return_amount` | INT UNSIGNED | DEFAULT 0 | Nilai kembalian ke nasabah |
| `customer_id` | BIGINT UNSIGNED | FK → customers, CASCADE | Nasabah bermasalah |
| `loan_id` | BIGINT UNSIGNED | FK → loans, CASCADE | Pinjaman yang bermasalah |
| `collateral_id` | BIGINT UNSIGNED | FK → collaterals, CASCADE | Jaminan yang ditarik |
| `created_at` | TIMESTAMP | NULLABLE | — |
| `updated_at` | TIMESTAMP | NULLABLE | — |

### 2.6 Tabel `visits`

| Kolom | Tipe | Constraint | Keterangan |
|---|---|---|---|
| `id` | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Primary Key |
| `date` | DATE | NOT NULL | Tanggal kunjungan |
| `notes` | TEXT | NULLABLE | Catatan kunjungan |
| `customer_id` | BIGINT UNSIGNED | FK → customers, CASCADE | Nasabah yang dikunjungi |
| `created_at` | TIMESTAMP | NULLABLE | — |
| `updated_at` | TIMESTAMP | NULLABLE | — |

---

## 3. Peta Relasi Eloquent ORM

### 3.1 Relasi dari Model `Customer`

```
Customer
  ├── hasMany(Deposit)       → deposits.customer_id
  ├── hasMany(Loan)          → loans.customer_id
  ├── hasMany(Collateral)    → collaterals.customer_id
  ├── hasMany(Foreclosure)   → foreclosures.customer_id
  └── hasMany(Visit)         → visits.customer_id
```

### 3.2 Relasi dari Model `Loan`

```
Loan
  ├── belongsTo(Customer)    → customers.id
  ├── belongsTo(Collateral)  → collaterals.id   [menggunakan 'collateral_id']
  └── hasMany(Deposit)       → deposits.loan_id
```

### 3.3 Relasi dari Model `Deposit`

```
Deposit
  ├── belongsTo(Customer)    → customers.id
  └── belongsTo(Loan)        → loans.id         [nullable]
```

### 3.4 Relasi dari Model `Collateral`

```
Collateral
  └── (tidak ada relasi Eloquent eksplisit di model)
      [Relasi ditangani lewat FK cascades di DB]
```

---

## 4. Titik Uji Kritis (Critical Test Points)

### 4.1 CTP-001: Atomicity `LoanController::store()`

**Lokasi**: `app/Http/Controllers/LoanController.php` baris 115-133

```php
DB::beginTransaction();
$collateral = Collateral::create([...]);  // ← Tulis ke tabel collaterals
$data['collateral_id'] = $collateral->id;
Loan::create($data);                     // ← Tulis ke tabel loans
DB::commit();
```

**Risiko**: Jika `Loan::create()` gagal, `Collateral` yang sudah dibuat akan di-rollback. **Harus diverifikasi**.

---

### 4.2 CTP-002: Kalkulasi `current_balance` di `DepositController::store()`

**Lokasi**: `app/Http/Controllers/DepositController.php` baris 125-143

```php
$simpanan = Deposit::where('customer_id', $request->customer_id)
                   ->where('type', $request->type)
                   ->latest()->first();
$data['previous_balance'] = $simpanan->current_balance ?? 0;
$data['current_balance']  = $data['previous_balance'] + $request->amount;
```

**Risiko**: Kalkulasi bergantung pada record simpanan **terakhir** per nasabah per tipe. Jika urutan insert tidak konsisten, saldo bisa salah.

---

### 4.3 CTP-003: Sinkronisasi `loans.paid` via `LoanTrait::paidLoan()`

**Lokasi**: `app/Traits/LoanTrait.php`

```php
public function paidLoan($id)
{
    $total_paid = Deposit::where('loan_id', $id)->where('type', 'wajib')->sum('amount');
    $updated = Loan::find($id)->update(['paid' => $total_paid]);
}
```

**Dipanggil saat**: Simpan cicilan baru, Edit cicilan, Hapus cicilan.

**Risiko**: Jika `paidLoan()` tidak dipanggil dalam semua skenario, `loans.paid` menjadi stale/tidak akurat.

---

### 4.4 CTP-004: Status Blacklist di `ForeclosureController::store()`

**Lokasi**: `app/Http/Controllers/ForeclosureController.php` baris 130-141

```php
DB::beginTransaction();
Customer::find($request->customer_id)->update(['status' => 'blacklist']); // ← Update status
Foreclosure::create([...]);                                                // ← Buat record
DB::commit();
```

**Risiko**: Jika `Foreclosure::create()` gagal, status nasabah tidak boleh berubah menjadi blacklist. Rollback harus memulihkan status nasabah.

---

### 4.5 CTP-005: Cascade Delete Saat Hapus Pinjaman

**Lokasi**: `app/Http/Controllers/LoanController.php` baris 192-201

```php
$pinjaman->deposits()->delete();  // ← Hapus manual deposits terkait
$pinjaman->delete();              // ← Hapus loan (cascade ke collateral? — perlu dicek)
```

**Risiko**: Hapus manual `deposits` dilakukan **di luar DB Transaction** — tidak ada `DB::beginTransaction()` di method `destroy()`. Jika `$pinjaman->delete()` gagal setelah `deposits()->delete()`, data deposit sudah terhapus tanpa bisa di-rollback.

---

### 4.6 CTP-006: Orphan Records saat Cascade Tidak Lengkap

**Relasi yang perlu diverifikasi**:
```sql
-- Cek orphan deposits (loan sudah dihapus tapi deposit masih ada)
SELECT d.* FROM deposits d 
LEFT JOIN loans l ON l.id = d.loan_id 
WHERE d.loan_id IS NOT NULL AND l.id IS NULL;

-- Cek orphan loans (customer dihapus tapi loan masih ada)
SELECT lo.* FROM loans lo 
LEFT JOIN customers c ON c.id = lo.customer_id 
WHERE c.id IS NULL;

-- Cek orphan collaterals
SELECT co.* FROM collaterals co 
LEFT JOIN customers c ON c.id = co.customer_id 
WHERE c.id IS NULL;
```

---

## 5. Matriks Risiko Relasi Database

| Relasi | Cascade Delete | Cascade Update | Nullable | Risiko Orphan |
|---|---|---|---|---|
| `customers` → `loans` | ✅ Ya | ✅ Ya | Tidak | Rendah |
| `customers` → `deposits` | ✅ Ya | ✅ Ya | Tidak | Rendah |
| `customers` → `collaterals` | ✅ Ya | ✅ Ya | Tidak | Rendah |
| `customers` → `foreclosures` | ✅ Ya | ✅ Ya | Tidak | Rendah |
| `customers` → `visits` | ✅ Ya | ✅ Ya | Tidak | Rendah |
| `collaterals` → `loans` | ✅ Ya | ✅ Ya | Tidak | **Sedang** (cascade berantai) |
| `loans` → `deposits` | ✅ Ya | ✅ Ya | ✅ Ya | Rendah |
| `loans` → `foreclosures` | ✅ Ya | ✅ Ya | Tidak | Rendah |
| `collaterals` → `foreclosures` | ✅ Ya | ✅ Ya | Tidak | Rendah |
