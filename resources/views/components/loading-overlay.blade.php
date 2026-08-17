<!-- High-Performance GPU Off-Thread Circular Logo Spinner -->
<style>
    @keyframes pureGpuSpin {
        0% { transform: translate3d(-50%, -50%, 0) rotate(0deg); }
        100% { transform: translate3d(-50%, -50%, 0) rotate(360deg); }
    }
    @keyframes pureGpuPulse {
        0%, 100% { transform: translate3d(-50%, -50%, 0) scale(0.92); opacity: 0.2; }
        50% { transform: translate3d(-50%, -50%, 0) scale(1.22); opacity: 0.6; }
    }
    .gpu-spinner-overlay {
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        width: 100vw !important;
        height: 100vh !important;
        z-index: 9999999 !important;
        background-color: rgba(255, 255, 255, 0.58) !important;
        backdrop-filter: blur(2px) !important;
        -webkit-backdrop-filter: blur(2px) !important;
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.12s cubic-bezier(0.4, 0, 0.2, 1), visibility 0.12s ease;
        pointer-events: none;
        user-select: none;
        margin: 0 !important;
        padding: 0 !important;
    }
    .gpu-spinner-overlay.is-active {
        opacity: 1 !important;
        visibility: visible !important;
        pointer-events: auto !important;
    }
    .gpu-spinner-pulse-ring {
        position: fixed !important;
        top: 50% !important;
        left: 50% !important;
        width: 70px !important;
        height: 70px !important;
        border-radius: 9999px !important;
        background-color: rgba(29, 155, 240, 0.2) !important;
        will-change: transform, opacity;
        animation: pureGpuPulse 1.3s ease-in-out infinite;
        -webkit-animation: pureGpuPulse 1.3s ease-in-out infinite;
        pointer-events: none !important;
    }
    .gpu-spinner-logo-icon {
        position: fixed !important;
        top: 50% !important;
        left: 50% !important;
        width: 54px !important;
        height: 54px !important;
        border-radius: 9999px !important;
        object-fit: cover !important;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.12) !important;
        border: 2px solid #ffffff !important;
        will-change: transform;
        animation: pureGpuSpin 0.7s linear infinite;
        -webkit-animation: pureGpuSpin 0.7s linear infinite;
        backface-visibility: hidden;
        -webkit-backface-visibility: hidden;
        pointer-events: none !important;
        display: block !important;
    }
</style>

<div id="global-page-loader" class="gpu-spinner-overlay is-active">
    <div class="gpu-spinner-pulse-ring"></div>
    <img src="{{ asset('images/favicon.png') }}" alt="Loading..." class="gpu-spinner-logo-icon">
</div>

<script>
(function() {
    var loader = document.getElementById('global-page-loader');

    function hide() {
        if (loader) {
            loader.classList.remove('is-active');
        }
    }

    function show() {
        if (loader) {
            loader.classList.add('is-active');
        }
    }

    // 1. Hide smoothly on page load
    if (document.readyState === 'complete') {
        hide();
    } else {
        window.addEventListener('load', hide);
        document.addEventListener('DOMContentLoaded', hide);
        setTimeout(hide, 1800); // Safety fallback
    }

    // 2. Show instantly on menu link clicks without triggering heavy JS loops
    document.addEventListener('click', function(e) {
        var anchor = e.target.closest('a');
        if (!anchor) return;
        var href = anchor.getAttribute('href');
        if (!href || href.startsWith('#') || href.startsWith('javascript:') || href.startsWith('mailto:') || href.startsWith('tel:')) return;
        if (anchor.target === '_blank' || anchor.hasAttribute('download') || href.includes('/pdf') || href.includes('/print')) return;
        if (anchor.origin !== window.location.origin) return;
        if (anchor.href === window.location.href) return;

        show();
    }, { passive: true });

    // 3. Show on form submissions
    document.addEventListener('submit', function() {
        show();
    }, { passive: true });

    // 4. Global helpers for async fetch / AJAX operations
    window.showLoading = show;
    window.hideLoading = hide;
})();
</script>
