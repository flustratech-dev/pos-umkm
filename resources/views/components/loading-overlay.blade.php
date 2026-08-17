<!-- Global Branded Circular Logo Spinner Overlay -->
<div 
    x-data 
    x-show="$store.app.globalLoading" 
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    x-cloak
    class="fixed inset-0 z-[99999] flex items-center justify-center bg-black/30 backdrop-blur-[3px] select-none"
>
    <div class="bg-white/95 backdrop-blur-md rounded-3xl p-6 shadow-2xl border border-[#eff3f4] flex flex-col items-center justify-center gap-3.5 min-w-[170px] max-w-[260px] text-center transform transition-all">
        <!-- Circular Spinning Logo -->
        <div class="relative flex items-center justify-center">
            <!-- Subtle glowing ring pulse behind logo -->
            <div class="absolute -inset-1 rounded-full bg-[#1d9bf0]/20 animate-ping"></div>
            <!-- Logo Icon Cropped to Perfect Circle and Spinning -->
            <img 
                src="{{ asset('images/favicon.png') }}" 
                alt="Loading..." 
                class="w-12 h-12 sm:w-14 sm:h-14 rounded-full object-cover shadow-md border-2 border-[#1d9bf0]/40 animate-spin"
            >
        </div>
        
        <!-- Loading Text -->
        <p class="text-xs sm:text-sm font-black text-[#0f1419] tracking-tight" x-text="$store.app.globalLoadingText || 'Memproses...'"></p>
    </div>
</div>
