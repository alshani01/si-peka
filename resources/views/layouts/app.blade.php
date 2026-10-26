<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <!-- STYLE UNTUK MENYEMBUNYIKAN ELEMENT X-CLOAK SEBELUM ALPINE LOAD -->
        <style>
            [x-cloak] { display: none !important; }
        </style>

        <link rel="icon" type="image/png" href="{{ asset('images/logo-kpkp.png') }}">
        <title>{{ config('app.name', 'SI-PEKA') }} @isset($header) - {{ strip_tags($header) }} @endisset</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-slate-100/80 text-gray-800">
        <div class="min-h-screen bg-slate-100/80">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white border-b border-gray-200/80 shadow-sm">
                    <div class="max-w-7xl mx-auto py-5 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>
    </body>
</html>