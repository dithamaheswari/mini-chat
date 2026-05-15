/**
 * chat.js — Logika Chat Real-Time
 *
 * File ini menangani:
 * 1. Koneksi WebSocket ke Reverb via Laravel Echo
 * 2. Subscribe ke presence channel untuk daftar user online
 * 3. Mendengarkan event pesan baru
 * 4. Mengirim pesan ke server via API
 * 5. Render pesan ke tampilan
 */

import './bootstrap'; // Import konfigurasi Echo dari bootstrap.js

// ============================================================
// KONFIGURASI AWAL
// ============================================================

// Ambil ID user yang sedang login (dikirim dari Blade via meta tag)
const idUserSaya = parseInt(
    document.querySelector('meta[name="user-id"]').getAttribute('content')
);

// Nama user yang sedang login (untuk ditampilkan di UI)
const namaUserSaya = document.querySelector('meta[name="user-name"]').getAttribute('content');

// ============================================================
// AMBIL REFERENSI ELEMEN HTML
// ============================================================

const kotakPesan   = document.getElementById('kotak-pesan');   // Area tampilan pesan
const inputPesan   = document.getElementById('input-pesan');   // Kolom input teks
const tombolKirim  = document.getElementById('tombol-kirim');  // Tombol kirim
const daftarOnline = document.getElementById('daftar-online'); // Panel user online
const jumlahOnline = document.getElementById('jumlah-online'); // Counter user online

// ============================================================
// FUNGSI: RENDER PESAN KE LAYAR
// ============================================================

/**
 * Tampilkan satu pesan di area chat
 *
 * @param {object} data - Data pesan (id, isi, pengirim_id, pengirim_nama, waktu)
 * @param {boolean} adalahSaya - true jika pesan ini dari user yang login
 */
function tampilkanPesan(data, adalahSaya = false) {
    const div = document.createElement('div');
    div.classList.add('flex', 'gap-3', 'mb-4', 'animate-slide-up');

    if (adalahSaya) {
        // Pesan dari saya — rata kanan, warna hijau
        div.classList.add('justify-end');
        div.innerHTML = `
            <div class="max-w-sm">
                <div class="bg-emerald-600 text-white px-4 py-2.5 rounded-2xl rounded-br-sm text-sm leading-relaxed">
                    ${escapeHtml(data.isi)}
                </div>
                <p class="text-xs text-gray-400 mt-1 text-right pr-1">${data.waktu}</p>
            </div>
        `;
    } else {
        // Pesan dari orang lain — rata kiri, warna putih
        div.innerHTML = `
            <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-700 font-medium text-xs flex-shrink-0 mt-1">
                ${inisial(data.pengirim_nama)}
            </div>
            <div class="max-w-sm">
                <p class="text-xs text-gray-500 mb-1 font-medium">${escapeHtml(data.pengirim_nama)}</p>
                <div class="bg-white border border-gray-200 px-4 py-2.5 rounded-2xl rounded-bl-sm text-sm text-gray-800 leading-relaxed">
                    ${escapeHtml(data.isi)}
                </div>
                <p class="text-xs text-gray-400 mt-1 pl-1">${data.waktu}</p>
            </div>
        `;
    }

    kotakPesan.appendChild(div);
    gulirKeBawah(); // Otomatis scroll ke pesan terbaru
}

// ============================================================
// FUNGSI: KIRIM PESAN
// ============================================================

/**
 * Kirim pesan ke server via API POST
 * Setelah berhasil, tampilkan pesan langsung di layar pengirim
 */
async function kirimPesan() {
    const isi = inputPesan.value.trim();

    // Jangan kirim jika pesan kosong
    if (!isi) return;

    // Nonaktifkan tombol sementara agar tidak double-klik
    tombolKirim.disabled = true;
    inputPesan.value = '';

    try {
        const respons = await fetch('/kirim-pesan', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ isi }),
        });

        const hasil = await respons.json();

        if (hasil.sukses) {
            // Tampilkan pesan milik sendiri langsung (tanpa tunggu broadcast)
            tampilkanPesan(hasil.pesan, true);
        }
    } catch (error) {
        console.error('Gagal mengirim pesan:', error);
        alert('Gagal mengirim pesan. Coba lagi.');
        inputPesan.value = isi; // Kembalikan teks jika gagal
    } finally {
        // Aktifkan kembali tombol dan fokuskan input
        tombolKirim.disabled = false;
        inputPesan.focus();
    }
}

// ============================================================
// FUNGSI: AMBIL RIWAYAT PESAN
// ============================================================

/**
 * Ambil 50 pesan terakhir dari database saat halaman pertama dibuka
 */
async function muatRiwayatPesan() {
    try {
        const respons = await fetch('/pesan', {
            headers: { 'Accept': 'application/json' },
        });
        const daftarPesan = await respons.json();

        // Tampilkan setiap pesan
        daftarPesan.forEach((pesan) => {
            const adalahSaya = pesan.pengirim_id === idUserSaya;
            tampilkanPesan(pesan, adalahSaya);
        });
    } catch (error) {
        console.error('Gagal memuat riwayat pesan:', error);
    }
}

// ============================================================
// FUNGSI: UPDATE DAFTAR USER ONLINE
// ============================================================

/**
 * Perbarui tampilan daftar user yang sedang online
 *
 * @param {object} daftarMember - Objek berisi semua member yang online
 */
function perbaruiDaftarOnline(daftarMember) {
    daftarOnline.innerHTML = ''; // Kosongkan dulu

    const anggota = Object.values(daftarMember);
    jumlahOnline.textContent = anggota.length;

    anggota.forEach((user) => {
        const li = document.createElement('li');
        li.classList.add('flex', 'items-center', 'gap-2.5', 'px-3', 'py-2', 'rounded-lg');

        // Tandai user sendiri
        if (user.id === idUserSaya) {
            li.classList.add('bg-emerald-50');
        }

        li.innerHTML = `
            <div class="w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-700 font-medium text-xs flex-shrink-0">
                ${inisial(user.nama)}
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-800 truncate">
                    ${escapeHtml(user.nama)}
                    ${user.id === idUserSaya ? '<span class="text-xs text-emerald-600">(Anda)</span>' : ''}
                </p>
            </div>
            <span class="w-2 h-2 rounded-full bg-emerald-500 flex-shrink-0"></span>
        `;
        daftarOnline.appendChild(li);
    });
}

// ============================================================
// KONEKSI WEBSOCKET — LARAVEL ECHO + REVERB
// ============================================================

/**
 * Bergabung ke Presence Channel 'chat-global'
 *
 * Presence channel memungkinkan kita:
 * - Mengetahui siapa saja yang online (here)
 * - Mendapat notifikasi saat user baru masuk (joining)
 * - Mendapat notifikasi saat user keluar (leaving)
 * - Mendengarkan event yang di-broadcast (listen)
 */
window.Echo.join('chat-global')

    // Dipanggil SEKALI saat pertama bergabung
    // 'members' berisi semua user yang sedang online
    .here((members) => {
        const objMember = {};
        members.forEach((m) => { objMember[m.id] = m; });
        perbaruiDaftarOnline(objMember);
    })

    // Dipanggil saat ada user BARU yang bergabung
    .joining((user) => {
        const itemLama = document.getElementById(`user-online-${user.id}`);
        if (itemLama) return; // Hindari duplikasi

        // Tambahkan user baru ke daftar online
        const li = document.createElement('li');
        li.id = `user-online-${user.id}`;
        li.classList.add('flex', 'items-center', 'gap-2.5', 'px-3', 'py-2', 'rounded-lg');
        li.innerHTML = `
            <div class="w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-700 font-medium text-xs">
                ${inisial(user.nama)}
            </div>
            <p class="text-sm font-medium text-gray-800 flex-1 truncate">${escapeHtml(user.nama)}</p>
            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
        `;
        daftarOnline.appendChild(li);

        // Perbarui counter
        jumlahOnline.textContent = daftarOnline.children.length;
    })

    // Dipanggil saat ada user yang KELUAR (tutup tab/browser)
    .leaving((user) => {
        const item = document.getElementById(`user-online-${user.id}`);
        if (item) item.remove();

        // Perbarui counter
        jumlahOnline.textContent = daftarOnline.children.length;
    })

    // Dengarkan event 'pesan.baru' yang di-broadcast dari server
    // Event ini dikirim oleh PesanDikirim.php
    .listen('.pesan.baru', (data) => {
        // Hanya tampilkan pesan dari orang lain
        // (pesan milik sendiri sudah ditampilkan saat kirimPesan())
        if (data.pengirim_id !== idUserSaya) {
            tampilkanPesan(data, false);
        }
    });

// ============================================================
// EVENT LISTENER
// ============================================================

// Klik tombol Kirim
tombolKirim.addEventListener('click', kirimPesan);

// Tekan Enter untuk kirim (Shift+Enter = baris baru)
inputPesan.addEventListener('keydown', (e) => {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        kirimPesan();
    }
});

// ============================================================
// FUNGSI BANTUAN (HELPER)
// ============================================================

/** Scroll area pesan ke paling bawah */
function gulirKeBawah() {
    kotakPesan.scrollTop = kotakPesan.scrollHeight;
}

/** Ambil 2 huruf pertama nama sebagai inisial avatar */
function inisial(nama) {
    return nama.split(' ')
               .slice(0, 2)
               .map((kata) => kata[0])
               .join('')
               .toUpperCase();
}

/** Mencegah XSS — konversi karakter HTML berbahaya menjadi aman */
function escapeHtml(teks) {
    const div = document.createElement('div');
    div.appendChild(document.createTextNode(teks));
    return div.innerHTML;
}

// ============================================================
// INISIALISASI — Jalankan saat halaman pertama dibuka
// ============================================================
muatRiwayatPesan();
