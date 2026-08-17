<!-- Global Minimal Circular Spinning Logo (Clean Bright Backdrop, No Black Overlay) -->
<div 
    id="page-preloader"
    x-data="{ preloaderActive: true }"
    x-show="$store.app?.globalLoading || preloaderActive"
    x-init="
        window.addEventListener('load', () => { setTimeout(() => { preloaderActive = false; }, 60); });
        setTimeout(() => { preloaderActive = false; }, 2000);
    "
    x-transition:enter="transition ease-out duration-100"
    x-transition:enter-start="opacity-0 scale-95"
    x-transition:enter-end="opacity-100 scale-100"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100 scale-100"
    x-transition:leave-end="opacity-0 scale-95"
    class="fixed inset-0 z-[999999] flex items-center justify-center bg-white/40 backdrop-blur-[1.5px] select-none"
>
    <!-- Pure Circular Spinning Logo -->
    <div class="relative flex items-center justify-center">
        <!-- Subtle soft blue glow ring -->
        <div class="absolute -inset-2 rounded-full bg-[#1d9bf0]/15 animate-ping"></div>
        <!-- Perfect Circular Spinning Logo -->
        <img 
            src="{{ asset('images/favicon.png') }}" 
            alt="Loading..." 
            class="w-12 h-12 sm:w-14 sm:h-14 rounded-full object-cover shadow-lg border-2 border-white animate-spin"
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
