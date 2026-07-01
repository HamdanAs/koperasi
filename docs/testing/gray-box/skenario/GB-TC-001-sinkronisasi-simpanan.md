# GB-TC-001 — Sinkronisasi Data Simpanan ke Database
## Gray-Box Test Case | Modul: Simpanan (Deposit)

---

| Field | Detail |
|---|---|
| **ID Kasus Uji** | GB-TC-001 |
| **Judul** | Sinkronisasi Data Simpanan dari Form UI ke Tabel `deposits` MySQL |
| **Modul** | Transaksi Simpanan (`DepositController`) |
| **Prioritas** | 🔴 Tinggi |
| **Metode** | Gray-Box (inspeksi form UI + verifikasi langsung ke database) |
| **Tanggal Dibuat** | Juli 2026 |
| **Status** | Belum Diuji |

---

## Pengetahuan Internal yang Digunakan

**Controller**: `app/Http/Controllers/DepositController.php`

Logika kritis yang diuji (baris 125-143):
```php
$simpanan = Deposit::where('customer_id', $request->customer_id)
                   ->where('type', $request->type)
                   ->latest()->first();

$data['previous_balance'] = $simpanan->current_balance ?? 0;
$data['current_balance']  = $data['previous_balance'] + $request->amount;

Deposit::create($data);
```

**Tabel yang terlibat**:
- `deposits` (INSERT)
- `customers` (SELECT — validasi nasabah aktif)
- `loans` (SELECT — optional, untuk tipe wajib)

---

## Prasyarat (Preconditions)

- [ ] Aplikasi berjalan di `http://localhost/koperasi-swamitra-karya-bersama/public`
- [ ] Pengguna sudah login sebagai admin/kasir
- [ ] Terdapat minimal **1 nasabah aktif** di tabel `customers` (status = 'active')
- [ ] Akses ke MySQL tersedia untuk verifikasi (phpMyAdmin/CLI)
- [ ] Query awal untuk mendapatkan baseline:

```sql
-- Simpan jumlah record sebelum pengujian
SELECT COUNT(*) AS total_deposits FROM deposits;
SELECT id, customer_id, type, amount, previous_balance, current_balance 
FROM deposits 
WHERE customer_id = [ID_NASABAH] 
ORDER BY id DESC LIMIT 5;
```

---

## Skenario Pengujian

---

### Skenario 1: Simpan Simpanan Sukarela Pertama Kali

**Tujuan**: Memastikan simpanan pertama nasabah memiliki `previous_balance = 0`.

#### Langkah-Langkah

| No | Aksi di UI | Aksi Verifikasi DB |
|---|---|---|
| 1 | Navigasi ke **Transaksi → Simpanan → Tambah Simpanan** | — |
| 2 | Pilih nasabah yang **belum punya simpanan sukarela** | `SELECT COUNT(*) FROM deposits WHERE customer_id=[ID] AND type='sukarela'` → harus **0** |
| 3 | Pilih tipe: **Sukarela** | — |
| 4 | Isi jumlah: **500000** | — |
| 5 | Klik tombol **Simpan** | — |
| 6 | Verifikasi notifikasi sukses di UI | — |
| 7 | — | `SELECT * FROM deposits WHERE customer_id=[ID] AND type='sukarela' ORDER BY id DESC LIMIT 1` |

#### Data Uji

| Field | Nilai |
|---|---|
| Nasabah | Pilih nasabah belum ada simpanan sukarela |
| Tipe | sukarela |
| Jumlah | 500000 |

#### Hasil yang Diharapkan

| Kolom DB | Nilai yang Diharapkan |
|---|---|
| `type` | `sukarela` |
| `amount` | `500000` |
| `previous_balance` | `0` ← harus nol karena belum ada simpanan sebelumnya |
| `current_balance` | `500000` |
| `customer_id` | ID nasabah yang dipilih |
| `loan_id` | `NULL` |

**Status**: ☐ PASS &nbsp;&nbsp; ☐ FAIL &nbsp;&nbsp; ☐ BLOCKED

**Catatan**:

---

### Skenario 2: Simpan Simpanan Sukarela Berikutnya (Akumulasi Saldo)

**Tujuan**: Memastikan `previous_balance` diambil dari `current_balance` simpanan terakhir.

#### Prasyarat Tambahan

Nasabah sudah memiliki simpanan sukarela sebelumnya (dari Skenario 1 atau data existing).

```sql
-- Cek simpanan terakhir nasabah
SELECT id, amount, previous_balance, current_balance 
FROM deposits 
WHERE customer_id=[ID] AND type='sukarela' 
ORDER BY id DESC LIMIT 1;
-- Catat nilai current_balance: [NILAI_SALDO_TERAKHIR]
```

#### Data Uji

| Field | Nilai |
|---|---|
| Nasabah | Nasabah yang sudah punya simpanan sukarela |
| Tipe | sukarela |
| Jumlah | 200000 |

#### Hasil yang Diharapkan

| Kolom DB | Nilai yang Diharapkan |
|---|---|
| `previous_balance` | `[NILAI_SALDO_TERAKHIR]` dari query di atas |
| `current_balance` | `[NILAI_SALDO_TERAKHIR] + 200000` |
| `amount` | `200000` |

**Status**: ☐ PASS &nbsp;&nbsp; ☐ FAIL &nbsp;&nbsp; ☐ BLOCKED

**Catatan**:

---

### Skenario 3: Simpan Simpanan Wajib dengan Referensi Pinjaman

**Tujuan**: Memastikan simpanan wajib tersimpan dengan `loan_id` yang benar.

#### Data Uji

| Field | Nilai |
|---|---|
| Nasabah | Nasabah yang memiliki pinjaman aktif |
| Tipe | wajib |
| Jumlah | 150000 |
| Pinjaman | Pilih pinjaman aktif nasabah tersebut |

#### Langkah Verifikasi DB

```sql
-- Setelah simpan, verifikasi deposit baru
SELECT d.*, l.amount AS loan_amount, l.paid AS loan_paid
FROM deposits d
LEFT JOIN loans l ON l.id = d.loan_id
WHERE d.customer_id = [ID]
AND d.type = 'wajib'
ORDER BY d.id DESC LIMIT 1;
```

#### Hasil yang Diharapkan

| Kolom DB | Nilai yang Diharapkan |
|---|---|
| `type` | `wajib` |
| `loan_id` | ID pinjaman yang dipilih (bukan NULL) |
| `amount` | `150000` |
| `loans.paid` | Harus ter-update oleh `paidLoan()` |

**Status**: ☐ PASS &nbsp;&nbsp; ☐ FAIL &nbsp;&nbsp; ☐ BLOCKED

**Catatan**:

---

### Skenario 4: Sinkronisasi Data Setelah Edit Simpanan

**Tujuan**: Memastikan perubahan data di form edit tersinkron ke database.

#### Langkah-Langkah

| No | Aksi | Verifikasi DB |
|---|---|---|
| 1 | Buka halaman **Edit Simpanan** untuk record yang ada | `SELECT * FROM deposits WHERE id=[ID_DEPOSIT]` |
| 2 | Ubah jumlah amount menjadi nilai baru | — |
| 3 | Klik **Simpan** | `SELECT * FROM deposits WHERE id=[ID_DEPOSIT]` — bandingkan |

#### Hasil yang Diharapkan

Nilai `amount` di database harus sesuai dengan nilai baru yang diinput di form.

**Status**: ☐ PASS &nbsp;&nbsp; ☐ FAIL &nbsp;&nbsp; ☐ BLOCKED

**Catatan**:

---

### Skenario 5: Sinkronisasi Hapus Simpanan

**Tujuan**: Memastikan record benar-benar terhapus dari database setelah hapus di UI.

#### Langkah-Langkah

| No | Aksi | Verifikasi DB |
|---|---|---|
| 1 | Catat ID simpanan yang akan dihapus | `SELECT COUNT(*) FROM deposits WHERE id=[ID]` → **1** |
| 2 | Klik tombol **Hapus** di daftar simpanan | — |
| 3 | Konfirmasi penghapusan | `SELECT COUNT(*) FROM deposits WHERE id=[ID]` → harus **0** |

**Status**: ☐ PASS &nbsp;&nbsp; ☐ FAIL &nbsp;&nbsp; ☐ BLOCKED

**Catatan**:

---

## Query Verifikasi Menyeluruh

```sql
-- Verifikasi semua simpanan nasabah terurut dengan benar
SELECT 
    id,
    type,
    amount,
    previous_balance,
    current_balance,
    (previous_balance + amount) AS expected_current,
    CASE WHEN current_balance = (previous_balance + amount) THEN 'OK' ELSE 'MISMATCH' END AS balance_check,
    created_at
FROM deposits
WHERE customer_id = [ID_NASABAH]
ORDER BY type, created_at ASC;
```

---

## Ringkasan Hasil

| Skenario | Status | Tanggal Uji | Penguji |
|---|---|---|---|
| Skenario 1: Simpanan Pertama | — | — | — |
| Skenario 2: Akumulasi Saldo | — | — | — |
| Skenario 3: Wajib + Pinjaman | — | — | — |
| Skenario 4: Edit Simpanan | — | — | — |
| Skenario 5: Hapus Simpanan | — | — | — |

**Kesimpulan Keseluruhan**: —
