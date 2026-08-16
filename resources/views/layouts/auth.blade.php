<!DOCTYPE html>
<html lang="id" class="h-full bg-[#f7f9f9] text-[#0f1419] antialiased selection:bg-[#1d9bf0] selection:text-white">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Autentikasi') — JADISATU</title>

    <!-- Favicon (High Curvature Squircle) -->
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/favicon-32x32.png') }}?v=3">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('images/favicon-16x16.png') }}?v=3">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('images/favicon.png') }}?v=3">
    <link rel="apple-touch-icon" href="{{ asset('images/apple-touch-icon.png') }}?v=3">

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
    <div class="sm:mx-auto sm:w-full sm:max-w-md relative z-10 flex flex-col items-center px-4">
        <!-- Logo & Text Horizontal (Kiri & Kanan) -->
        <a href="/" class="inline-flex items-center justify-center gap-3.5 group">
            <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl overflow-hidden shrink-0 flex items-center justify-center shadow-md bg-white border border-[#eff3f4] p-1.5 transition-transform group-hover:scale-105">
                <img src="{{ asset('images/logo_jadisatu.png') }}" alt="Logo JADISATU" class="w-full h-full object-contain">
            </div>
            <div class="text-left">
                <span class="text-xl sm:text-2xl font-black tracking-tight text-[#0f1419] block leading-tight">JADISATU</span>
                <span class="text-xs text-[#536471] font-medium tracking-wide block mt-0.5">creating stories, crafting moments</span>
            </div>
        </a>

        <!-- Active Event Info Badge (Below Logo & Text) -->
        <div class="mt-4 inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-white border border-[#eff3f4] text-xs text-[#0f1419] shadow-xs">
            <span class="w-2 h-2 rounded-full bg-[#00ba7c] animate-pulse"></span>
            <span class="text-[#536471]">Event:</span>
            <span class="font-bold text-[#0f1419] truncate max-w-[220px]" x-text="$store.app.getActiveEvent()?.name || 'Event Belum Aktif'"></span>
        </div>
    </div>

    <!-- Auth Card Content -->
    <div class="mt-6 sm:mx-auto sm:w-full sm:max-w-md relative z-10 px-4">
        <div class="bg-white py-8 px-6 sm:px-10 shadow-xl shadow-slate-200/50 rounded-3xl border border-[#eff3f4] text-[#0f1419]">
            @yield('content')
        </div>
    </div>

    <!-- Global Toast Notifications -->
    @include('components.toast')
</body>
</html>
