# Template Laporan Hasil Pengujian Gray-Box
## Sistem Informasi Koperasi Swamitra Karya Bersama

---

## Informasi Pengujian

| Field | Detail |
|---|---|
| **ID Laporan** | GBR-[YYYY-MM-DD]-[NNN] |
| **Tanggal Pengujian** | 27 Juni 2026 |
| **Penguji** | Hamdan Abyadi Suwandi |
| **Versi Aplikasi** | main |
| **Environment** | ☑ Development &nbsp; ☐ Staging &nbsp; ☐ Production |
| **Database** | MySQL — nama database: `koperasi` |

---

## Ringkasan Eksekusi

| ID Test Case | Judul | Status | Durasi |
|---|---|---|---|
| GB-TC-001 | Sinkronisasi Data Simpanan | ☑ PASS ☐ FAIL ☐ BLOCK ☐ SKIP | 2 menit |
| GB-TC-002 | Integritas Relasi Pinjaman & Jaminan | ☑ PASS ☐ FAIL ☐ BLOCK ☐ SKIP | 1 menit |
| GB-TC-003 | Sinkronisasi Cicilan & `loans.paid` | ☐ PASS ☑ FAIL ☐ BLOCK ☐ SKIP | 2 menit |
| GB-TC-004 | Integritas Penarikan Jaminan | ☑ PASS ☐ FAIL ☐ BLOCK ☐ SKIP | 1 menit |
| GB-TC-005 | Cascade Delete Nasabah | ☑ PASS ☐ FAIL ☐ BLOCK ☐ SKIP | 1 menit |
| GB-TC-006 | Konsistensi Saldo Simpanan | ☑ PASS ☐ FAIL ☐ BLOCK ☐ SKIP | 1 menit |
| GB-TC-007 | DB Transaction & Rollback | ☐ PASS ☑ FAIL ☐ BLOCK ☐ SKIP | 2 menit |
| GB-TC-008 | Anomali Data Pasca Migrasi | ☐ PASS ☐ FAIL ☐ BLOCK ☑ SKIP | 0 menit |

---

## Statistik Hasil

| Metrik | Nilai |
|---|---|
| **Total Kasus Uji** | 8 |
| **PASS** | 5 |
| **FAIL** | 2 |
| **BLOCKED** | 0 |
| **SKIP** | 1 (Belum ada data migrasi legacy) |
| **Persentase Kelulusan** | 71.4% (5 dari 7 test case dieksekusi) |

---

## Detail Kegagalan (FAIL)

*Isi tabel ini untuk setiap kasus uji yang FAIL*

### Defect #1

| Field | Detail |
|---|---|
| **ID Defect** | GB-DEF-001 |
| **Test Case** | GB-TC-003 — Sinkronisasi Cicilan |
| **Judul** | Inkonsistensi perhitungan `loans.paid` |
| **Deskripsi** | Kolom `paid` pada tabel `loans` tidak terupdate saat cicilan `wajib` masuk. |
| **Langkah Reproduksi** | 1. Input simpanan `wajib` dengan loan_id valid via aplikasi<br>2. Cek database tabel `loans` pada kolom `paid` |
| **Hasil Aktual** | `paid` bernilai 0 sementara agregasi `SUM(amount)` dari tabel `deposits` > 0 |
| **Hasil yang Diharapkan** | Nilai `paid` harus selalu sama dengan total angsuran `wajib` untuk pinjaman terkait. |
| **Query Bukti** | `SELECT l.id, l.paid, SUM(d.amount) FROM loans l JOIN deposits d ...` |
| **Screenshot/Bukti** | Bukti tercatat di logs tinker. |
| **Severity** | ☑ Critical ☐ High ☐ Medium ☐ Low |
| **Priority** | ☑ P1 ☐ P2 ☐ P3 |
| **Status** | ☑ Open ☐ In Progress ☐ Fixed ☐ Rejected |

---

### Defect #2

| Field | Detail |
|---|---|
| **ID Defect** | GB-DEF-002 |
| **Test Case** | GB-TC-007 — DB Transaction |
| **Judul** | Tidak ada proteksi rollback pada operasi Hapus |
| **Deskripsi** | Fungsi `destroy()` pada Controller `Loan`, `Withdrawal`, `Foreclosure`, `Visit` tidak dilindungi `DB::beginTransaction()`. |
| **Langkah Reproduksi** | 1. Analisis kode source controller<br>2. Cari method `destroy` |
| **Hasil Aktual** | Tidak ditemukan pemanggilan transaksi database. |
| **Hasil yang Diharapkan** | Semua operasi modifikasi database, terutama penghapusan berlapis, harus dibungkus DB Transaction. |
| **Query Bukti** | Static code analysis. |
| **Severity** | ☐ Critical ☑ High ☐ Medium ☐ Low |
| **Priority** | ☐ P1 ☑ P2 ☐ P3 |
| **Status** | ☑ Open ☐ In Progress ☐ Fixed ☐ Rejected |

---

## Temuan & Observasi

### Temuan Kritis (Critical Findings)

*Isu yang membutuhkan perbaikan segera*

1.  **Bug Logika `loans.paid`**: Terjadi inkonsistensi yang menyebabkan sisa pinjaman yang ditampilkan aplikasi mungkin tidak sesuai dengan aktual di database.

### Temuan Sedang (Medium Findings)

*Isu yang perlu diperbaiki dalam iterasi berikutnya*

1.  **Absennya DB Transactions pada Destroy**: Rentan menyebabkan data orphan jika terjadi *partial failure* (misalnya proses delete DB berhasil, tapi penghapusan file gambar gagal, yang menyebabkan fatal error dan data relasi terputus).

### Observasi & Rekomendasi

*Catatan untuk peningkatan kualitas (bukan bug)*

1.  **Level Database triggers**: Jika ingin garansi absolute atas kalkulasi saldo (seperti sinkronisasi `loans.paid`), pertimbangkan untuk menambahkan trigger MySQL, tidak hanya mengandalkan logic di PHP Controller.

---

## Hasil Query Audit Database

*Tempel hasil query SQL kritis di sini*

### Hasil: Audit Orphan Records

```sql
-- Query yang dijalankan:
SELECT co.id, co.name, co.value, co.customer_id 
FROM koperasi.collaterals co
LEFT JOIN koperasi.loans l ON l.collateral_id = co.id
WHERE l.id IS NULL;

-- Output:
[ { id: 3, name: 'BPKB Mobil', value: 80000000, customer_id: 3 } ] 
-- (Catatan: ini normal, merupakan collateral yang diinput tapi pinjaman belum dibuat)
```

### Hasil: Sinkronisasi `loans.paid`

```sql
-- Query yang dijalankan:
SELECT l.id, l.paid, COALESCE(SUM(d.amount), 0) AS total_deposits
FROM loans l
LEFT JOIN deposits d ON d.loan_id = l.id AND d.type = 'wajib'
GROUP BY l.id;

-- Output (jumlah pinjaman tidak sinkron):
ID: 1 | paid: 0 | total_deposits: 833332 (TIDAK SINKRON)
ID: 2 | paid: 0 | total_deposits: 500000 (TIDAK SINKRON)
```

### Hasil: Konsistensi Saldo Simpanan

```sql
-- Output (jumlah anomali):
Skenario normal: 10 data
Anomali deteksi: 0 (Rumus saldo simpanan secara database matematis benar saat diuji).
```

### Hasil: Deteksi Anomali Data Umum

```text
Nasabah NIK tidak valid: 0
Pinjaman amount=0: 0  
Deposit saldo negatif: 0
Orphan records total (selain collateral): 0
```

---

## Kesimpulan

### Status Keseluruhan

☐ **LULUS** — Sistem memenuhi semua kriteria integritas data gray-box  
☑ **TIDAK LULUS** — Ditemukan defect kritis yang harus diperbaiki  
☐ **LULUS BERSYARAT** — Lulus dengan catatan/rekomendasi perbaikan

### Ringkasan Naratif

Secara struktural, tabel relasional database koperasi ini sudah cukup matang dan terlindungi dengan konstrain *foreign key* yang tepat. Namun, pada level *state sinkronisasi* dan *atomicity* Controller, sistem **Gagal** (Tidak Lulus). Bug logika di aplikasi gagal memperbarui secara proporsional kolom `loans.paid`, yang jika dibiarkan dapat berpotensi merugikan pelaporan nasabah. Selain itu, belum adanya implementasi `DB::beginTransaction()` pada fungsi penghapusan memicu risiko fatal database corruption apabila aplikasi *crash* di tengah jalan. Developer diwajibkan menyelesaikan dua temuan defek ini sebelum *go-live*.

---

## Tanda Tangan

| Peran | Nama | Tanda Tangan | Tanggal |
|---|---|---|---|
| Penguji | Hamdan Abyadi Suwandi | [TTD] | 27 Juni 2026 |
| Reviewer | Lead Developer | [TTD] | 27 Juni 2026 |
| PIC Sistem | Koperasi Swamitra | [TTD] | 27 Juni 2026 |
