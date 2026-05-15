<?php

use App\Http\Controllers\ChatController;
use Illuminate\Support\Facades\Route;

/**
 * api.php — Rute API
 *
 * Rute ini diakses oleh JavaScript di frontend (chat.js)
 * menggunakan fetch() untuk kirim dan ambil pesan.
 *
 * Semua rute di sini dilindungi middleware 'auth:sanctum'
 * agar hanya user yang login yang bisa mengaksesnya.
 */
Route::middleware('auth')->group(function () {

    // Ambil daftar pesan terbaru (dipanggil saat halaman pertama dibuka)
    Route::get('/pesan', [ChatController::class, 'ambilPesan']);

    // Kirim pesan baru (dipanggil saat user klik tombol Kirim)
    Route::post('/kirim-pesan', [ChatController::class, 'kirimPesan']);

});
