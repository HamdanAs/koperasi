# GB-TC-007 — Mekanisme DB Transaction & Rollback
## Gray-Box Test Case | Modul: Multi-Modul (Transaction Boundary)

---

| Field | Detail |
|---|---|
| **ID Kasus Uji** | GB-TC-007 |
| **Judul** | Verifikasi Mekanisme `DB::beginTransaction()`, `commit()`, dan `rollBack()` |
| **Modul** | Multi-Modul (Pinjaman, Simpanan, Cicilan, Penarikan Jaminan) |
| **Prioritas** | 🔴 Tinggi |
| **Metode** | Gray-Box (transaction boundary testing + error injection) |
| **Tanggal Dibuat** | Juli 2026 |
| **Status** | Belum Diuji |

---

## Pengetahuan Internal yang Digunakan

### Peta Penggunaan DB Transaction per Controller

| Controller | Method | DB Transaction? | Catatan |
|---|---|---|---|
| `LoanController` | `store()` | ✅ Ya | Atomik: collateral + loan |
| `LoanController` | `update()` | ✅ Ya | Atomik: loan + collateral |
| `LoanController` | `destroy()` | ❌ **Tidak** | ⚠️ Risiko: deposits dihapus tanpa transaction |
| `DepositController` | `store()` | ✅ Ya | |
| `DepositController` | `update()` | ✅ Ya | |
| `DepositController` | `destroy()` | ✅ Ya | |
| `InstallmentController` | `store()` | ✅ Ya | |
| `InstallmentController` | `update()` | ✅ Ya | |
| `InstallmentController` | `destroy()` | ✅ Ya | |
| `ForeclosureController` | `store()` | ✅ Ya | Atomik: customer status + foreclosure |
| `ForeclosureController` | `update()` | ❌ **Tidak** | ⚠️ Tidak ada transaction |
| `ForeclosureController` | `destroy()` | ❌ **Tidak** | ⚠️ Tidak ada transaction |

---

## Skenario Pengujian

---

### Skenario 1: Rollback saat `Loan::create()` Gagal

**Tujuan**: Memastikan jika `Loan::create()` gagal setelah `Collateral::create()` berhasil, maka collateral juga tidak tersimpan (rollback atomik).

#### Metode Pengujian (Error Injection via Sementara Modifikasi)

> ⚠️ **Catatan**: Skenario ini memerlukan manipulasi sementara di environment pengujian.

**Metode A (Simulasi via SQL Constraint)**:
```sql
-- Buat constraint temporary yang akan memaksa Loan::create() gagal
-- Misalnya: ubah sementara kolom period menjadi NOT NULL tanpa default
-- (Hanya di environment DEV/TEST!)
```

**Metode B (Verifikasi Normal)**:
Verifikasi bahwa setelah skenario sukses, tidak ada collateral "yatim" tanpa loan:

```sql
-- Cek collateral yang tidak memiliki loan (orphan collateral)
SELECT co.* 
FROM collaterals co
LEFT JOIN loans l ON l.collateral_id = co.id
WHERE l.id IS NULL;
```

> Jika **ada** collateral tanpa loan, kemungkinan ada kasus rollback yang tidak berjalan atau ada bug lain.

#### Hasil yang Diharapkan

- Jika transaksi sukses: `collaterals` dan `loans` keduanya punya record baru.
- Jika transaksi gagal: Tidak ada record baru di keduanya.
- Tidak boleh ada collateral tanpa loan (kecuali memang ada kasus bisnis tersebut).

**Status**: ☐ PASS &nbsp;&nbsp; ☐ FAIL &nbsp;&nbsp; ☐ BLOCKED

**Catatan**:

---

### Skenario 2: `LoanController::destroy()` Tanpa Transaction — Risiko Data Parsial

**Tujuan**: Mendokumentasikan dan memverifikasi risiko nyata dari tidak adanya DB Transaction di `destroy()`.

**Kode yang diuji**:
```php
// LoanController::destroy() - TIDAK ada DB::beginTransaction()
public function destroy(Loan $pinjaman)
{
    try {
        $pinjaman->deposits()->delete();  // ← Langkah 1: hapus deposits
        $pinjaman->delete();             // ← Langkah 2: hapus loan
        return back()->with('success', ...);
    } catch (\Throwable $th) {
        return back()->with('error', $th->getMessage());
    }
}
```

#### Skenario Risiko

**Apa yang terjadi jika Langkah 2 gagal setelah Langkah 1 berhasil?**
- Deposits sudah terhapus (permanen, tanpa rollback)
- Loan masih ada (tidak terhapus)
- Sistem menampilkan pesan error
- Data menjadi **inkonsisten**: loan ada tapi deposits-nya sudah hilang

#### Verifikasi (Kondisi Normal)

Dalam kondisi normal, verifikasi kedua langkah berhasil:

```sql
-- Sebelum hapus
SELECT 
    l.id AS loan_id,
    COUNT(d.id) AS deposits_count
FROM loans l
LEFT JOIN deposits d ON d.loan_id = l.id
WHERE l.id = [ID_PINJAMAN]
GROUP BY l.id;

-- Setelah hapus (kedua harus 0)
SELECT COUNT(*) FROM loans WHERE id = [ID_PINJAMAN];
SELECT COUNT(*) FROM deposits WHERE loan_id = [ID_PINJAMAN];
```

#### Rekomendasi

> ⚠️ **Temuan Potensial**: Method `LoanController::destroy()` dan `ForeclosureController::destroy()` tidak menggunakan `DB::beginTransaction()`. Ini perlu didokumentasikan sebagai **risiko** dalam laporan hasil pengujian.

**Status**: ☐ PASS &nbsp;&nbsp; ☐ FAIL &nbsp;&nbsp; ☐ BLOCKED (dokumentasi risiko)

**Catatan**:

---

### Skenario 3: Rollback saat Penarikan Jaminan Gagal

**Tujuan**: Memastikan jika `Foreclosure::create()` gagal, status nasabah tidak berubah menjadi `blacklist`.

**Kode yang diuji**:
```php
DB::beginTransaction();
Customer::find($request->customer_id)->update(['status' => 'blacklist']); // ← Langkah 1
Foreclosure::create([...]);  // ← Langkah 2 (jika ini gagal, rollback harus memulihkan langkah 1)
DB::commit();
```

#### Verifikasi Kondisi Normal

```sql
-- Setelah penarikan jaminan berhasil:
SELECT status FROM customers WHERE id = [ID];  -- Harus 'blacklist'
SELECT COUNT(*) FROM foreclosures WHERE customer_id = [ID];  -- Harus > 0

-- Jika ada error (simulasi):
-- Status nasabah harus tetap 'active' dan tidak ada record foreclosure baru
```

#### Metode Verifikasi Error (Simulasi)

Untuk menguji rollback, bisa dicoba:
1. Matikan koneksi database saat proses berjalan (extreme)
2. Simulasi dengan menambahkan constraint sementara pada tabel `foreclosures`

**Status**: ☐ PASS &nbsp;&nbsp; ☐ FAIL &nbsp;&nbsp; ☐ BLOCKED

**Catatan**:

---

### Skenario 4: Konsistensi Transaction saat Hapus Simpanan

**Tujuan**: Memverifikasi bahwa hapus simpanan tipe `wajib` + update `loans.paid` berjalan atomik.

**Kode yang diuji** (`DepositController::destroy()`):
```php
DB::beginTransaction();
$type = $simpanan->type;
$id   = $simpanan->loan_id;
$simpanan->delete();                     // ← Langkah 1
if ($type == 'wajib' && $id) {
    $this->paidLoan($id);                // ← Langkah 2 (update loans.paid)
}
DB::commit();
```

#### Query Verifikasi

```sql
-- Sebelum hapus cicilan
SELECT l.paid, SUM(d.amount) AS total_cicilan
FROM loans l
JOIN deposits d ON d.loan_id = l.id AND d.type = 'wajib'
WHERE l.id = [ID_PINJAMAN]
GROUP BY l.id;

-- Setelah hapus cicilan
SELECT l.paid, COALESCE(SUM(d.amount), 0) AS total_cicilan
FROM loans l
LEFT JOIN deposits d ON d.loan_id = l.id AND d.type = 'wajib'
WHERE l.id = [ID_PINJAMAN]
GROUP BY l.id;

-- Keduanya harus SAMA (loans.paid = SUM deposits wajib)
```

**Status**: ☐ PASS &nbsp;&nbsp; ☐ FAIL &nbsp;&nbsp; ☐ BLOCKED

**Catatan**:

---

### Skenario 5: Inventarisasi Method Tanpa DB Transaction

**Tujuan**: Mengidentifikasi dan mendokumentasikan semua method yang melakukan operasi multi-langkah tanpa perlindungan DB Transaction.

#### Temuan yang Perlu Diverifikasi

| Method | File | Operasi | Status Transaction |
|---|---|---|---|
| `LoanController::destroy()` | `LoanController.php:192` | hapus deposits + hapus loan | ❌ Tidak ada |
| `ForeclosureController::update()` | `ForeclosureController.php:179` | update foreclosure saja | ⚠️ Mungkin tidak perlu |
| `ForeclosureController::destroy()` | `ForeclosureController.php:195` | hapus foreclosure saja | ⚠️ Mungkin tidak perlu |
| `CustomerController::destroy()` | Perlu dicek | Hapus nasabah (cascade) | Perlu dicek |

#### Aksi

Untuk setiap method di atas:
1. Verifikasi apakah operasinya multi-langkah (lebih dari 1 SQL statement penting)
2. Jika ya, catat sebagai **temuan risiko**
3. Rekomendasikan penambahan `DB::beginTransaction()`

**Status**: ☐ PASS &nbsp;&nbsp; ☐ FAIL &nbsp;&nbsp; ☐ BLOCKED

**Catatan**:

---

## Ringkasan Hasil & Temuan Risiko

| Skenario | Status | Risiko | Severity |
|---|---|---|---|
| Skenario 1: Rollback Loan+Collateral | — | Orphan collateral jika rollback gagal | 🔴 Tinggi |
| Skenario 2: destroy() Tanpa Transaction | — | Data parsial (deposits hilang, loan masih ada) | 🔴 Tinggi |
| Skenario 3: Rollback Foreclosure+Status | — | Status nasabah berubah tanpa record | 🔴 Tinggi |
| Skenario 4: Hapus Cicilan Atomik | — | `paid` tidak ter-update | 🟠 Sedang |
| Skenario 5: Inventarisasi Risiko | — | Dokumentasi | 🟠 Sedang |

**Rekomendasi Perbaikan**:
- Tambahkan `DB::beginTransaction()` pada `LoanController::destroy()` untuk membungkus `deposits()->delete()` dan `delete()`
