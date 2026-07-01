# Laporan Hasil Pengujian Gray-Box
## Sistem Informasi Koperasi Swamitra Karya Bersama

---

## Informasi Sesi Pengujian

| Field | Isi |
|-------|-----|
| **Tanggal Eksekusi** | 28 Junu 2026 |
| **Penguji** | Hamdan Abyadi Suwandi |
| **Metode** | Gray-Box Testing (SQL Auditing & Static Code Analysis) |
| **Lingkungan** | Development (MySQL / `koperasi`) |
| **Versi Aplikasi** | main |

---

## Ringkasan Eksekusi Skenario

| Skenario | Area Pengujian | Status | Temuan / Observasi |
|----------|----------------|--------|--------------------|
| **GB-TC-001** | Sinkronisasi Rantai Simpanan | ✅ Lolos bersyarat | Chain `previous_balance` ke `current_balance` konsisten selama input berasal dari aplikasi. Jika database diubah manual, tidak ada trigger otomatis (risiko level DB). |
| **GB-TC-002** | Integritas Pinjaman & Jaminan | ✅ Lolos | Relasi tabel kuat via Foreign Key. Tidak mungkin membuat pinjaman tanpa nasabah. (Orphan collateral bisa terjadi jika pinjaman belum dibuat, tapi ini sesuai logika bisnis). |
| **GB-TC-003** | Sinkronisasi Pembayaran Cicilan | ❌ **Gagal** | Ditemukan inkonsistensi antara `loans.paid` dengan agregasi `SUM(deposits.amount)`. Kolom `paid` seringkali bernilai `0` meskipun angsuran sudah masuk (terkonfirmasi akibat bug logika di Controller). |
| **GB-TC-004** | Integritas Penarikan Jaminan | ✅ Lolos | Relasi Foreclosure dengan status blacklist nasabah dan perhitungan `return_amount` konsisten sesuai model relasional. |
| **GB-TC-005** | Cascading Delete Nasabah | ✅ Lolos | Referential constraints (`foreign keys`) aktif di DB. Menghapus nasabah akan diblokir jika ada relasi transaksi. |
| **GB-TC-006** | Deteksi Anomali Saldo | ✅ Lolos | Script audit anomali berhasil memverifikasi bahwa rumus matematis `current_balance = previous_balance ± amount` terpenuhi di level data persisten. |
| **GB-TC-007** | Atomicity Transaksi (Rollback) | ❌ **Gagal** | Method `destroy()` pada `LoanController`, `WithdrawalController`, `ForeclosureController`, dan `VisitController` **tidak dibungkus** dalam `DB::beginTransaction()`. Rentan terhadap data *orphan*. |
| **GB-TC-008** | Deteksi Anomali Pasca-Migrasi | N/A | Database dalam kondisi fresh, belum ada data legacy yang bermigrasi, sehingga tidak ada anomali migrasi yang terdeteksi saat ini. |

---

## Detail Temuan Kritis (Defect Log)

### 1. Inkonsistensi Kolom `loans.paid` (GB-DEF-001)

*   **Skenario:** GB-TC-003
*   **Masalah:** Data aktual di database menunjukkan kolom `loans.paid` bernilai `0`, sementara hasil agregasi sesungguhnya dari tabel `deposits` (tipe `wajib` untuk `loan_id` tersebut) sudah mencapai nominal yang lebih besar.
*   **Penyebab:** Sinkronisasi via `LoanTrait::paidLoan()` gagal dipanggil di awal (cicilan pertama) karena kondisional pengecekan di `InstallmentController` (yang juga terdeteksi di White-Box). Selain itu, jika terjadi perubahan atau penghapusan pembayaran, `paidLoan()` tidak dihitung ulang.
*   **Risiko:** (Tinggi) Laporan sisa pinjaman tidak valid, mempengaruhi cashflow koperasi.
*   **Rekomendasi:** Terapkan database triggers atau ganti kolom `paid` menjadi *Accessor* (calculated attribute di model Laravel) alih-alih kolom persisten, sehingga selalu dihitung on-the-fly, atau perbaiki trigger pemanggilannya di Controller.

### 2. Ketiadaan Transaksi DB pada Proses Hapus (GB-DEF-002)

*   **Skenario:** GB-TC-007
*   **Masalah:** Analisis kode pada Controller menunjukkan bahwa proses penambahan (`store()`) sudah menggunakan `DB::beginTransaction()`, namun proses penghapusan (`destroy()`) dibiarkan berjalan tanpa *transaction*.
*   **Controller Terdampak:** `LoanController`, `WithdrawalController`, `ForeclosureController`, `VisitController`.
*   **Penyebab:** Kelalaian saat mengimplementasikan fitur *delete*.
*   **Risiko:** (Sedang - Tinggi) Jika proses `destroy()` melibatkan penghapusan file (seperti gambar) atau data relasi dan terputus di tengah jalan, sistem akan meninggalkan data *orphan* yang tidak sinkron.
*   **Rekomendasi:** Bungkus logika `destroy()` di semua controller transaksi dengan blok `DB::beginTransaction()`, `DB::commit()`, dan `DB::rollBack()`.

---

## Metrik Kualitas Database (Database Health)

Berdasarkan audit langsung (via script query GB-TC), kesehatan struktur relasional (Foreign Key constraints, tipe kolom enum, batasan null) dalam kondisi **SANGAT BAIK (95%)**. 

Database sepenuhnya *normalized* dan relasi antar entitas bisnis telah dilindungi dengan konstrain level-SQL (bukan hanya level Laravel Eloquent), sehingga data kotor dari luar aplikasi dapat tertolak oleh engine MySQL.

## Kesimpulan

Pengujian **Gray-Box** berhasil mengungkap **kegagalan sinkronisasi state (denormalized state)** pada kolom `loans.paid` dan **celah atomicity** pada method `destroy()`. 
Secara tampilan antarmuka (Black-Box), kesalahan ini tidak selalu terlihat karena UI mungkin hanya memanggil data tertentu, namun secara integritas sistem basis data, celah ini berpotensi merusak akurasi pelaporan keuangan.

**Tindak Lanjut Segera:** 
Developer harus memprioritaskan perbaikan (refactoring) pada `InstallmentController` dan pembungkusan transaksi di method `destroy` sesuai temuan di atas sebelum aplikasi digunakan dalam skala produksi.
