{{--
|--------------------------------------------------------------------------
| File: resources/views/welcome.blade.php
|--------------------------------------------------------------------------
|
| Ini adalah view untuk "landing page" (halaman utama) aplikasi kita.
| Kita menggunakan Tailwind CSS murni untuk styling, yang dipanggil
| melalui directive @vite.
|
--}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Smart Plants - IoT Monitoring</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />

        {{-- 
        |--------------------------------------------------------------------------
        | Pemanggilan Aset (Vite)
        |--------------------------------------------------------------------------
        |
        | Baris ini sangat penting.
        | @vite memanggil resources/css/app.css (yang berisi Tailwind)
        | dan resources/js/app.js.
        | Ini adalah cara Laravel modern memuat CSS dan JS.
        |
        --}}
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-200">
        
        <div class="min-h-screen flex flex-col">
            
            {{-- 
             | Navigasi ini menggunakan Tailwind:
             | - `bg-white dark:bg-gray-800`: Mengatur warna latar (mendukung dark mode).
             | - `shadow-md`: Memberi bayangan.
             | - `max-w-7xl mx-auto`: Memberi batas lebar maksimum dan menengahkan.
             | - `flex justify-between`: Membuat logo di kiri dan link di kanan.
            --}}
            <nav class="bg-white dark:bg-gray-800 shadow-md sticky top-0 z-50">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between h-16">
                        
                        <div class="flex items-center">
                            <span class="font-bold text-xl text-green-600">🌱 Smart Plants</span>
                        </div>
                        
                        {{-- 
                         | Logika Blade @if, @auth, @else:
                         | - Mengecek apakah route 'login' tersedia.
                         | - Jika user sudah login (@auth), tampilkan link "Dashboard".
                         | - Jika belum (@else), tampilkan link "Log in" dan "Register".
                        --}}
                        <div class="flex items-center">
                            @if (Route::has('login'))
                                <div class="space-x-4">
                                    @auth
                                        <a href="{{ url('/dashboard') }}" class="text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-green-600">Dashboard</a>
                                    @else
                                        <a href="{{ route('login') }}" class="text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-green-600">Log in</a>

                                        @if (Route::has('register'))
                                            <a href="{{ route('register') }}" class="text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-green-600">Register</a>
                                        @endif
                                    @endauth
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </nav>

            {{-- 
             | - `flex-grow`: Membuat konten ini mengisi sisa ruang (mendorong footer ke bawah).
             | - `text-center`: Menengahkan semua teks di dalamnya.
            --}}
            <main class="flex-grow">
                <div class="max-w-7xl mx-auto py-16 px-4 sm:px-6 lg:px-8 text-center">
                    
                    <h1 class="text-4xl md:text-5xl font-extrabold text-gray-900 dark:text-white">
                        Monitor Tanamanmu, di Mana Saja.
                    </h1>
                    
                    <p class="mt-4 text-lg md:text-xl text-gray-600 dark:text-gray-400">
                        Sistem IoT kami membantumu memantau suhu, kelembapan, dan kesehatan tanaman secara real-time.
                    </p>
                    
                    <div class="mt-8">
                        <a href="{{ route('login') }}" 
                           class="inline-block bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-8 rounded-lg text-lg shadow-lg transition duration-300">
                            Mulai Monitoring
                        </a>
                    </div>
                </div>
            </main>

            {{-- 
             | Footer ini akan otomatis menempel di bagian bawah halaman
             | karena parent-nya memiliki `min-h-screen` dan `flex-col`
             | dan <main> memiliki `flex-grow`.
            --}}
            <footer class="bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 text-center text-gray-500 dark:text-gray-400 text-sm">
                    &copy; {{ date('Y') }} Tim Smart Plants.
                </div>
            </footer>
            
        </div>
    </body>
</html>