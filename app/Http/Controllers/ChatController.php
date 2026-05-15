<?php

namespace App\Http\Controllers;

use App\Events\PesanDikirim;
use App\Models\Pesan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * ChatController
 *
 * Mengelola semua aksi yang berhubungan dengan chat:
 * - Menampilkan halaman chat
 * - Menyimpan dan broadcast pesan baru
 * - Mengambil riwayat pesan
 */
class ChatController extends Controller
{
    /**
     * Tampilkan halaman utama chat
     *
     * Rute: GET /chat
     */
    public function index()
    {
        return view('chat');
    }

    /**
     * Kirim pesan baru
     *
     * Langkah-langkah:
     * 1. Validasi input dari user
     * 2. Simpan pesan ke database
     * 3. Broadcast event ke semua user yang online
     * 4. Kembalikan data pesan sebagai JSON
     *
     * Rute: POST /api/kirim-pesan
     */
    public function kirimPesan(Request $request)
    {
        // Langkah 1: Validasi — isi pesan wajib ada, maks 1000 karakter
        $request->validate([
            'isi' => ['required', 'string', 'max:1000'],
        ]);

        // Langkah 2: Simpan pesan ke database
        $pesan = Pesan::create([
            'pengirim_id' => Auth::id(),
            'isi'         => $request->isi,
        ]);

        // Langkah 3: Broadcast event — kirim ke semua user yang online
        // Event ini akan diterima oleh Laravel Echo di frontend
        broadcast(new PesanDikirim($pesan))->toOthers();
        // toOthers() = jangan kirim ke pengirim sendiri (sudah tampil langsung)

        // Langkah 4: Kembalikan data pesan sebagai respons JSON
        return response()->json([
            'sukses'  => true,
            'pesan'   => [
                'id'            => $pesan->id,
                'isi'           => $pesan->isi,
                'pengirim_id'   => $pesan->pengirim_id,
                'pengirim_nama' => Auth::user()->name,
                'waktu'         => $pesan->created_at->format('H:i'),
            ],
        ]);
    }

    /**
     * Ambil riwayat pesan (50 pesan terakhir)
     *
     * Digunakan saat pertama kali halaman dibuka
     * agar percakapan sebelumnya bisa ditampilkan
     *
     * Rute: GET /api/pesan
     */
    public function ambilPesan()
    {
        $daftarPesan = Pesan::with('pengirim')          // Sertakan data pengirim
                            ->latest()                   // Urutkan terbaru dulu
                            ->take(50)                   // Ambil 50 pesan terakhir
                            ->get()
                            ->reverse()                  // Balik urutan (terlama di atas)
                            ->map(function ($pesan) {    // Ubah format data
                                return [
                                    'id'            => $pesan->id,
                                    'isi'           => $pesan->isi,
                                    'pengirim_id'   => $pesan->pengirim_id,
                                    'pengirim_nama' => $pesan->pengirim->name,
                                    'waktu'         => $pesan->created_at->format('H:i'),
                                ];
                            });

        return response()->json($daftarPesan->values());
    }
}
