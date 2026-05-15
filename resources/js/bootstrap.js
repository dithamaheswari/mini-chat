/**
 * bootstrap.js — Konfigurasi Awal Laravel
 *
 * File ini dijalankan sebelum semua file JS lainnya.
 * Di sini kita konfigurasi:
 * - Axios (library HTTP request) dengan token CSRF
 * - Laravel Echo (client WebSocket) yang terhubung ke Reverb
 */

import axios from 'axios';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

// ============================================================
// KONFIGURASI AXIOS
// ============================================================

// Jadikan axios tersedia secara global
window.axios = axios;

// Sertakan token CSRF di setiap request
// Ini mencegah serangan Cross-Site Request Forgery
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// ============================================================
// KONFIGURASI LARAVEL ECHO + REVERB
// ============================================================

/**
 * Pusher JS digunakan sebagai driver oleh Laravel Echo
 * untuk berkomunikasi dengan Reverb (WebSocket server).
 *
 * Walaupun Reverb bukan Pusher, protokolnya kompatibel.
 */
window.Pusher = Pusher;

/**
 * Inisialisasi Laravel Echo
 *
 * Echo bertugas:
 * - Membuka koneksi WebSocket ke Reverb
 * - Subscribe ke channel (presence, private, public)
 * - Mendengarkan event yang di-broadcast dari server
 */
window.Echo = new Echo({
    broadcaster: 'reverb',  // Gunakan Reverb sebagai server WebSocket

    // Konfigurasi koneksi — dibaca dari variabel VITE_ di .env
    key:      import.meta.env.VITE_REVERB_APP_KEY,
    wsHost:   import.meta.env.VITE_REVERB_HOST,
    wsPort:   import.meta.env.VITE_REVERB_PORT ?? 8080,
    wssPort:  import.meta.env.VITE_REVERB_PORT ?? 443,
    scheme:   import.meta.env.VITE_REVERB_SCHEME ?? 'http',

    // Opsi tambahan
    forceTLS:              (import.meta.env.VITE_REVERB_SCHEME ?? 'http') === 'https',
    enabledTransports:     ['ws', 'wss'], // Protokol WebSocket yang digunakan
    disableStats:          true,          // Nonaktifkan statistik Pusher (tidak perlu)
    authEndpoint:          '/broadcasting/auth', // Endpoint otorisasi channel private/presence
});
