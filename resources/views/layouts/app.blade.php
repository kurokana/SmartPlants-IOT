{{--
|--------------------------------------------------------------------------
| File: resources/views/layouts/app.blade.php
|--------------------------------------------------------------------------
|
| PERUBAHAN TEMA "CERAH":
| 1. Mengganti background body ke 'bg-white' (Putih Bersih).
| 2. Menghapus SEMUA class 'dark:' untuk memaksa TEMA CERAH.
|
--}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        {{-- Latar belakang 'bg-white' untuk tema cerah maksimal --}}
        <div class="min-h-screen bg-white">
            
            {{-- Memuat Navigasi (yang juga akan kita perbaiki) --}}
            @include('layouts.navigation')

            @if (isset($header))
                {{-- Header tetap 'bg-white' tapi dengan border bawah --}}
                <header class="bg-white border-b border-gray-100">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif

            <main>
                {{ $slot }}
            </main>
        </div>
    </body>
</html>