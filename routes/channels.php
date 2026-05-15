<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

/**
 * Presence Channel: chat-global
 *
 * Presence channel adalah channel khusus yang bisa melacak
 * siapa saja yang sedang bergabung (untuk fitur "user online").
 *
 * Syarat masuk: user harus sudah login (Auth::check())
 *
 * Data yang dikembalikan (array) akan dibagikan ke semua member
 * di channel, sehingga semua user bisa melihat daftar yang online.
 */
Broadcast::channel('chat-global', function ($user) {
    // Hanya user yang sudah login yang boleh bergabung
    if ($user) {
        // Data ini akan dikirim ke semua member di presence channel
        // Digunakan untuk menampilkan daftar user online
        return [
            'id'   => $user->id,
            'nama' => $user->name,
        ];
    }

    // Kembalikan false jika user belum login (tolak akses)
    return false;
});
