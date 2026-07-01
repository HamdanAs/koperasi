# GB-TC-006 — Konsistensi Kalkulasi Saldo Simpanan
## Gray-Box Test Case | Modul: Simpanan (Deposit)

---

| Field | Detail |
|---|---|
| **ID Kasus Uji** | GB-TC-006 |
| **Judul** | Konsistensi Kalkulasi `previous_balance` dan `current_balance` di Tabel `deposits` |
| **Modul** | Transaksi Simpanan (`DepositController`) |
| **Prioritas** | 🔴 Tinggi |
| **Metode** | Gray-Box (kalkulasi running balance + boundary value analysis) |
| **Tanggal Dibuat** | Juli 2026 |
| **Status** | Belum Diuji |

---

## Pengetahuan Internal yang Digunakan

### Logika Kalkulasi Saldo

**Di `DepositController::store()`**:
```php
// Ambil simpanan terakhir per nasabah per tipe
$simpanan = Deposit::where('customer_id', $request->customer_id)
                   ->where('type', $request->type)
                   ->latest()  // ORDER BY created_at DESC → ambil TERBARU
                   ->first();

// Hitung saldo
$data['previous_balance'] = $simpanan->current_balance ?? 0;
$data['current_balance']  = $data['previous_balance'] + $request->amount;
```

### Poin Kritis yang Perlu Diverifikasi

1. **Urutan `latest()`** — apakah `latest()` menggunakan `created_at` atau `id`? Jika ada 2 record dengan `created_at` identik, urutan bisa tidak deterministik.
2. **Tipe terpisah** — `previous_balance` dihitung per tipe. Simpanan tipe `sukarela` dan `wajib` punya running balance **masing-masing terpisah**.
3. **Tidak ada recalculation saat edit** — `update()` tidak menghitung ulang saldo, hanya mengubah field yang diinput. Ini bisa menyebabkan inkonsistensi.

---

## Prasyarat (Preconditions)

- [ ] Ada nasabah dengan beberapa simpanan

```sql
-- Lihat riwayat simpanan nasabah untuk baseline
SELECT 
    id, 
    type, 
    amount, 
    previous_balance, 
    current_balance,
    created_at
FROM deposits
WHERE customer_id = [ID_NASABAH]
ORDER BY type, created_at ASC;
```

---

## Skenario Pengujian

---

### Skenario 1: Verifikasi Running Balance per Tipe Simpanan

**Tujuan**: Memastikan `current_balance` setiap baris = `previous_balance` baris berikutnya (untuk tipe yang sama).

#### Query Verifikasi

```sql
-- Cek konsistensi running balance per nasabah per tipe
SELECT 
    a.id,
    a.type,
    a.amount,
    a.previous_balance,
    a.current_balance,
    b.id AS next_id,
    b.previous_balance AS next_previous_balance,
    CASE 
        WHEN b.id IS NULL THEN '(terakhir)'
        WHEN a.current_balance = b.previous_balance THEN '✅ KONSISTEN'
        ELSE CONCAT('❌ TIDAK KONSISTEN: current=', a.current_balance, ' ≠ next_prev=', b.previous_balance)
    END AS chain_check
FROM deposits a
LEFT JOIN deposits b ON (
    b.customer_id = a.customer_id 
    AND b.type = a.type 
    AND b.created_at = (
        SELECT MIN(created_at) FROM deposits 
        WHERE customer_id = a.customer_id 
        AND type = a.type 
        AND created_at > a.created_at
    )
)
WHERE a.customer_id = [ID_NASABAH]
ORDER BY a.type, a.created_at;
```

#### Hasil yang Diharapkan

Semua baris menunjukkan `chain_check = '✅ KONSISTEN'` atau `'(terakhir)'`.

**Status**: ☐ PASS &nbsp;&nbsp; ☐ FAIL &nbsp;&nbsp; ☐ BLOCKED

**Catatan**:

---

### Skenario 2: Saldo Simpanan Tipe `sukarela` Tidak Tercampur dengan `wajib`

**Tujuan**: Memastikan kalkulasi saldo tipe `sukarela` tidak terpengaruh oleh simpanan tipe `wajib` atau `pokok`.

#### Langkah-Langkah

| No | Aksi | Verifikasi |
|---|---|---|
| 1 | Catat `current_balance` simpanan sukarela terakhir nasabah | Query di bawah |
| 2 | Tambah simpanan baru tipe `wajib` untuk nasabah yang sama | — |
| 3 | Tambah simpanan baru tipe `sukarela` untuk nasabah yang sama | — |
| 4 | Verifikasi `previous_balance` simpanan sukarela baru | Harus = saldo sukarela terakhir (bukan saldo wajib) |

#### Query Verifikasi

```sql
-- Saldo terakhir per tipe
SELECT type, current_balance AS saldo_terakhir
FROM deposits
WHERE customer_id = [ID_NASABAH]
AND id IN (
    SELECT MAX(id) FROM deposits 
    WHERE customer_id = [ID_NASABAH] 
    GROUP BY type
)
ORDER BY type;
```

**Status**: ☐ PASS &nbsp;&nbsp; ☐ FAIL &nbsp;&nbsp; ☐ BLOCKED

**Catatan**:

---

### Skenario 3: Boundary Value — Simpanan dengan Amount = 0

**Tujuan**: Memverifikasi sistem menolak atau menangani dengan benar input jumlah simpanan = 0.

#### Data Uji

| Field | Nilai |
|---|---|
| Tipe | sukarela |
| Jumlah | `0` |

#### Hasil yang Diharapkan

Sistem harus menolak input dengan pesan validasi, ATAU jika diterima, `current_balance` tidak berubah dari `previous_balance`.

> Cek apakah ada validasi `min:1` atau `min:0` di `StoreDepositRequest`.

```sql
-- Setelah percobaan, cek apakah ada record baru dengan amount = 0
SELECT * FROM deposits WHERE customer_id = [ID] AND amount = 0;
```

**Status**: ☐ PASS &nbsp;&nbsp; ☐ FAIL &nbsp;&nbsp; ☐ BLOCKED

**Catatan**:

---

### Skenario 4: Dampak Edit Simpanan terhadap Konsistensi Saldo

**Tujuan**: Menguji apakah mengedit amount simpanan di tengah riwayat menyebabkan inkonsistensi saldo.

> ⚠️ **Prediksi**: Edit amount di simpanan yang bukan terakhir akan menyebabkan saldo menjadi **tidak konsisten** karena tidak ada mekanisme recalculation running balance.

#### Langkah-Langkah

| No | Aksi |
|---|---|
| 1 | Ambil nasabah yang memiliki > 3 simpanan sukarela |
| 2 | Edit simpanan ke-2 (bukan yang terakhir), ubah amount |
| 3 | Jalankan query audit running balance |

#### Query Audit Pasca-Edit

```sql
-- Deteksi inkonsistensi saldo setelah edit
SELECT 
    id, type, amount, previous_balance, current_balance,
    LAG(current_balance) OVER (PARTITION BY customer_id, type ORDER BY created_at) AS prev_current,
    CASE 
        WHEN LAG(current_balance) OVER (PARTITION BY customer_id, type ORDER BY created_at) IS NULL 
             THEN '(pertama)'
        WHEN previous_balance = LAG(current_balance) OVER (PARTITION BY customer_id, type ORDER BY created_at) 
             THEN '✅ OK'
        ELSE '❌ INKONSISTEN'
    END AS chain_status
FROM deposits
WHERE customer_id = [ID_NASABAH]
ORDER BY type, created_at;
```

**Status**: ☐ PASS &nbsp;&nbsp; ☐ FAIL &nbsp;&nbsp; ☐ BLOCKED

**Catatan**:

---

### Skenario 5: Audit Saldo Negatif atau Tidak Wajar

**Tujuan**: Deteksi nilai saldo yang tidak wajar (negatif atau `current_balance < previous_balance` saat bukan penarikan).

```sql
-- Deteksi anomali saldo
SELECT 
    d.id,
    d.customer_id,
    cu.name,
    d.type,
    d.amount,
    d.previous_balance,
    d.current_balance,
    CASE
        WHEN d.current_balance < 0 THEN '❌ Saldo negatif'
        WHEN d.type != 'penarikan' AND d.current_balance < d.previous_balance 
             THEN '⚠️ Saldo berkurang padahal bukan penarikan'
        WHEN d.current_balance != (d.previous_balance + d.amount) 
             AND d.type != 'penarikan'
             THEN CONCAT('❌ Kalkulasi salah: ', d.previous_balance, '+', d.amount, '≠', d.current_balance)
        ELSE '✅ Normal'
    END AS anomali
FROM deposits d
JOIN customers cu ON cu.id = d.customer_id
WHERE d.current_balance < 0
   OR (d.type != 'penarikan' AND d.current_balance < d.previous_balance)
   OR (d.type != 'penarikan' AND d.current_balance != (d.previous_balance + d.amount))
ORDER BY d.customer_id, d.type, d.created_at;
```

> Jika query mengembalikan **0 baris**, tidak ada anomali = ✅ PASS.

**Status**: ☐ PASS &nbsp;&nbsp; ☐ FAIL &nbsp;&nbsp; ☐ BLOCKED

**Catatan**:

---

## Ringkasan Hasil

| Skenario | Status | Tanggal Uji | Penguji |
|---|---|---|---|
| Skenario 1: Running Balance Chain | — | — | — |
| Skenario 2: Isolasi per Tipe | — | — | — |
| Skenario 3: Boundary Amount = 0 | — | — | — |
| Skenario 4: Dampak Edit Tengah | — | — | — |
| Skenario 5: Audit Anomali Saldo | — | — | — |

**Kesimpulan Keseluruhan**: —
