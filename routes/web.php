<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatController;

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

// Halaman beranda — arahkan ke chat jika sudah login
Route::get('/', function () {
    return redirect()->route('chat');
});

// Halaman chat — hanya bisa diakses user yang sudah login
// Middleware 'auth' otomatis mengarahkan ke /login jika belum login
Route::get('/chat', [ChatController::class, 'index'])
     ->middleware(['auth', 'verified'])
     ->name('chat');

Route::middleware('auth')->group(function () {

    Route::get('/pesan', [ChatController::class, 'ambilPesan']);

    Route::post('/kirim-pesan', [ChatController::class, 'kirimPesan']);

});
