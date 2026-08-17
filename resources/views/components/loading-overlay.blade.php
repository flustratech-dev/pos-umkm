<!-- Critical GPU Hardware-Accelerated Smooth Spinner Styles -->
<style>
    @keyframes gpuLogoSpinCenter {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    @keyframes gpuPulseRingCenter {
        0%, 100% { transform: translate(-50%, -50%) scale(0.9); opacity: 0.2; }
        50% { transform: translate(-50%, -50%) scale(1.3); opacity: 0.6; }
    }
    .gpu-spin-logo-img {
        will-change: transform;
        animation: gpuLogoSpinCenter 0.75s linear infinite;
        -webkit-animation: gpuLogoSpinCenter 0.75s linear infinite;
        backface-visibility: hidden;
        -webkit-backface-visibility: hidden;
        display: block !important;
    }
    .gpu-pulse-ring-elem {
        will-change: transform, opacity;
        animation: gpuPulseRingCenter 1.4s ease-in-out infinite;
        -webkit-animation: gpuPulseRingCenter 1.4s ease-in-out infinite;
    }
</style>

<!-- Unified Global Circular Logo Spinner (Fixed Dead Center at 50% / 50%) -->
<div 
    id="global-page-loader"
    x-data="{ isVisible: true }"
    x-show="isVisible || $store.app?.globalLoading"
    x-init="
        const hideLoader = () => { 
            setTimeout(() => { 
                isVisible = false; 
                if (window.Alpine && window.Alpine.store('app')) {
                    window.Alpine.store('app').globalLoading = false;
                }
            }, 60); 
        };
        if (document.readyState === 'complete') {
            hideLoader();
        } else {
            window.addEventListener('load', hideLoader);
            document.addEventListener('DOMContentLoaded', hideLoader);
            setTimeout(hideLoader, 2000);
        }
    "
    x-transition:enter="transition ease-out duration-100"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    style="position: fixed !important; top: 0 !important; left: 0 !important; right: 0 !important; bottom: 0 !important; width: 100vw !important; height: 100vh !important; z-index: 999999 !important; background-color: rgba(255, 255, 255, 0.6) !important; backdrop-filter: blur(2px) !important; -webkit-backdrop-filter: blur(2px) !important; margin: 0 !important; padding: 0 !important; pointer-events: auto !important;"
>
    <!-- Soft blue pulsing glow ring (Positioned Dead Center) -->
    <div class="gpu-pulse-ring-elem" style="position: fixed !important; top: 50% !important; left: 50% !important; transform: translate(-50%, -50%) !important; width: 68px !important; height: 68px !important; border-radius: 9999px !important; background-color: rgba(29, 155, 240, 0.25) !important; pointer-events: none !important; z-index: 999999 !important;"></div>

    <!-- Perfect Circular Spinning Logo (Positioned Dead Center) -->
    <div style="position: fixed !important; top: 50% !important; left: 50% !important; transform: translate(-50%, -50%) !important; width: 56px !important; height: 56px !important; display: flex !important; align-items: center !important; justify-content: center !important; z-index: 1000000 !important;">
        <img 
            src="{{ asset('images/favicon.png') }}" 
            alt="Loading..." 
            class="gpu-spin-logo-img"
            style="width: 56px !important; height: 56px !important; border-radius: 9999px !important; object-fit: cover !important; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15) !important; border: 2.5px solid #ffffff !important;"
        >
    </div>
</div>

<script>
    // Seamless Navigation Trigger: Show GPU Spinner immediately when clicking internal links or submitting forms
    (function() {
        document.addEventListener('click', function(e) {
            const anchor = e.target.closest('a');
            if (!anchor) return;
            const href = anchor.getAttribute('href');
            if (!href || href.startsWith('#') || href.startsWith('javascript:') || href.startsWith('mailto:') || href.startsWith('tel:')) return;
            if (anchor.target === '_blank' || anchor.hasAttribute('download') || href.includes('/pdf') || href.includes('/print')) return;
            if (anchor.origin !== window.location.origin) return;
            if (anchor.href === window.location.href) return;

            const loader = document.getElementById('global-page-loader');
            if (loader) {
                loader.style.display = 'block';
                loader.style.opacity = '1';
            }
            if (window.Alpine && window.Alpine.store('app')) {
                window.Alpine.store('app').globalLoading = true;
            }
        });

        document.addEventListener('submit', function(e) {
            const loader = document.getElementById('global-page-loader');
            if (loader) {
                loader.style.display = 'block';
                loader.style.opacity = '1';
            }
            if (window.Alpine && window.Alpine.store('app')) {
                window.Alpine.store('app').globalLoading = true;
            }
        });
    })();
</script>
