<!DOCTYPE html>
<html lang="id" class="h-full bg-[#f7f9f9] text-[#0f1419] antialiased selection:bg-[#1d9bf0] selection:text-white">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'POS Kasir UMKM Event') — Sistem Kasir & Bagi Hasil Event UMKM</title>

    <!-- Google Fonts: Plus Jakarta Sans & Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Vite Styles & Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <script>
        window.__AUTH_USER__ = @json(auth()->user() ? [
            'id' => auth()->user()->id,
            'name' => auth()->user()->name,
            'username' => auth()->user()->username,
            'email' => auth()->user()->email,
            'role' => auth()->user()->role,
            'store_id' => auth()->user()->store_id,
            'store_name' => auth()->user()->store ? auth()->user()->store->name : null,
            'booth_number' => auth()->user()->store ? auth()->user()->store->booth_number : null,
        ] : null);
        window.__ACTIVE_EVENT__ = @json(\App\Models\Event::getActive() ? [
            'id' => \App\Models\Event::getActive()->id,
            'name' => \App\Models\Event::getActive()->name,
            'slug' => \App\Models\Event::getActive()->slug,
            'location' => \App\Models\Event::getActive()->location,
            'is_active' => \App\Models\Event::getActive()->is_active,
        ] : null);
    </script>

    <style>
        [x-cloak] { display: none !important; }
        body { font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; }
    </style>
</head>
<body class="h-full flex overflow-hidden bg-[#f7f9f9]" x-data>
    <!-- Desktop Sidebar (Twitter UI) -->
    @include('components.sidebar')

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col min-w-0 h-screen overflow-hidden">
        <!-- Header / Topbar (Twitter UI) -->
        @include('components.topbar')

        <!-- Scrollable Body Content -->
        <main class="flex-1 overflow-y-auto custom-scrollbar pb-24 lg:pb-10 px-4 sm:px-6 lg:px-8 py-6 max-w-7xl w-full mx-auto">
            @yield('content')
        </main>
    </div>

    <!-- Mobile Bottom Navigation (Twitter UI) -->
    @include('components.bottom-nav')

    <!-- Global Toast Notifications -->
    @include('components.toast')

    <!-- Global Thermal Receipt Modal -->
    @include('components.receipt-modal')
</body>
</html>
