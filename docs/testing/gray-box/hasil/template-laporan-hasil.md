# Template Laporan Hasil Pengujian Gray-Box
## Sistem Informasi Koperasi Swamitra Karya Bersama

---

## Informasi Pengujian

| Field | Detail |
|---|---|
| **ID Laporan** | GBR-[YYYY-MM-DD]-[NNN] |
| **Tanggal Pengujian** | |
| **Penguji** | |
| **Versi Aplikasi** | |
| **Environment** | ☐ Development &nbsp; ☐ Staging &nbsp; ☐ Production |
| **Database** | MySQL — nama database: |

---

## Ringkasan Eksekusi

| ID Test Case | Judul | Status | Durasi |
|---|---|---|---|
| GB-TC-001 | Sinkronisasi Data Simpanan | ☐ PASS ☐ FAIL ☐ BLOCK ☐ SKIP | menit |
| GB-TC-002 | Integritas Relasi Pinjaman & Jaminan | ☐ PASS ☐ FAIL ☐ BLOCK ☐ SKIP | menit |
| GB-TC-003 | Sinkronisasi Cicilan & `loans.paid` | ☐ PASS ☐ FAIL ☐ BLOCK ☐ SKIP | menit |
| GB-TC-004 | Integritas Penarikan Jaminan | ☐ PASS ☐ FAIL ☐ BLOCK ☐ SKIP | menit |
| GB-TC-005 | Cascade Delete Nasabah | ☐ PASS ☐ FAIL ☐ BLOCK ☐ SKIP | menit |
| GB-TC-006 | Konsistensi Saldo Simpanan | ☐ PASS ☐ FAIL ☐ BLOCK ☐ SKIP | menit |
| GB-TC-007 | DB Transaction & Rollback | ☐ PASS ☐ FAIL ☐ BLOCK ☐ SKIP | menit |
| GB-TC-008 | Anomali Data Pasca Migrasi | ☐ PASS ☐ FAIL ☐ BLOCK ☐ SKIP | menit |

---

## Statistik Hasil

| Metrik | Nilai |
|---|---|
| **Total Kasus Uji** | 8 |
| **PASS** | |
| **FAIL** | |
| **BLOCKED** | |
| **SKIP** | |
| **Persentase Kelulusan** | % |

---

## Detail Kegagalan (FAIL)

*Isi tabel ini untuk setiap kasus uji yang FAIL*

### Defect #1

| Field | Detail |
|---|---|
| **ID Defect** | DEF-001 |
| **Test Case** | GB-TC-[XXX] — Skenario [N] |
| **Judul** | |
| **Deskripsi** | |
| **Langkah Reproduksi** | 1. ... |
| **Hasil Aktual** | |
| **Hasil yang Diharapkan** | |
| **Query Bukti** | |
| **Screenshot/Bukti** | |
| **Severity** | ☐ Critical ☐ High ☐ Medium ☐ Low |
| **Priority** | ☐ P1 ☐ P2 ☐ P3 |
| **Status** | ☐ Open ☐ In Progress ☐ Fixed ☐ Rejected |

---

### Defect #2

| Field | Detail |
|---|---|
| **ID Defect** | DEF-002 |
| **Test Case** | |
| **Judul** | |
| **Deskripsi** | |
| **Langkah Reproduksi** | |
| **Hasil Aktual** | |
| **Hasil yang Diharapkan** | |
| **Query Bukti** | |
| **Severity** | |
| **Priority** | |
| **Status** | |

---

## Temuan & Observasi

### Temuan Kritis (Critical Findings)

*Isu yang membutuhkan perbaikan segera*

1. —

### Temuan Sedang (Medium Findings)

*Isu yang perlu diperbaiki dalam iterasi berikutnya*

1. —

### Observasi & Rekomendasi

*Catatan untuk peningkatan kualitas (bukan bug)*

1. —

---

## Hasil Query Audit Database

*Tempel hasil query SQL kritis di sini*

### Hasil: Audit Orphan Records

```sql
-- Query yang dijalankan:
[query]

-- Output:
[hasil]
```

### Hasil: Sinkronisasi `loans.paid`

```sql
-- Query yang dijalankan:
SELECT l.id, l.paid, COALESCE(SUM(d.amount), 0) AS total_deposits...

-- Output (jumlah pinjaman tidak sinkron):
[hasil]
```

### Hasil: Konsistensi Saldo Simpanan

```sql
-- Output (jumlah anomali):
[hasil]
```

### Hasil: Deteksi Anomali Data Umum

```
Nasabah NIK tidak valid: [jumlah]
Pinjaman amount=0: [jumlah]  
Deposit saldo negatif: [jumlah]
Orphan records total: [jumlah]
```

---

## Kesimpulan

### Status Keseluruhan

☐ **LULUS** — Sistem memenuhi semua kriteria integritas data gray-box  
☐ **TIDAK LULUS** — Ditemukan defect kritis yang harus diperbaiki  
☐ **LULUS BERSYARAT** — Lulus dengan catatan/rekomendasi perbaikan

### Ringkasan Naratif

*[Tuliskan narasi singkat tentang kondisi integrasi front-end ↔ database, temuan utama, dan rekomendasi tindak lanjut]*

---

## Tanda Tangan

| Peran | Nama | Tanda Tangan | Tanggal |
|---|---|---|---|
| Penguji | | | |
| Reviewer | | | |
| PIC Sistem | | | |
