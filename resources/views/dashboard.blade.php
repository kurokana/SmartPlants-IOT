{{--
|--------------------------------------------------------------------------
| File: resources/views/dashboard.blade.php
|--------------------------------------------------------------------------
|
| Ini adalah view utama untuk dashboard setelah user login.
| Kita menggunakan layout 'app' (layouts.app.blade.php) yang sudah
| menyediakan navigasi dan struktur halaman.
|
| Kita akan mengisi slot 'header' dan slot 'content' utama.
|
--}}

<x-app-layout>
    {{-- Slot 'header' untuk judul halaman --}}
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    {{-- Konten utama halaman (setelah header) --}}
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            {{-- 
            |--------------------------------------------------------------------------
            | Grid Statistik (Grid Layout)
            |--------------------------------------------------------------------------
            |
            | Kita menggunakan Tailwind CSS Grid untuk membuat layout kartu.
            | - `grid`: Mengaktifkan CSS Grid.
            | - `grid-cols-1`: 1 kolom di layar HP (Mobile-first).
            | - `md:grid-cols-2`: 2 kolom di layar medium (Tablet).
            | - `lg:grid-cols-3`: 3 kolom di layar large (Desktop).
            | - `gap-6`: Memberi jarak (gutter) antar kartu.
            |
            | Ini adalah contoh bagus dari Keterbacaan dan Reusabilitas,
            | karena semua kartu di dalamnya akan otomatis tersusun rapi.
            |
            --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                {{-- 
                |--------------------------------------------------------------------------
                | Kartu (Card) Statis
                |--------------------------------------------------------------------------
                |
                | Ini adalah "data pura-pura" (static data) untuk frontend.
                | Perhatikan penggunaan kelas Tailwind untuk styling:
                | - `bg-white dark:bg-gray-800`: Latar belakang kartu (mendukung dark mode).
                | - `overflow-hidden`: Memastikan konten tidak "bocor".
                | - `shadow-lg`: Memberi efek bayangan yang jelas.
                | - `rounded-lg`: Membuat sudut kartu melengkung.
                | - `p-6`: Memberi padding (jarak dalam) di sekeliling konten.
                |
                --}}

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-lg rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-700 dark:text-gray-300">
                        🌡️ Suhu Udara
                    </h3>
                    <p class="mt-2 text-5xl font-bold text-gray-900 dark:text-white">
                        28 <span class="text-3xl">&deg;C</span>
                    </p>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Kondisi ideal
                    </p>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-lg rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-700 dark:text-gray-300">
                        💧 Kelembapan Udara
                    </h3>
                    <p class="mt-2 text-5xl font-bold text-gray-900 dark:text-white">
                        75 <span class="text-3xl">%</span>
                    </p>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Cukup lembap
                    </p>
                </div>
                
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-lg rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-700 dark:text-gray-300">
                        🌱 Kelembapan Tanah
                    </h3>
                    <p class="mt-2 text-5xl font-bold text-gray-900 dark:text-white">
                        45 <span class="text-3xl">%</span>
                    </p>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Tanah mulai kering
                    </p>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-lg rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-700 dark:text-gray-300">
                        🚿 Penyiraman
                    </h3>
                    <div class="mt-2 flex items-center">
                        <span class="text-gray-600 dark:text-gray-400 mr-2">Status: </span>
                        {{-- Contoh status "badge" menggunakan Tailwind --}}
                        <span class="px-3 py-1 inline-flex text-sm font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100">
                            Mati
                        </span>
                    </div>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                        Terakhir Menyiram: 08:00 Pagi Tadi
                    </p>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-lg rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-700 dark:text-gray-300">
                        🌿 Kesehatan Tanaman
                    </h3>
                    <div class="mt-2 flex items-center">
                        <span class="text-gray-600 dark:text-gray-400 mr-2">Status: </span>
                        <span class="px-3 py-1 inline-flex text-sm font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100">
                            Sehat
                        </span>
                    </div>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                        Rekomendasi: Tidak ada
                    </p>
                </div>
                
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-lg rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-700 dark:text-gray-300">
                        📡 Status Perangkat
                    </h3>
                    <div class="mt-2 flex items-center">
                        <span class="text-gray-600 dark:text-gray-400 mr-2">Koneksi: </span>
                        <span class="px-3 py-1 inline-flex text-sm font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100">
                            Online
                        </span>
                    </div>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                        Terakhir update: 1 menit lalu
                    </p>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>