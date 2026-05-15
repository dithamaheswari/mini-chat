<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migrasi untuk membuat tabel 'pesans'
 * Tabel ini menyimpan semua pesan yang dikirim di chat
 */
return new class extends Migration
{
    /**
     * Jalankan migrasi — buat tabel pesans
     */
    public function up(): void
    {
        Schema::create('pesans', function (Blueprint $table) {
            $table->id(); // ID unik setiap pesan

            // ID pengirim pesan (relasi ke tabel users)
            $table->foreignId('pengirim_id')
                  ->constrained('users')
                  ->onDelete('cascade');

            // Isi pesan yang dikirim
            $table->text('isi');

            // Waktu pesan dibuat dan diperbarui (otomatis oleh Laravel)
            $table->timestamps();
        });
    }

    /**
     * Batalkan migrasi — hapus tabel pesans
     */
    public function down(): void
    {
        Schema::dropIfExists('pesans');
    }
};
