# GB-TC-003 — Sinkronisasi Pembayaran Cicilan & `loans.paid`
## Gray-Box Test Case | Modul: Pembayaran Cicilan (Installment)

---

| Field | Detail |
|---|---|
| **ID Kasus Uji** | GB-TC-003 |
| **Judul** | Sinkronisasi antara Pembayaran Cicilan (tabel `deposits`) dan Field `loans.paid` |
| **Modul** | Transaksi Pembayaran Cicilan (`InstallmentController`) |
| **Prioritas** | 🔴 Tinggi |
| **Metode** | Gray-Box (kalkulasi agregat + sinkronisasi field derived) |
| **Tanggal Dibuat** | Juli 2026 |
| **Status** | Belum Diuji |

---

## Pengetahuan Internal yang Digunakan

### Mekanisme `paidLoan()` di `LoanTrait`

```php
// app/Traits/LoanTrait.php
public function paidLoan($id)
{
    $total_paid = Deposit::where('loan_id', $id)
                         ->where('type', 'wajib')
                         ->sum('amount');
    $updated = Loan::find($id)->update(['paid' => $total_paid]);
    return $updated;
}
```

**Kapan `paidLoan()` dipanggil** (dari `InstallmentController`):
- `store()` — saat tambah cicilan baru
- `update()` — saat edit cicilan
- `destroy()` — saat hapus cicilan

### Kondisi Pemanggilan `paidLoan()` di `store()`

```php
// InstallmentController::store() baris 130-133
if ($pembayaran && $request->type == 'wajib' && $request->loan_id) {
    $this->paidLoan($request->loan_id);
}
```

> ⚠️ **Titik Risiko**: `paidLoan()` hanya dipanggil jika `$pembayaran` (record cicilan sebelumnya) **sudah ada**. Artinya, cicilan pertama mungkin tidak men-trigger update `loans.paid`.

---

## Prasyarat (Preconditions)

- [ ] Ada nasabah dengan pinjaman aktif
- [ ] Pinjaman belum/sudah ada cicilan (untuk kedua skenario)

```sql
-- Identifikasi pinjaman aktif untuk diuji
SELECT 
    l.id AS loan_id,
    l.amount,
    l.paid,
    l.return_amount,
    (l.return_amount - l.paid) AS sisa_hutang,
    cu.name AS nasabah,
    COUNT(d.id) AS jumlah_cicilan
FROM loans l
JOIN customers cu ON cu.id = l.customer_id
LEFT JOIN deposits d ON d.loan_id = l.id AND d.type = 'wajib'
GROUP BY l.id
ORDER BY l.id DESC;
```

---

## Skenario Pengujian

---

### Skenario 1: Tambah Cicilan Pertama — Apakah `loans.paid` Ter-Update?

**Tujuan**: Memverifikasi bahwa cicilan pertama pada suatu pinjaman tetap menyinkronkan `loans.paid`.

> ⚠️ **Perhatian**: Berdasarkan kode, kondisi `if ($pembayaran && ...)` berarti `paidLoan()` **tidak dipanggil** jika belum ada cicilan sebelumnya (`$pembayaran = null`).

#### Langkah-Langkah

| No | Aksi | Verifikasi DB |
|---|---|---|
| 1 | Pilih pinjaman yang **belum pernah ada cicilannya** | `SELECT paid FROM loans WHERE id=[ID]` → catat nilai awal |
| 2 | Tambah pembayaran cicilan baru untuk pinjaman tersebut | — |
| 3 | Isi amount: `450000`, tipe: `wajib`, pilih pinjaman | — |
| 4 | Submit form | — |
| 5 | — | `SELECT paid FROM loans WHERE id=[ID]` → bandingkan |

#### Hasil yang Diharapkan vs Aktual

| | Diharapkan | Aktual |
|---|---|---|
| `loans.paid` setelah cicilan pertama | `450000` | ? |

> Jika `loans.paid` tetap `0` setelah cicilan pertama, ini adalah **BUG** yang perlu dilaporkan.

**Status**: ☐ PASS &nbsp;&nbsp; ☐ FAIL &nbsp;&nbsp; ☐ BLOCKED

**Catatan**:

---

### Skenario 2: Tambah Cicilan Kedua — Akumulasi `loans.paid`

**Tujuan**: Memastikan setiap penambahan cicilan mengakumulasikan `loans.paid` dengan benar.

#### Prasyarat Tambahan

Pinjaman sudah memiliki minimal 1 cicilan sebelumnya.

```sql
-- Catat state sebelum cicilan kedua
SELECT 
    l.paid AS loans_paid_before,
    SUM(d.amount) AS total_deposits_wajib
FROM loans l
JOIN deposits d ON d.loan_id = l.id AND d.type = 'wajib'
WHERE l.id = [ID_PINJAMAN]
GROUP BY l.id;
```

#### Data Uji

| Field | Nilai |
|---|---|
| Tipe | wajib |
| Jumlah cicilan | `450000` |
| Pinjaman | Pinjaman yang sudah ada cicilan sebelumnya |

#### Kalkulasi yang Diharapkan

```
loans.paid (baru) = SUM(semua deposits.amount WHERE loan_id=[ID] AND type='wajib')
```

#### Query Verifikasi

```sql
-- Verifikasi setelah cicilan kedua
SELECT 
    l.id,
    l.paid AS loans_paid_after,
    SUM(d.amount) AS total_sum_deposits,
    CASE 
        WHEN l.paid = SUM(d.amount) THEN '✅ SINKRON'
        ELSE '❌ TIDAK SINKRON'
    END AS sync_status
FROM loans l
JOIN deposits d ON d.loan_id = l.id AND d.type = 'wajib'
WHERE l.id = [ID_PINJAMAN]
GROUP BY l.id;
```

**Status**: ☐ PASS &nbsp;&nbsp; ☐ FAIL &nbsp;&nbsp; ☐ BLOCKED

**Catatan**:

---

### Skenario 3: Hapus Cicilan — `loans.paid` Harus Berkurang

**Tujuan**: Memastikan penghapusan cicilan memperbarui `loans.paid` dengan mengurangi jumlah yang sesuai.

**Referensi kode** (`destroy()`):
```php
DB::beginTransaction();
$type = $pembayaran->type;
$id   = $pembayaran->loan_id;
$pembayaran->delete();
if ($type == 'wajib' && $id) {
    $this->paidLoan($id);
}
DB::commit();
```

#### Langkah-Langkah

| No | Aksi | Verifikasi |
|---|---|---|
| 1 | Catat `loans.paid` sebelum hapus | `SELECT paid FROM loans WHERE id=[ID]` |
| 2 | Catat jumlah cicilan yang akan dihapus | `SELECT amount FROM deposits WHERE id=[ID_DEPOSIT]` |
| 3 | Hapus cicilan dari UI | — |
| 4 | Verifikasi `loans.paid` berkurang | `SELECT paid FROM loans WHERE id=[ID]` |

#### Hasil yang Diharapkan

```
loans.paid (setelah hapus) = loans.paid (sebelum) - amount (cicilan yang dihapus)
```

**Status**: ☐ PASS &nbsp;&nbsp; ☐ FAIL &nbsp;&nbsp; ☐ BLOCKED

**Catatan**:

---

### Skenario 4: Edit Jumlah Cicilan — `loans.paid` Ter-Recalculate

**Tujuan**: Memastikan edit jumlah cicilan memicu recalculation `loans.paid`.

**Referensi kode** (`update()`):
```php
$pembayaran->update($request->all());
if ($pembayaran && $request->type == 'wajib' && $pembayaran->loan_id) {
    $this->paidLoan($pembayaran->loan_id);
}
```

#### Data Uji

| Field | Nilai Lama | Nilai Baru |
|---|---|---|
| Amount cicilan | `450000` | `500000` |

#### Query Verifikasi

```sql
-- Setelah edit, verifikasi loans.paid = SUM dari semua cicilan
SELECT 
    l.paid,
    SUM(d.amount) AS expected_paid,
    CASE WHEN l.paid = SUM(d.amount) THEN 'SINKRON' ELSE 'TIDAK SINKRON' END AS status
FROM loans l
JOIN deposits d ON d.loan_id = l.id AND d.type = 'wajib'
WHERE l.id = [ID_PINJAMAN]
GROUP BY l.id;
```

**Status**: ☐ PASS &nbsp;&nbsp; ☐ FAIL &nbsp;&nbsp; ☐ BLOCKED

**Catatan**:

---

### Skenario 5: Verifikasi Konsistensi Global `loans.paid`

**Tujuan**: Pemeriksaan menyeluruh bahwa semua `loans.paid` konsisten dengan jumlah cicilan di `deposits`.

#### Query Audit Menyeluruh

```sql
-- Deteksi SEMUA pinjaman yang loans.paid-nya tidak sinkron
SELECT 
    l.id AS loan_id,
    cu.name AS nasabah,
    l.paid AS nilai_paid_di_loans,
    COALESCE(SUM(d.amount), 0) AS total_cicilan_di_deposits,
    (l.paid - COALESCE(SUM(d.amount), 0)) AS selisih,
    CASE 
        WHEN l.paid = COALESCE(SUM(d.amount), 0) THEN '✅ SINKRON'
        ELSE '❌ TIDAK SINKRON - PERLU INVESTIGASI'
    END AS status
FROM loans l
JOIN customers cu ON cu.id = l.customer_id
LEFT JOIN deposits d ON d.loan_id = l.id AND d.type = 'wajib'
GROUP BY l.id
HAVING l.paid != COALESCE(SUM(d.amount), 0)  -- Hanya tampilkan yang tidak sinkron
ORDER BY ABS(selisih) DESC;
```

> Jika query ini mengembalikan **0 baris**, maka semua data sinkron = ✅ PASS.
> Jika mengembalikan **lebih dari 0 baris**, ada anomali = ❌ FAIL.

**Status**: ☐ PASS &nbsp;&nbsp; ☐ FAIL &nbsp;&nbsp; ☐ BLOCKED

**Catatan**:

---

## Ringkasan Hasil

| Skenario | Status | Tanggal Uji | Penguji |
|---|---|---|---|
| Skenario 1: Cicilan Pertama | — | — | — |
| Skenario 2: Akumulasi `paid` | — | — | — |
| Skenario 3: Hapus Cicilan | — | — | — |
| Skenario 4: Edit Cicilan | — | — | — |
| Skenario 5: Audit Global | — | — | — |

**Kesimpulan Keseluruhan**: —

---

## Temuan / Defect

| ID Defect | Deskripsi | Skenario | Severity |
|---|---|---|---|
| — | — | — | — |
