# 💬 Mini Chat Real-Time (Laravel + Reverb)

Mini Chat adalah aplikasi chat real-time berbasis Laravel yang menggunakan **Laravel Reverb (WebSocket)** untuk mengirim pesan secara langsung tanpa refresh halaman.

---

## Fitur Utama

- Autentikasi pengguna (Login & Register)
- Chat real-time tanpa refresh
- WebSocket menggunakan Laravel Reverb
- Presence Channel (melihat user online)
- Broadcasting event otomatis
- Penyimpanan pesan ke database
- Queue system untuk performa lebih stabil

---

## Teknologi yang Digunakan

- Laravel
- Laravel Breeze
- Laravel Reverb (WebSocket)
- MySQL
- Blade Template
- Alpine.js
- Bootstrap
- Laravel Echo
- Pusher JS

---

## Instalasi Project

### 1. Clone repository
```bash
git clone https://github.com/USERNAME/mini-chat.git
cd mini-chat
2. Install dependency PHP
composer install
3. Install dependency frontend
npm install
npm run build
4. Setup environment
cp .env.example .env
php artisan key:generate
5. Setup database di .env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mini_chat
DB_USERNAME=root
DB_PASSWORD=
6. Buat database
CREATE DATABASE mini_chat;
7. Jalankan migrasi
php artisan migrate
8. Jalankan server Laravel
php artisan serve
9. Jalankan Reverb (WebSocket)
php artisan reverb:start --debug
10. Jalankan Queue Worker
php artisan queue:work


🚀 Cara Menggunakan

Buka browser:

http://localhost:8000
Register 2 akun berbeda
Login di 2 tab/browser berbeda
Kirim pesan
Pesan akan muncul real-time tanpa refresh
