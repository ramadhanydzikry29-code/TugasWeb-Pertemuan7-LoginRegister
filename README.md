# Tugas Rutin 7 — Sistem Login/Register (PHP Native + JSON)

Sistem login/register sederhana tanpa database; data pengguna disimpan di `data/users.json`.

## Cara menjalankan
```bash
php -S localhost:8000
```
Buka http://localhost:8000 (atau taruh folder di `htdocs` XAMPP / Laragon).
Pastikan folder `data/` dapat ditulis oleh web server.

**Akun contoh:** `budi@example.com` / `Password123`

## Fitur (sesuai requirements)
1. Form registrasi dengan validasi (nama, email, password, konfirmasi password)
2. Validasi email dengan `filter_var(FILTER_VALIDATE_EMAIL)`
3. Password di-hash dengan `password_hash()` dan diverifikasi dengan `password_verify()`
4. Data disimpan di file JSON (`LOCK_EX` saat menulis)
5. Cek duplikasi email saat registrasi
6. Login dengan session (`session_regenerate_id` untuk mencegah session fixation)
7. Dashboard terproteksi (redirect ke login jika belum login)
8. Logout dengan `session_destroy()`
9. Sanitasi input dan escape output dengan `htmlspecialchars()`
10. Pesan error & sukses yang jelas (flash message)

## Bonus
- "Remember Me" dengan cookie (token acak, hanya hash SHA-256 yang disimpan, token dirotasi)
- Halaman edit profil (ubah nama & password)
- Tampilan CSS dark theme yang rapi dan responsif

## Keamanan tambahan
- Token CSRF di semua form (logout juga lewat POST)
- Pesan login generik ("Email atau password salah")
- Cookie `HttpOnly` + `SameSite`
- `data/.htaccess` memblokir akses langsung ke `users.json` (Apache)

## Struktur
```
├── index.php  register.php  login.php  dashboard.php  profile.php  logout.php
├── includes/ (functions.php, header.php, footer.php)
├── assets/style.css
└── data/ (users.json, .htaccess)
```
