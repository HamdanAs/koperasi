<p align="center">
  <a href="https://404notfound.fun" target="_blank">
    <img src="https://avatars.githubusercontent.com/u/87377917?s=200&v=4" width="200" alt="404NFID Logo">
  </a>
</p>


## Laravel Koperasi Swamitra 🤑

Adalah sistem manajemen koperasi berbasis web yang dibangun dengan framework Laravel. Aplikasi ini dirancang untuk membantu pengelolaan operasional koperasi simpan pinjam secara digital, mencakup manajemen anggota, simpanan, pinjaman, dan pelacakan pembayaran.

## 🛢️ Skema Database
![Database Schema](docs/ERD.png)

File database bisa didownload di [sini](docs/swamitra.sql).

## ⚡ Instalasi Super Cepat
### 🔥 Persyaratan
- [Docker](https://docs.docker.com/desktop/setup/install/windows-install/)

### 🚀 Menjalankan dengan Docker
Clone repository ini, lalu jalankan:
```sh
docker compose up -d
```


## 🔑 Login
Gunakan akun berikut buat masuk:

| Username         | Password |
|------------------|----------|
| manajer          | admin    |
| teller           | admin    |
| kolektor         | admin    |

## 🧪 Hasil Pengujian
Berikut adalah ringkasan hasil pengujian sistem secara acak untuk tiga metode utama:

- White-Box Testing: logika perhitungan pinjaman dan simpanan berjalan sesuai alur dasar, serta tidak ditemukan error logika yang signifikan pada fungsi internal.
- Black-Box Testing: modul login, manajemen anggota, simpanan, dan pinjaman mampu merespons dengan benar pada skenario umum yang diuji dari sisi antarmuka.
- Gray-Box Testing: data transaksi dari form web berhasil tersimpan ke basis data dan terhubung dengan tabel yang relevan sesuai struktur database yang tersedia.

Detail lengkap tersedia di folder [docs/pengujian](docs/pengujian).

## � Komunikasi Pengujian di GitHub
Proses pengujian dapat dikomunikasikan secara terstruktur melalui GitHub Issues. Contoh alur komunikasi yang umum digunakan adalah:

1. Tester melakukan pengujian Black-Box pada fitur login dan menemukan masalah.
2. Tester membuat issue baru di GitHub dengan judul yang jelas, misalnya: "Bug: Gagal Login untuk Role Kolektor".
3. Di dalam issue, tester mencantumkan langkah pengujian, hasil yang ditemukan, serta bukti error yang muncul.
4. Programmer meninjau laporan tersebut, memperbaiki kode atau konfigurasi yang bermasalah, lalu memberikan balasan di kolom komentar.
5. Tester melakukan pengujian ulang dan melaporkan hasilnya di issue yang sama.
6. Jika masalah sudah teratasi, issue dapat ditutup sebagai selesai.

Contoh komunikasi singkat:

- Tester: "Saya mencoba login menggunakan akun kolektor dengan password admin, namun sistem menampilkan halaman error."
- Programmer: "Masalah ditemukan pada konfigurasi koneksi database. Perbaikan telah diterapkan. Silakan lakukan pengujian ulang."
- Tester: "Setelah diperbaiki, login kolektor berhasil masuk ke dashboard."

## � Screenshot

![Screenshot](docs/screenshots/Screenshot%202025-05-31%20at%2012.02.35.png)
![Screenshot](docs/screenshots/Screenshot%202025-05-31%20at%2012.02.58.png)
![Screenshot](docs/screenshots/Screenshot%202025-05-31%20at%2012.20.44.png)
![Screenshot](docs/screenshots/Screenshot%202025-05-31%20at%2012.20.56.png)
![Screenshot](docs/screenshots/Screenshot%202025-05-31%20at%2012.21.10.png)
![Screenshot](docs/screenshots/Screenshot%202025-05-31%20at%2012.21.21.png)
![Screenshot](docs/screenshots/Screenshot%202025-05-31%20at%2012.21.29.png)
![Screenshot](docs/screenshots/Screenshot%202025-05-31%20at%2012.21.37.png)
![Screenshot](docs/screenshots/Screenshot%202025-05-31%20at%2012.21.44.png)
![Screenshot](docs/screenshots/Screenshot%202025-05-31%20at%2012.21.51.png)
![Screenshot](docs/screenshots/Screenshot%202025-05-31%20at%2012.22.04.png)
