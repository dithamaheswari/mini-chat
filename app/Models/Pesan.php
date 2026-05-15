<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model Pesan
 *
 * Merepresentasikan satu pesan yang dikirim di chat.
 * Setiap pesan dimiliki oleh seorang pengguna (pengirim).
 */
class Pesan extends Model
{
    /**
     * Kolom yang boleh diisi secara massal (mass assignment)
     */
    protected $fillable = [
        'pengirim_id', // ID user yang mengirim pesan
        'isi',         // Isi teks pesan
    ];

    /**
     * Relasi: satu pesan dimiliki oleh satu user (pengirim)
     *
     * Cara pakai: $pesan->pengirim->name
     */
    public function pengirim(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pengirim_id');
    }
}
