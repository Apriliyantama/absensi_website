<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased bg-slate-100">

    <div class="min-h-screen flex">
        {{-- Left Section --}}
        <div class="hidden lg:flex w-1/2 bg-slate-800 items-center justify-center p-12">
            <div class="text-white max-w-lg">

                <div class="flex justify-center mb-8">
                    <i class="fa-solid fa-school text-[90px]"></i>
                </div>

                <h1 class="text-4xl font-bold mb-4 text-center">
                    Sistem Absensi Sekolah
                </h1>

                <p class="text-lg opacity-90 text-justify leading-relaxed">
                    Platform terintegrasi untuk pengelolaan absensi sekolah berbasis mobile.
                    memantau kehadiran secara real-time
                    dengan proses verifikasi yang lebih cepat, akurat, dan efisien.
                </p>

                <div class="mt-8 text-sm opacity-75 text-center">
                    Dashboard Admin • Dashboard Guru • Monitoring Kehadiran
                </div>

            </div>
        </div>
        {{-- Right Section --}}
        <div class="w-full lg:w-1/2 flex items-center justify-center p-6 bg-slate-800">
            <div class="w-full max-w-md">
                {{-- <div class="text-center mb-6">
                    <a href="/">
                        <img src="{{ asset('images/icon.png') }}" class="w-20 h-20 mx-auto" alt="Logo">
                    </a>
                </div> --}}
                <div class="bg-white shadow-xl rounded-2xl p-8 border border-gray-200">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </div>
</body>

</html>
