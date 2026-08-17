<!-- Global Branded Circular Logo Spinner Overlay (Realtime Async Actions) -->
<div 
    x-data
    x-show="$store.app?.globalLoading"
    x-transition:enter="transition ease-out duration-150"
    x-transition:enter-start="opacity-0 scale-90"
    x-transition:enter-end="opacity-100 scale-100"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100 scale-100"
    x-transition:leave-end="opacity-0 scale-90"
    x-cloak
    class="fixed inset-0 z-[999999] flex items-center justify-center bg-white/50 backdrop-blur-[2px] select-none"
>
    <!-- Pure Circular Spinning Logo -->
    <div class="relative flex items-center justify-center">
        <!-- Glowing ring pulse behind logo -->
        <div class="absolute -inset-2.5 rounded-full bg-[#1d9bf0]/20 animate-ping pointer-events-none"></div>
        <!-- Perfect Circular Spinning Logo -->
        <img 
            src="{{ asset('images/favicon.png') }}" 
            alt="Loading..." 
            class="w-13 h-13 sm:w-15 sm:h-15 rounded-full object-cover shadow-xl border-2 border-white/90 animate-spin will-change-transform"
        >
    </div>
</div>
