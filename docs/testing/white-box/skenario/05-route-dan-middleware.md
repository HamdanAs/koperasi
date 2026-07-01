# Skenario 05 — Route dan Middleware

Pengujian konfigurasi route internal Laravel dan proteksi middleware.

**Referensi kode:**
- `routes/web.php`
- `routes/api.php`
- `app/Providers/RouteServiceProvider.php`
- `app/Http/Controllers/Auth/LoginController.php`
- `app/Http/Middleware/Authenticate.php`

**Test script:** `tests/Feature/Routes/WebRoutesTest.php`, `tests/Feature/Auth/AuthenticatedAccessTest.php`

---

## A. Route Named — Dashboard & Auth

### WB-ROUTE-01: Route home terdaftar

| Field | Nilai |
|-------|-------|
| **Name** | `home` |
| **URI** | `/dashboard` |
| **Controller** | `HomeController@index` |
| **Middleware** | `auth` |

### WB-ROUTE-02: Route login terdaftar

| Field | Nilai |
|-------|-------|
| **Name** | `login` |
| **URI** | `/login` |
| **Middleware** | `guest` (via LoginController) |

### WB-ROUTE-03: Redirect setelah login

| Field | Nilai |
|-------|-------|
| **Konstanta** | `RouteServiceProvider::HOME = '/dashboard'` |
| **Controller** | `LoginController::$redirectTo` |
| **Harapan** | User redirect ke `/dashboard` |

---

## B. Route Resource — Data Master

| ID | Name prefix | URI | Resource |
|----|-------------|-----|----------|
| WB-ROUTE-04 | `user.*` | `/karyawan` | UserController |
| WB-ROUTE-05 | `customer.*` | `/nasabah` | CustomerController |

### WB-ROUTE-06: Custom resource verb — create

| Field | Nilai |
|-------|-------|
| **Konfigurasi** | `RouteServiceProvider`: `'create' => 'baru'` |
| **Harapan** | Path create = `/karyawan/baru`, bukan `/karyawan/create` |
| **Area uji** | `RouteServiceProvider::boot()` |

---

## C. Route Transaksi

| ID | Name | URI |
|----|------|-----|
| WB-ROUTE-07 | `transaction.loan.*` | `/transaksi/pinjaman` |
| WB-ROUTE-08 | `transaction.installment.*` | `/transaksi/pembayaran` |
| WB-ROUTE-09 | `transaction.deposit.*` | `/transaksi/simpanan` |
| WB-ROUTE-10 | `transaction.withdrawal.*` | `/transaksi/penarikan` |

Setiap grup memiliki route POST cetak: `*.print`

---

## D. Route Kolektor

| ID | Name | URI |
|----|------|-----|
| WB-ROUTE-11 | `collection.visit.*` | `/kolektor/nasabah-bermasalah` |
| WB-ROUTE-12 | `collection.foreclosure.*` | `/kolektor/penarikan-jaminan` |

---

## E. Middleware Auth

### WB-ROUTE-13: Guest redirect dari dashboard

| Field | Nilai |
|-------|-------|
| **Request** | GET `/dashboard` tanpa session |
| **Harapan** | HTTP 302 redirect ke `/login` |
| **Area uji** | Middleware `auth` |

### WB-ROUTE-14: Guest redirect dari route transaksi

| Field | Nilai |
|-------|-------|
| **Request** | GET `/transaksi/pinjaman` |
| **Harapan** | Redirect ke login |

### WB-ROUTE-15: User terautentikasi akses dashboard

| Field | Nilai |
|-------|-------|
| **Setup** | User factory / seeder login |
| **Request** | GET `/dashboard` |
| **Harapan** | HTTP 200 |

---

## F. LoginController

### WB-ROUTE-16: Username field bukan email

| Field | Nilai |
|-------|-------|
| **Method** | `LoginController::username()` return `'username'` |
| **Harapan** | Auth menggunakan kolom `username`, bukan `email` |
| **Area uji** | Trait `AuthenticatesUsers` |

### WB-ROUTE-17: Logout hanya untuk authenticated

| Field | Nilai |
|-------|-------|
| **Middleware** | `guest` except `logout` |
| **Harapan** | Route logout membutuhkan session aktif |

---

## G. Route Profil

| Name | Method | URI | Controller |
|------|--------|-----|------------|
| `profile.show` | GET | `/pengaturan` | HomeController@profile |
| `profile.update` | POST | `/pengaturan` | HomeController@update |
| `profile.truncate` | DELETE | `/pengaturan` | HomeController@truncate |

### WB-ROUTE-18: Semua route profil behind auth

| Field | Nilai |
|-------|-------|
| **Harapan** | Grup middleware `auth` di `web.php` baris 31 |

---

## H. Root Redirect

### WB-ROUTE-19: Root redirect ke home

| Field | Nilai |
|-------|-------|
| **URI** | `/` |
| **Harapan** | Redirect ke route `home` |
| **Area uji** | `routes/web.php` baris 25–27 |

---

## I. API Routes (Scope Terbatas)

| ID | Route | Middleware | Harapan |
|----|-------|------------|---------|
| WB-ROUTE-20 | GET `/api/user` | `auth:sanctum` | Hanya user token valid |

Catatan: Aplikasi utama web-based; API scope minimal.
