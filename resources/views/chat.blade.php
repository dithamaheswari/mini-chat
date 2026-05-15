<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mini Chat by Ditha</title>

    {{-- Token CSRF untuk keamanan request POST --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Data user yang sedang login — dibaca oleh chat.js --}}
    <meta name="user-id"   content="{{ auth()->id() }}">
    <meta name="user-name" content="{{ auth()->user()->name }}">

    {{-- Tailwind CSS via Vite --}}
    @vite(['resources/css/app.css', 'resources/js/chat.js'])

    <style>
        /* Animasi pesan baru masuk */
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(10px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .animate-slide-up {
            animation: slideUp 0.2s ease-out;
        }

        /* Sembunyikan scrollbar tapi tetap bisa scroll */
        #kotak-pesan::-webkit-scrollbar { width: 4px; }
        #kotak-pesan::-webkit-scrollbar-track { background: transparent; }
        #kotak-pesan::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 2px; }

        /* Efek ripple tombol kirim */
        #tombol-kirim:active { transform: scale(0.96); }
    </style>
</head>
<body class="bg-gray-100 h-screen flex items-center justify-center p-4">

{{--
    =====================================================
    KONTAINER UTAMA APLIKASI CHAT
    =====================================================
--}}
<div class="w-full max-w-5xl h-[85vh] bg-white rounded-2xl shadow-xl overflow-hidden flex">

    {{-- ================================================
         PANEL KIRI: HEADER + DAFTAR USER ONLINE
         ================================================ --}}
    <aside class="w-64 bg-gray-50 border-r border-gray-200 flex flex-col flex-shrink-0">

        {{-- Header panel kiri --}}
        <div class="p-5 border-b border-gray-200">
            <h1 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                {{-- Ikon chat --}}
                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                </svg>
                Mini Chat
            </h1>
            <p class="text-xs text-gray-400 mt-1">Ditha (2401010055)</p>
        </div>

        {{-- Label jumlah user online --}}
        <div class="px-4 pt-4 pb-2">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-emerald-500 inline-block"></span>
                Online —
                <span id="jumlah-online" class="text-emerald-600 font-semibold">0</span>
            </p>
        </div>

        {{-- Daftar user yang sedang online (diisi oleh chat.js) --}}
        <ul id="daftar-online" class="flex-1 overflow-y-auto px-2 pb-4 space-y-1">
            {{-- Placeholder saat loading --}}
            <li class="text-xs text-gray-400 text-center py-4">Menghubungkan...</li>
        </ul>

        {{-- Info user yang sedang login (pojok bawah sidebar) --}}
        <div class="p-4 border-t border-gray-200 bg-white">
            <div class="flex items-center gap-3">
                {{-- Avatar inisial --}}
                <div class="w-9 h-9 rounded-full bg-emerald-600 flex items-center justify-center text-white font-semibold text-sm">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 truncate">{{ auth()->user()->name }}</p>
                    <p class="text-xs text-emerald-600">● Anda</p>
                </div>
                {{-- Tombol logout --}}
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" title="Logout"
                            class="p-1.5 text-gray-400 hover:text-red-500 transition-colors rounded-lg hover:bg-red-50">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    {{-- ================================================
         PANEL KANAN: AREA CHAT UTAMA
         ================================================ --}}
    <main class="flex-1 flex flex-col min-w-0">

        {{-- Header area chat --}}
        <header class="px-6 py-4 border-b border-gray-200 flex items-center gap-3 bg-white">
            <div class="w-9 h-9 rounded-full bg-emerald-100 flex items-center justify-center">
                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <div>
                <h2 class="text-sm font-semibold text-gray-900">Ruang Chat Global</h2>
                <p class="text-xs text-emerald-600 flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 inline-block"></span>
                    Real-time via Reverb WebSocket
                </p>
            </div>

            {{-- Badge teknologi --}}
            <div class="ml-auto flex items-center gap-2">
                <span class="text-xs bg-purple-100 text-purple-700 px-2.5 py-1 rounded-full font-medium">
                    Presence Channel
                </span>
                <span class="text-xs bg-emerald-100 text-emerald-700 px-2.5 py-1 rounded-full font-medium">
                    Laravel Reverb
                </span>
            </div>
        </header>

        {{-- ========================
             KOTAK PESAN
             ======================== --}}
        <div id="kotak-pesan"
             class="flex-1 overflow-y-auto px-6 py-5 space-y-1 bg-gray-50">
            {{-- Pesan-pesan dirender di sini oleh chat.js --}}

            {{-- Tulisan sambutan sebelum pesan dimuat --}}
            <div class="text-center text-xs text-gray-400 py-2 mb-4" id="label-mulai">
                — Memuat percakapan... —
            </div>
        </div>

        {{-- ========================
             KOLOM INPUT PESAN
             ======================== --}}
        <div class="px-6 py-4 border-t border-gray-200 bg-white">
            <div class="flex items-end gap-3">

                {{-- Avatar pengirim (user yang login) --}}
                <div class="w-9 h-9 rounded-full bg-emerald-600 flex items-center justify-center text-white font-semibold text-sm flex-shrink-0 mb-0.5">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>

                {{-- Input teks pesan --}}
                <div class="flex-1 relative">
                    <textarea
                        id="input-pesan"
                        rows="1"
                        placeholder="Ketik pesan... (Enter untuk kirim)"
                        class="w-full resize-none border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent bg-gray-50 transition-all"
                        style="min-height: 42px; max-height: 120px;"
                    ></textarea>
                </div>

                {{-- Tombol kirim --}}
                <button
                    id="tombol-kirim"
                    type="button"
                    class="w-10 h-10 rounded-xl bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 disabled:cursor-not-allowed text-white flex items-center justify-center transition-all flex-shrink-0 mb-0.5"
                >
                    {{-- Ikon send --}}
                    <svg class="w-4 h-4 translate-x-[1px]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                    </svg>
                </button>
            </div>

            {{-- Petunjuk singkat --}}
            <p class="text-xs text-gray-400 mt-2 ml-12">
                Tekan <kbd class="bg-gray-100 border border-gray-300 rounded px-1 text-gray-600">Enter</kbd> untuk kirim ·
                <kbd class="bg-gray-100 border border-gray-300 rounded px-1 text-gray-600">Shift + Enter</kbd> untuk baris baru
            </p>
        </div>

    </main>
</div>

{{-- Auto-resize textarea saat user mengetik --}}
<script>
    const inputPesan = document.getElementById('input-pesan');
    inputPesan.addEventListener('input', function () {
        this.style.height = 'auto';
        this.style.height = Math.min(this.scrollHeight, 120) + 'px';
    });
</script>

</body>
</html>
