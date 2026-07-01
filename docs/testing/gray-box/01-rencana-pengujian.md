# 01 — Rencana Pengujian Gray-Box
## Sistem Informasi Koperasi Swamitra Karya Bersama

---

## 1. Tujuan Pengujian

Dokumen ini mendefinisikan rencana pengujian gray-box yang bertujuan untuk:

1. **Memvalidasi sinkronisasi data** antara antarmuka pengguna (Blade template) dan basis data MySQL — memastikan bahwa setiap aksi di UI menghasilkan perubahan data yang tepat di database.
2. **Memverifikasi integritas relasi** antar tabel — memastikan foreign key constraints, cascade delete/update, dan nullable relationships berjalan benar.
3. **Mendeteksi anomali data** yang mungkin terjadi selama migrasi database atau kondisi race condition pada transaksi konkuren.
4. **Menguji mekanisme DB Transaction** — memastikan `DB::beginTransaction()`, `DB::commit()`, dan `DB::rollBack()` melindungi integritas data saat terjadi kegagalan.

---

## 2. Ruang Lingkup Pengujian

### 2.1 Dalam Ruang Lingkup (In-Scope)

| Area | Deskripsi |
|---|---|
| Lapisan Antarmuka | Form input, tabel data (DataTables AJAX), tombol aksi |
| Lapisan Controller | Logika bisnis di semua Controller (CRUD) |
| Lapisan Model | Eloquent ORM, relasi, dan atribut |
| Lapisan Basis Data | Skema tabel, foreign key, constraint, trigger migrasi |
| Integrasi DB | Sinkronisasi antara form submission dan baris MySQL |

### 2.2 Di Luar Ruang Lingkup (Out-of-Scope)

| Area | Alasan |
|---|---|
| Pengujian keamanan (penetration test) | Dilakukan secara terpisah |
| Pengujian performa/load | Di luar ruang lingkup gray-box |
| Pengujian unit isolasi | Dilakukan di suite white-box |

---

## 3. Arsitektur Sistem yang Diuji

```
[Browser / Pengguna]
        │
        ▼
[Blade Template Views]          ← Lapisan Front-End
        │
        ▼
[Routes (web.php)]              ← Routing Laravel
        │
        ▼
[HTTP Controllers]              ← Logika Bisnis
   ├── LoanController
   ├── DepositController
   ├── InstallmentController
   ├── ForeclosureController
   ├── CustomerController
   └── WithdrawalController
        │
        ▼
[Eloquent ORM / Models]         ← Abstraksi Data
   ├── Customer  ──────────────────┐
   ├── Loan ──────────────────┐    │
   ├── Deposit                │    │
   ├── Collateral             │    │
   ├── Foreclosure            │    │
   └── Visit                  │    │
        │                     │    │
        ▼                     │    │
[MySQL Database]              │    │
   ├── customers ◄────────────┼────┘
   ├── loans ◄────────────────┘
   ├── deposits
   ├── collaterals
   ├── foreclosures
   └── visits
```

### Titik Integrasi Kritis (Integration Points)

Pengujian gray-box berfokus pada **titik antara layer Controller → Model → Database**:

| Titik Integrasi | Komponen | Risiko |
|---|---|---|
| Form Pinjaman → `loans` + `collaterals` | `LoanController::store()` | Atomicity transaksi 2 tabel |
| Form Simpanan → `deposits` + `loans.paid` | `DepositController::store()` | Kalkulasi `current_balance` |
| Cicilan → `deposits` + `loans.paid` | `InstallmentController::store()` | Sinkronisasi `paidLoan()` |
| Penarikan Jaminan → `foreclosures` + `customers.status` | `ForeclosureController::store()` | Status blacklist |
| Hapus Pinjaman → `deposits` (cascade) | `LoanController::destroy()` | Cascade delete manual |

---

## 4. Pendekatan Pengujian Gray-Box

### 4.1 Teknik yang Digunakan

| Teknik | Deskripsi | Aplikasi |
|---|---|---|
| **Database State Inspection** | Memeriksa state database sebelum & sesudah aksi UI | Semua skenario |
| **Transaction Boundary Testing** | Menguji perilaku saat transaksi commit vs rollback | GB-TC-007 |
| **Relational Integrity Testing** | Menguji constraint FK & cascade | GB-TC-002, GB-TC-005 |
| **Data Flow Tracing** | Menelusuri aliran data dari form → DB | GB-TC-001, GB-TC-003 |
| **Boundary Value Analysis** | Nilai batas untuk field numerik & enum | GB-TC-001, GB-TC-006 |
| **Migration Anomaly Detection** | Verifikasi data pasca migrasi | GB-TC-008 |

### 4.2 Pengetahuan Internal yang Digunakan (Gray-Box Knowledge)

Penguji memiliki akses ke:
- ✅ Skema migrasi database lengkap (`database/migrations/`)
- ✅ Kode sumber Controller (`app/Http/Controllers/`)
- ✅ Kode Model Eloquent (`app/Models/`)
- ✅ Logika `LoanTrait::paidLoan()` (`app/Traits/LoanTrait.php`)
- ✅ Konfigurasi database (`.env` / `config/database.php`)
- ❌ Query optimizer internal MySQL (tidak diakses langsung)

---

## 5. Lingkungan Pengujian

### 5.1 Konfigurasi

| Parameter | Nilai |
|---|---|
| **Server** | Laragon (Apache + PHP) |
| **Database** | MySQL |
| **Framework** | Laravel |
| **Browser** | Chrome / Firefox (versi terbaru) |
| **Tools DB Inspection** | phpMyAdmin / MySQL Workbench / `mysql` CLI |

### 5.2 Data Uji (Test Fixtures)

Sebelum pengujian dimulai, pastikan:
- [ ] Database sudah di-migrate: `php artisan migrate:fresh`
- [ ] Seeder (jika ada) sudah dijalankan: `php artisan db:seed`
- [ ] Minimal ada **1 nasabah aktif** dengan NIK, nomor rekening unik
- [ ] Minimal ada **1 data pinjaman** yang sudah tersimpan

### 5.3 Akses Database untuk Verifikasi

Penguji menggunakan query SQL langsung untuk memverifikasi state database:

```sql
-- Cek koneksi ke database koperasi
USE koperasi_swamitra;

-- Melihat semua tabel
SHOW TABLES;

-- Template query verifikasi umum
SELECT * FROM [nama_tabel] ORDER BY id DESC LIMIT 10;
```

---

## 6. Daftar Skenario Pengujian

| ID | Judul | Modul | Prioritas | Status |
|---|---|---|---|---|
| GB-TC-001 | Sinkronisasi Data Simpanan ke Database | Simpanan | 🔴 Tinggi | Belum Diuji |
| GB-TC-002 | Integritas Relasi Pinjaman & Jaminan | Pinjaman | 🔴 Tinggi | Belum Diuji |
| GB-TC-003 | Sinkronisasi Pembayaran Cicilan & `loans.paid` | Cicilan | 🔴 Tinggi | Belum Diuji |
| GB-TC-004 | Integritas Data Penarikan Jaminan | Penarikan Jaminan | 🟠 Sedang | Belum Diuji |
| GB-TC-005 | Cascade Delete Nasabah & Data Terkait | Nasabah | 🟠 Sedang | Belum Diuji |
| GB-TC-006 | Konsistensi Kalkulasi Saldo Simpanan | Simpanan | 🔴 Tinggi | Belum Diuji |
| GB-TC-007 | Mekanisme DB Transaction & Rollback | Multi-Modul | 🔴 Tinggi | Belum Diuji |
| GB-TC-008 | Anomali Data Pasca Migrasi Database | Sistem | 🟠 Sedang | Belum Diuji |

---

## 7. Kriteria Kelulusan (Pass/Fail Criteria)

### 7.1 Kriteria Lulus (PASS)

- Data yang tersimpan di database **identik** dengan data yang diinput melalui UI
- Relasi foreign key **tidak dilanggar** oleh operasi apapun
- Saldo simpanan (`current_balance`) **selalu akurat** setelah setiap transaksi
- Field `loans.paid` **tersinkronisasi** setiap kali cicilan ditambah/dihapus
- `DB::rollBack()` **tidak meninggalkan data parsial** di database
- Cascade delete/update **berjalan sesuai** definisi migrasi

### 7.2 Kriteria Gagal (FAIL)

- Terdapat perbedaan antara data di UI dan data aktual di database
- Foreign key constraint terlanggar tanpa penanganan error yang tepat
- `current_balance` bernilai salah setelah operasi simpan/ubah/hapus
- Data tersimpan sebagian (partial data) saat terjadi exception
- Relasi orphan ditemukan (record child tanpa parent)

---

## 8. Pelaporan Hasil

Semua hasil pengujian didokumentasikan menggunakan template di:
```
docs/testing/gray-box/hasil/template-laporan-hasil.md
```

Setiap kasus uji memiliki status: **PASS** | **FAIL** | **BLOCKED** | **SKIP**
