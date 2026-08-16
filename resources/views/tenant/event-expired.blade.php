<!DOCTYPE html>
<html lang="id" class="h-full bg-[#f7f9f9] text-[#0f1419] antialiased selection:bg-[#1d9bf0] selection:text-white">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    
    <title>Event Berakhir — JADISATU</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/favicon-32x32.png') }}?v=3">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('images/favicon-16x16.png') }}?v=3">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css'])
    
    <style>
        body { font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; }
    </style>
</head>
<body class="min-h-full flex flex-col justify-center py-10 sm:px-6 lg:px-8 bg-[#f7f9f9] text-[#0f1419] relative overflow-x-hidden">
    
    <div class="sm:mx-auto sm:w-full sm:max-w-md relative z-10 flex flex-col items-center px-4 mb-6">
        <!-- Logo -->
        <div class="inline-flex items-center justify-center gap-3.5 mb-4">
            <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl overflow-hidden shrink-0 flex items-center justify-center shadow-md bg-white border border-[#eff3f4] p-1.5">
                <img src="{{ asset('images/logo_jadisatu.png') }}" alt="Logo JADISATU" class="w-full h-full object-contain">
            </div>
        </div>
    </div>

    <!-- Main Card -->
    <div class="sm:mx-auto sm:w-full sm:max-w-md relative z-10 px-4">
        <div class="bg-white py-10 px-6 sm:px-10 shadow-xl shadow-slate-200/50 rounded-3xl border border-[#eff3f4] text-center">
            
            <div class="w-16 h-16 bg-[#f7f9f9] border border-[#eff3f4] rounded-full text-[#536471] flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>

            <h2 class="text-2xl font-black text-[#0f1419] mb-2 tracking-tight">Event Sudah Berakhir</h2>
            
            @if(isset($event))
                <div class="inline-block bg-[#e8f5fd] border border-[#bde2f9] text-[#1d9bf0] px-4 py-2 rounded-xl text-sm font-black mb-4 mt-2">
                    {{ $event->name }}
                </div>
                <p class="text-xs text-[#536471] font-semibold mb-6">
                    {{ \Carbon\Carbon::parse($event->start_date)->format('d M Y') }} - {{ \Carbon\Carbon::parse($event->end_date)->format('d M Y') }}
                </p>
            @endif

            <p class="text-sm text-[#0f1419] font-medium leading-relaxed bg-[#f7f9f9] p-4 rounded-2xl border border-[#eff3f4]">
                Halo <strong>{{ $store->name ?? 'Tenant' }}</strong>, terima kasih telah berpartisipasi. Masa aktif event telah selesai dan link akses ini tidak lagi dapat digunakan untuk menerima pesanan.
            </p>

            <div class="mt-8">
                <p class="text-xs text-[#536471] font-medium">
                    JADISATU POS UMKM System &copy; {{ date('Y') }}
                </p>
            </div>
        </div>
    </div>
</body>
</html>
