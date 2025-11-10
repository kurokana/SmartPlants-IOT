<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Smart Plants - IoT Monitoring</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />

        {{-- Memanggil CSS (Tailwind) dan JS via Vite --}}
        @vite(['resources/css/app.css', 'resources/js/app.js'])

    </head>
    {{-- PERUBAHAN: bg-white dan hapus text-gray-800 --}}
    <body class="font-sans antialiased bg-white">
        
        <div class="min-h-screen flex flex-col">
            
            {{-- PERUBAHAN: Hapus shadow-md, ganti border-b --}}
            <nav class="bg-white border-b border-gray-100 sticky top-0 z-50">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between h-16">
                        
                        <div class="flex items-center">
                            {{-- PERUBAHAN: Hapus dark:text-primary-500 --}}
                            <span class="font-bold text-xl text-primary-600">Smart Plants</span>
                        </div>
                        
                        <div class="flex items-center">
                            @if (Route::has('login'))
                                <div class="space-x-4">
                                    @auth
                                        {{-- PERUBAHAN: Hapus dark: class --}}
                                        <a href="{{ url('/dashboard') }}" class="text-sm font-medium text-gray-700 hover:text-primary-600">Dashboard</a>
                                    @else
                                        {{-- PERUBAHAN: Hapus dark: class --}}
                                        <a href="{{ route('login') }}" class="text-sm font-medium text-gray-700 hover:text-primary-600">Log in</a>

                                        @if (Route::has('register'))
                                            {{-- PERUBAHAN: Hapus dark: class --}}
                                            <a href="{{ route('register') }}" class="text-sm font-medium text-gray-700 hover:text-primary-600">Register</a>
                                        @endif
                                    @endauth
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </nav>

            <main class="flex-grow">
                <div class="max-w-7xl mx-auto py-16 px-4 sm:px-6 lg:px-8 text-center">
                    
                    {{-- PERUBAHAN: Hapus dark:text-white --}}
                    <h1 class="text-4xl md:text-5xl font-extrabold text-gray-900">
                        Monitor Tanamanmu, di Mana Saja.
                    </h1>
                    
                    {{-- PERUBAHAN: Hapus dark:text-gray-400 --}}
                    <p class="mt-4 text-lg md:text-xl text-gray-600">
                        Sistem IoT kami membantumu memantau suhu, kelembapan, dan kesehatan tanaman secara real-time.
                    </p>
                    
                    <div class="mt-8">
                        {{-- Tombol ini sudah benar menggunakan warna primary --}}
                        <a href="{{ route('dashboard') }}" 
                           class="inline-block bg-primary-600 hover:bg-primary-700 text-black font-bold py-3 px-8 rounded-lg text-lg shadow-lg transition duration-300">
                            Mulai Monitoring
                        </a>
                    </div>
                </div>
            </main>

            {{-- PERUBAHAN: Hapus dark: class, ganti border-gray-100 --}}
            <footer class="bg-white border-t border-gray-100">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 text-center text-gray-500 text-sm">
                    &copy; {{ date('Y') }} Tim Smart Plants.
                </div>
            </footer>
            
        </div>
    </body>
</html>