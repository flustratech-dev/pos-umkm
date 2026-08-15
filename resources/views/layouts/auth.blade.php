<!DOCTYPE html>
<html lang="id" class="h-full bg-[#f7f9f9] text-[#0f1419] antialiased selection:bg-[#1d9bf0] selection:text-white">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Autentikasi') — POS Kasir UMKM Event</title>

    <!-- Google Fonts: Plus Jakarta Sans & Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Vite Styles & Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        [x-cloak] { display: none !important; }
        body { font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; }
    </style>
</head>
<body class="min-h-full flex flex-col justify-center py-10 sm:px-6 lg:px-8 bg-[#f7f9f9] text-[#0f1419] relative overflow-x-hidden" x-data>
    <div class="sm:mx-auto sm:w-full sm:max-w-md relative z-10 text-center px-4">
        <!-- Logo (Twitter Blue) -->
        <a href="/" class="inline-flex items-center gap-3">
            <div class="w-12 h-12 rounded-full bg-[#1d9bf0] flex items-center justify-center text-white font-black text-2xl shadow-md shadow-[#1d9bf0]/20">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
            </div>
            <div class="text-left">
                <span class="text-xl font-extrabold tracking-tight text-[#0f1419] block">POS UMKM Event</span>
                <span class="text-xs text-[#1d9bf0] font-semibold">Kasir & Bagi Hasil Multi-Tenant</span>
            </div>
        </a>

        <!-- Active Event Info Badge -->
        <div class="mt-3 inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-white border border-[#eff3f4] text-xs text-[#0f1419] shadow-xs">
            <span class="w-2 h-2 rounded-full bg-[#00ba7c] animate-pulse"></span>
            <span class="text-[#536471]">Event:</span>
            <span class="font-bold text-[#0f1419] truncate max-w-[200px]" x-text="$store.app.getActiveEvent()?.name"></span>
        </div>
    </div>

    <!-- Auth Card Content -->
    <div class="mt-6 sm:mx-auto sm:w-full sm:max-w-lg relative z-10 px-4">
        <div class="bg-white py-8 px-6 sm:px-10 shadow-xl shadow-slate-200/50 rounded-3xl border border-[#eff3f4] text-[#0f1419]">
            @yield('content')
        </div>
    </div>

    <!-- Global Toast Notifications -->
    @include('components.toast')
</body>
</html>
