/**
 * Instant SPA Navigation Engine for POS UMKM
 * Zero-latency navigation with prefetching, DOM morphing, and Alpine.js state preservation.
 */

class InstantNavigator {
    constructor() {
        this.cache = new Map();
        this.maxCacheSize = 25;
        this.isNavigating = false;
        this.progressBar = null;
        this.progressTimeout = null;
        
        this.init();
    }

    init() {
        if (typeof window === 'undefined' || typeof document === 'undefined') return;

        // Create sleek top progress indicator
        this.createProgressBar();

        // 1. Prefetch links on mouseover and touchstart (65ms predictive delay)
        document.addEventListener('mouseover', (e) => this.handlePrefetch(e), { passive: true });
        document.addEventListener('touchstart', (e) => this.handlePrefetch(e), { passive: true });

        // 2. Intercept link clicks for 0ms instantaneous DOM transition
        document.addEventListener('click', (e) => this.handleClick(e));

        // 3. Handle browser back / forward buttons seamlessly
        window.addEventListener('popstate', (e) => {
            this.navigate(window.location.href, false);
        });

        // Cache initial page
        this.cachePage(window.location.href, document.documentElement.outerHTML);
    }

    createProgressBar() {
        let bar = document.getElementById('instant-nav-bar');
        if (!bar) {
            bar = document.createElement('div');
            bar.id = 'instant-nav-bar';
            bar.className = 'fixed top-0 left-0 right-0 h-[2.5px] bg-[#1d9bf0] z-[9999] transform -translate-x-full transition-transform duration-200 ease-out pointer-events-none shadow-[0_0_8px_rgba(29,155,240,0.6)]';
            document.body.appendChild(bar);
        }
        this.progressBar = bar;
    }

    showProgress() {
        if (this.progressTimeout) clearTimeout(this.progressTimeout);
        this.progressTimeout = setTimeout(() => {
            if (this.progressBar) {
                this.progressBar.style.transition = 'transform 300ms ease-out';
                this.progressBar.style.transform = 'translateX(-30%)';
            }
        }, 80);
    }

    hideProgress() {
        if (this.progressTimeout) clearTimeout(this.progressTimeout);
        if (this.progressBar) {
            this.progressBar.style.transition = 'transform 150ms ease-out';
            this.progressBar.style.transform = 'translateX(0%)';
            setTimeout(() => {
                if (this.progressBar) {
                    this.progressBar.style.transition = 'none';
                    this.progressBar.style.transform = 'translateX(-100%)';
                }
            }, 200);
        }
    }

    isEligibleLink(anchor) {
        if (!anchor || anchor.tagName !== 'A') return false;
        const href = anchor.getAttribute('href');
        if (!href || href.startsWith('#') || href.startsWith('javascript:') || href.startsWith('mailto:') || href.startsWith('tel:')) {
            return false;
        }

        // Must be same-origin
        if (anchor.origin !== window.location.origin) return false;

        // Skip downloads, targets, PDF reports, or explicit opt-outs
        if (anchor.hasAttribute('download') || anchor.getAttribute('target') === '_blank' || anchor.hasAttribute('data-no-instant')) {
            return false;
        }

        // Skip receipt prints or PDF downloads
        if (href.includes('/pdf') || href.includes('/print') || href.includes('/download')) {
            return false;
        }

        return true;
    }

    handlePrefetch(e) {
        const anchor = e.target.closest('a');
        if (!this.isEligibleLink(anchor)) return;

        const url = anchor.href;
        if (this.cache.has(url)) return;

        // Predictive background prefetch
        setTimeout(() => {
            if (!this.cache.has(url)) {
                this.fetchPage(url).catch(() => {});
            }
        }, 65);
    }

    async fetchPage(url) {
        if (this.cache.has(url)) {
            return this.cache.get(url);
        }

        const res = await fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-Instant-Nav': 'true'
            }
        });

        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        const html = await res.text();
        this.cachePage(url, html);
        return html;
    }

    cachePage(url, html) {
        if (this.cache.size >= this.maxCacheSize) {
            const firstKey = this.cache.keys().next().value;
            this.cache.delete(firstKey);
        }
        this.cache.set(url, html);
    }

    handleClick(e) {
        // Ignore modified clicks (Ctrl, Cmd, Shift, middle click)
        if (e.defaultPrevented || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) {
            return;
        }

        const anchor = e.target.closest('a');
        if (!this.isEligibleLink(anchor)) return;

        // Don't re-navigate to the exact current URL (unless hash differs)
        if (anchor.href === window.location.href) {
            e.preventDefault();
            return;
        }

        e.preventDefault();
        this.navigate(anchor.href, true);
    }

    async navigate(url, pushState = true) {
        if (this.isNavigating) return;
        this.isNavigating = true;
        this.showProgress();

        try {
            const html = await this.fetchPage(url);
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');

            const currentMain = document.querySelector('main');
            const newMain = doc.querySelector('main');

            if (!currentMain || !newMain) {
                // Fallback to normal navigation if structure differs
                window.location.href = url;
                return;
            }

            // 1. Update Title
            if (doc.title) {
                document.title = doc.title;
            }

            // 2. Micro-fade out transition for super smooth feel
            currentMain.style.transition = 'opacity 60ms ease-out';
            currentMain.style.opacity = '0.7';

            // 3. Swap main content
            currentMain.innerHTML = newMain.innerHTML;

            // 4. Update Navigation active pill states (Sidebar & Bottom Nav)
            this.updateActiveNavStates(new URL(url).pathname);

            // 5. Execute new inline scripts (for data bindings like __INITIAL_PRODUCTS__)
            this.executeNewScripts(doc);

            // 6. Fade back in
            currentMain.style.opacity = '1';
            currentMain.style.transition = 'opacity 80ms ease-in';

            // 7. Re-initialize Alpine.js directives on new content
            if (window.Alpine) {
                window.Alpine.initTree(currentMain);
            }

            // 8. Update browser URL history
            if (pushState) {
                window.history.pushState({ instantNav: true }, '', url);
            }

            // 9. Scroll to top of scroll container smoothly
            const scrollContainer = currentMain.closest('.overflow-y-auto') || window;
            if (scrollContainer.scrollTo) {
                scrollContainer.scrollTo({ top: 0, behavior: 'instant' });
            }

            this.hideProgress();
        } catch (err) {
            console.warn('[InstantNav] Navigation failed, falling back to full load:', err);
            window.location.href = url;
        } finally {
            this.isNavigating = false;
        }
    }

    updateActiveNavStates(pathname) {
        // Update all nav links active states
        document.querySelectorAll('aside a, .fixed a').forEach((link) => {
            const href = link.getAttribute('href');
            if (!href) return;

            const isMatch = href === pathname || (pathname.startsWith(href) && href !== '/' && href !== '/user' && href !== '/admin' && href !== '/superadmin');
            
            // Check if it's bottom dock link or sidebar link
            if (link.closest('.grid-cols-5, .grid-cols-4')) {
                // Bottom nav
                if (isMatch) {
                    link.classList.add('text-[#1d9bf0]', 'font-black', 'bg-[#e8f5fd]');
                    link.classList.remove('text-[#536471]');
                } else {
                    link.classList.remove('text-[#1d9bf0]', 'font-black', 'bg-[#e8f5fd]');
                    link.classList.add('text-[#536471]');
                }
            } else if (link.closest('aside')) {
                // Sidebar
                if (isMatch) {
                    link.classList.add('bg-[#e8f5fd]', 'text-[#1d9bf0]', 'font-bold');
                    link.classList.remove('text-[#0f1419]');
                } else {
                    link.classList.remove('bg-[#e8f5fd]', 'text-[#1d9bf0]', 'font-bold');
                    link.classList.add('text-[#0f1419]');
                }
            }
        });
    }

    executeNewScripts(doc) {
        doc.querySelectorAll('script').forEach((script) => {
            const content = script.textContent;
            if (!content) return;

            // Extract initial JSON bindings and update Alpine store
            if (content.includes('__INITIAL_PRODUCTS__') || content.includes('__INITIAL_TRANSACTIONS__') || content.includes('__INITIAL_STORES__')) {
                try {
                    // Safe evaluated execution of window state variables
                    const fn = new Function(content);
                    fn();

                    if (window.Alpine && window.Alpine.store('app')) {
                        const app = window.Alpine.store('app');
                        if (window.__INITIAL_PRODUCTS__) app.products = window.__INITIAL_PRODUCTS__;
                        if (window.__INITIAL_TRANSACTIONS__) app.transactions = window.__INITIAL_TRANSACTIONS__;
                        if (window.__INITIAL_STORES__) app.stores = window.__INITIAL_STORES__;
                        if (window.__INITIAL_EVENTS__) app.events = window.__INITIAL_EVENTS__;
                        if (window.__INITIAL_TICKETS__) app.helpdeskTickets = window.__INITIAL_TICKETS__;
                    }
                } catch (e) {
                    console.debug('[InstantNav] Script execution notice:', e);
                }
            }
        });
    }
}

// Initialize on page load
if (typeof window !== 'undefined') {
    window.instantNavigator = new InstantNavigator();
}

export default InstantNavigator;
