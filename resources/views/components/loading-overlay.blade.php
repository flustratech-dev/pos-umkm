<!-- Global Minimal Circular Spinning Logo (Pure Logo Spinner, No Card, No Text) -->
<div 
    id="page-preloader"
    x-data="{ preloaderActive: true }"
    x-show="$store.app?.globalLoading || preloaderActive"
    x-init="
        window.addEventListener('load', () => { setTimeout(() => { preloaderActive = false; }, 80); });
        setTimeout(() => { preloaderActive = false; }, 2000);
    "
    x-transition:enter="transition ease-out duration-150"
    x-transition:enter-start="opacity-0 scale-90"
    x-transition:enter-end="opacity-100 scale-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100 scale-100"
    x-transition:leave-end="opacity-0 scale-90"
    class="fixed inset-0 z-[999999] flex items-center justify-center bg-black/20 backdrop-blur-[2px] select-none"
>
    <!-- Pure Circular Spinning Logo -->
    <div class="relative flex items-center justify-center">
        <!-- Subtle pulsing glow -->
        <div class="absolute -inset-2 rounded-full bg-[#1d9bf0]/25 animate-ping"></div>
        <!-- Perfect Circular Spinning Logo -->
        <img 
            src="{{ asset('images/favicon.png') }}" 
            alt="Loading..." 
            class="w-14 h-14 sm:w-16 sm:h-16 rounded-full object-cover shadow-2xl border-2 border-white/80 animate-spin"
        >
    </div>
</div>

<script>
    // Trigger Circular Logo Spinner on page navigation & form submissions
    document.addEventListener('DOMContentLoaded', function() {
        document.addEventListener('click', function(e) {
            const anchor = e.target.closest('a');
            if (!anchor) return;
            const href = anchor.getAttribute('href');
            if (!href || href.startsWith('#') || href.startsWith('javascript:') || href.startsWith('mailto:') || href.startsWith('tel:')) return;
            if (anchor.target === '_blank' || anchor.hasAttribute('download') || href.includes('/pdf') || href.includes('/print')) return;
            if (anchor.origin !== window.location.origin) return;
            if (anchor.href === window.location.href) return;

            if (window.Alpine && window.Alpine.store('app')) {
                window.Alpine.store('app').showLoading();
            } else {
                const el = document.getElementById('page-preloader');
                if (el) el.style.display = 'flex';
            }
        });

        document.addEventListener('submit', function(e) {
            if (window.Alpine && window.Alpine.store('app')) {
                window.Alpine.store('app').showLoading();
            }
        });
    });
</script>
