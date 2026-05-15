<?php

namespace App\Events;

use App\Models\Pesan;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event PesanDikirim
 *
 * Event ini di-broadcast (disiarkan) ke semua user yang sedang
 * membuka halaman chat ketika ada pesan baru yang dikirim.
 *
 * Alur: User kirim pesan → Controller → dispatch event ini
 *       → Reverb siarkan ke semua client → Pesan muncul real-time
 */
class PesanDikirim implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Data pesan yang akan dikirim ke semua client
     * Properti public otomatis dikirim sebagai payload event
     */
    public Pesan $pesan;

    /**
     * Constructor — siapkan data pesan beserta info pengirimnya
     */
    public function __construct(Pesan $pesan)
    {
        // Load relasi pengirim agar nama user ikut terkirim
        $this->pesan = $pesan->load('pengirim');
    }

    /**
     * Tentukan channel mana yang menerima event ini
     *
     * PresenceChannel berarti:
     * - Semua user yang bergabung ke channel ini akan menerima event
     * - Bisa mengetahui siapa saja yang sedang online di channel ini
     */
    public function broadcastOn(): array
    {
        return [
            new PresenceChannel('chat-global'),
        ];
    }

    /**
     * Nama event yang didengar oleh Laravel Echo di frontend
     * Default: nama class dengan namespace (App\Events\PesanDikirim)
     * Kita ubah menjadi lebih pendek agar mudah di frontend
     */
    public function broadcastAs(): string
    {
        return 'pesan.baru';
    }

    /**
     * Data yang dikirim bersama event ke semua client
     */
    public function broadcastWith(): array
    {
        return [
            'id'             => $this->pesan->id,
            'isi'            => $this->pesan->isi,
            'pengirim_id'    => $this->pesan->pengirim_id,
            'pengirim_nama'  => $this->pesan->pengirim->name,
            'waktu'          => $this->pesan->created_at->format('H:i'),
            'waktu_lengkap'  => $this->pesan->created_at->diffForHumans(),
        ];
    }
}
