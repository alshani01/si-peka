<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <!-- Favicon (Logo di Tab Browser) -->
        <link rel="icon" type="image/png" href="{{ asset('images/logo-kpkp.png') }}">

        <title>{{ config('app.name', 'SI-PEKA') }} - Login</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <!-- Background Elegan Khas Dinas KPKP (Soft Blue Ocean Gradient) -->
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gradient-to-br from-slate-900 via-blue-900 to-indigo-950 relative overflow-hidden">
            
            <!-- Ornamen Glow Lembut -->
            <div class="absolute -top-24 -left-24 w-96 h-96 bg-blue-500/10 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute -bottom-24 -right-24 w-96 h-96 bg-indigo-500/10 rounded-full blur-3xl pointer-events-none"></div>

            <div class="z-10 mb-2 [&_span]:text-white [&_span.text-gray-500]:!text-blue-200">
                <a href="/">
                    <x-application-logo />
                </a>
            </div>

            <div class="w-full sm:max-w-md mt-4 px-6 py-8 bg-white/95 backdrop-blur-md shadow-2xl overflow-hidden sm:rounded-3xl border border-white/20 z-10">
                {{ $slot }}
            </div>
            
            <p class="text-xs text-blue-200/60 mt-6 z-10 font-medium">© {{ date('Y') }} Dinas KPKP DKI Jakarta - SI-PEKA</p>
        </div>
    </body>
</html>