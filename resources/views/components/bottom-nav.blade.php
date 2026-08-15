<!-- Mobile Bottom Navigation Bar (Twitter UI Theme) -->
<div class="lg:hidden fixed bottom-0 left-0 right-0 z-40 bg-white/95 backdrop-blur-lg border-t border-[#eff3f4] px-1.5 py-1 shadow-[0_-4px_20px_rgba(0,0,0,0.03)] safe-area-pb">
    
    <!-- 1. USER (Pemilik Warung) Bottom Nav with Twitter Center Kasir Button -->
    <template x-if="$store.app.currentRole === 'user'">
        <div class="grid grid-cols-5 items-center">
            <!-- 1. Produk -->
            <a 
                href="/user/produk" 
                class="flex flex-col items-center justify-center py-1 transition-colors group cursor-pointer"
                :class="window.location.pathname.includes('/user/produk') ? 'text-[#1d9bf0] font-black' : 'text-[#0f1419] hover:text-[#1d9bf0]'"
            >
                <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                <span class="text-[10px] tracking-tight font-bold">Produk</span>
            </a>

            <!-- 2. Laporan -->
            <a 
                href="/user/laporan" 
                class="flex flex-col items-center justify-center py-1 transition-colors group cursor-pointer"
                :class="window.location.pathname.includes('/user/laporan') ? 'text-[#1d9bf0] font-black' : 'text-[#0f1419] hover:text-[#1d9bf0]'"
            >
                <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                <span class="text-[10px] tracking-tight font-bold">Laporan</span>
            </a>

            <!-- 3. CENTER ELEVATED FAB: Kasir / Checkout (Twitter Blue FAB) -->
            <div class="flex justify-center -mt-6">
                <a 
                    href="/user/kasir" 
                    class="w-14 h-14 rounded-full bg-[#1d9bf0] hover:bg-[#1a8cd8] text-white flex flex-col items-center justify-center shadow-lg shadow-[#1d9bf0]/40 border-4 border-white active:scale-95 transition-all cursor-pointer"
                    :class="window.location.pathname.includes('/user/kasir') ? 'ring-2 ring-[#1d9bf0] ring-offset-2' : ''"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </a>
            </div>

            <!-- 4. Helpdesk -->
            <a 
                href="/user/helpdesk" 
                class="flex flex-col items-center justify-center py-1 transition-colors group cursor-pointer"
                :class="window.location.pathname.includes('/user/helpdesk') ? 'text-[#1d9bf0] font-black' : 'text-[#0f1419] hover:text-[#1d9bf0]'"
            >
                <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                <span class="text-[10px] tracking-tight font-bold">Helpdesk</span>
            </a>

            <!-- 5. Panduan -->
            <a 
                href="/user/panduan" 
                class="flex flex-col items-center justify-center py-1 transition-colors group cursor-pointer"
                :class="window.location.pathname.includes('/user/panduan') ? 'text-[#1d9bf0] font-black' : 'text-[#0f1419] hover:text-[#1d9bf0]'"
            >
                <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                <span class="text-[10px] tracking-tight font-bold">Panduan</span>
            </a>
        </div>
    </template>

    <!-- 2. ADMIN EO Mobile Bottom Nav (Semua 8 Menu Termasuk Multi-Event, SOP & Produk) -->
    <template x-if="$store.app.currentRole === 'admin'">
        <div class="flex items-center justify-between gap-1 overflow-x-auto no-scrollbar py-0.5 px-1">
            <!-- 1. Dashboard -->
            <a 
                href="/admin/dashboard" 
                class="flex flex-col items-center justify-center py-1 px-2.5 min-w-[58px] shrink-0 transition-colors cursor-pointer rounded-xl"
                :class="window.location.pathname === '/admin/dashboard' ? 'text-[#1d9bf0] font-black bg-[#e8f5fd]' : 'text-[#0f1419] hover:text-[#1d9bf0]'"
            >
                <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                <span class="text-[9px] tracking-tight font-bold whitespace-nowrap">Dashboard</span>
            </a>

            <!-- 2. Kelola Multi-Event (BARU UNTUK ADMIN EO) -->
            <a 
                href="/admin/events" 
                class="flex flex-col items-center justify-center py-1 px-2.5 min-w-[58px] shrink-0 transition-colors cursor-pointer rounded-xl"
                :class="window.location.pathname.includes('/events') ? 'text-[#1d9bf0] font-black bg-[#e8f5fd]' : 'text-[#0f1419] hover:text-[#1d9bf0]'"
            >
                <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                <span class="text-[9px] tracking-tight font-bold whitespace-nowrap">Event</span>
            </a>

            <!-- 3. Verif QRIS -->
            <a 
                href="/admin/verifikasi-qris" 
                class="flex flex-col items-center justify-center py-1 px-2.5 min-w-[58px] shrink-0 relative transition-colors cursor-pointer rounded-xl"
                :class="window.location.pathname.includes('/admin/verifikasi-qris') ? 'text-[#1d9bf0] font-black bg-[#e8f5fd]' : 'text-[#0f1419] hover:text-[#1d9bf0]'"
            >
                <svg class="w-5 h-5 mb-0.5 text-[#1d9bf0]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                <span class="text-[9px] tracking-tight font-bold whitespace-nowrap">Verif QRIS</span>
                <template x-if="$store.app.transactions.filter(t => t.status === 'pending_verification').length > 0">
                    <span class="w-2 h-2 rounded-full bg-[#1d9bf0] absolute top-1 right-2.5 animate-pulse"></span>
                </template>
            </a>

            <!-- 4. Produk Semua Warung -->
            <a 
                href="/admin/produk" 
                class="flex flex-col items-center justify-center py-1 px-2.5 min-w-[58px] shrink-0 transition-colors cursor-pointer rounded-xl"
                :class="window.location.pathname.includes('/admin/produk') ? 'text-[#1d9bf0] font-black bg-[#e8f5fd]' : 'text-[#0f1419] hover:text-[#1d9bf0]'"
            >
                <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path></svg>
                <span class="text-[9px] tracking-tight font-bold whitespace-nowrap">Produk</span>
            </a>

            <!-- 5. Warung & Pemilik -->
            <a 
                href="/admin/warung" 
                class="flex flex-col items-center justify-center py-1 px-2.5 min-w-[58px] shrink-0 transition-colors cursor-pointer rounded-xl"
                :class="window.location.pathname.includes('/admin/warung') ? 'text-[#1d9bf0] font-black bg-[#e8f5fd]' : 'text-[#0f1419] hover:text-[#1d9bf0]'"
            >
                <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                <span class="text-[9px] tracking-tight font-bold whitespace-nowrap">Warung</span>
            </a>

            <!-- 6. Laporan & Bagi Hasil -->
            <a 
                href="/admin/laporan" 
                class="flex flex-col items-center justify-center py-1 px-2.5 min-w-[58px] shrink-0 transition-colors cursor-pointer rounded-xl"
                :class="window.location.pathname.includes('/admin/laporan') ? 'text-[#1d9bf0] font-black bg-[#e8f5fd]' : 'text-[#0f1419] hover:text-[#1d9bf0]'"
            >
                <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                <span class="text-[9px] tracking-tight font-bold whitespace-nowrap">Laporan</span>
            </a>

            <!-- 7. Helpdesk -->
            <a 
                href="/admin/helpdesk" 
                class="flex flex-col items-center justify-center py-1 px-2.5 min-w-[58px] shrink-0 transition-colors cursor-pointer rounded-xl"
                :class="window.location.pathname.includes('/admin/helpdesk') ? 'text-[#1d9bf0] font-black bg-[#e8f5fd]' : 'text-[#0f1419] hover:text-[#1d9bf0]'"
            >
                <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
                <span class="text-[9px] tracking-tight font-bold whitespace-nowrap">Helpdesk</span>
            </a>

            <!-- 8. SOP Operasional EO -->
            <a 
                href="/admin/panduan" 
                class="flex flex-col items-center justify-center py-1 px-2.5 min-w-[58px] shrink-0 transition-colors cursor-pointer rounded-xl"
                :class="window.location.pathname.includes('/admin/panduan') ? 'text-[#1d9bf0] font-black bg-[#e8f5fd]' : 'text-[#0f1419] hover:text-[#1d9bf0]'"
            >
                <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                <span class="text-[9px] tracking-tight font-bold whitespace-nowrap">SOP EO</span>
            </a>
        </div>
    </template>

    <!-- 3. SUPER ADMIN Mobile Bottom Nav (Semua Menu Sesuai PRD Full System Visibility) -->
    <template x-if="$store.app.currentRole === 'superadmin'">
        <div class="flex items-center justify-between gap-1 overflow-x-auto no-scrollbar py-0.5 px-1">
            <!-- 1. Dashboard Platform -->
            <a 
                href="/superadmin/dashboard" 
                class="flex flex-col items-center justify-center py-1 px-2.5 min-w-[58px] shrink-0 transition-colors cursor-pointer rounded-xl"
                :class="window.location.pathname === '/superadmin/dashboard' ? 'text-[#1d9bf0] font-black bg-[#e8f5fd]' : 'text-[#0f1419] hover:text-[#1d9bf0]'"
            >
                <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                <span class="text-[9px] tracking-tight font-bold whitespace-nowrap">Dashboard</span>
            </a>

            <!-- 2. Multi-Event -->
            <a 
                href="/superadmin/events" 
                class="flex flex-col items-center justify-center py-1 px-2.5 min-w-[58px] shrink-0 transition-colors cursor-pointer rounded-xl"
                :class="window.location.pathname.includes('/superadmin/events') ? 'text-[#1d9bf0] font-black bg-[#e8f5fd]' : 'text-[#0f1419] hover:text-[#1d9bf0]'"
            >
                <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                <span class="text-[9px] tracking-tight font-bold whitespace-nowrap">Event</span>
            </a>

            <!-- 3. Verifikasi QRIS (Audit) -->
            <a 
                href="/superadmin/verifikasi-qris" 
                class="flex flex-col items-center justify-center py-1 px-2.5 min-w-[58px] shrink-0 relative transition-colors cursor-pointer rounded-xl"
                :class="window.location.pathname.includes('/verifikasi-qris') ? 'text-[#1d9bf0] font-black bg-[#e8f5fd]' : 'text-[#0f1419] hover:text-[#1d9bf0]'"
            >
                <svg class="w-5 h-5 mb-0.5 text-[#1d9bf0]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                <span class="text-[9px] tracking-tight font-bold whitespace-nowrap">Verif QRIS</span>
                <template x-if="$store.app.transactions.filter(t => t.status === 'pending_verification').length > 0">
                    <span class="w-2 h-2 rounded-full bg-[#1d9bf0] absolute top-1 right-2.5 animate-pulse"></span>
                </template>
            </a>

            <!-- 4. Produk Semua Warung -->
            <a 
                href="/superadmin/produk" 
                class="flex flex-col items-center justify-center py-1 px-2.5 min-w-[58px] shrink-0 transition-colors cursor-pointer rounded-xl"
                :class="window.location.pathname.includes('/superadmin/produk') || window.location.pathname === '/admin/produk' ? 'text-[#1d9bf0] font-black bg-[#e8f5fd]' : 'text-[#0f1419] hover:text-[#1d9bf0]'"
            >
                <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path></svg>
                <span class="text-[9px] tracking-tight font-bold whitespace-nowrap">Produk</span>
            </a>

            <!-- 5. Warung & Pemilik -->
            <a 
                href="/superadmin/warung" 
                class="flex flex-col items-center justify-center py-1 px-2.5 min-w-[58px] shrink-0 transition-colors cursor-pointer rounded-xl"
                :class="window.location.pathname.includes('/superadmin/warung') || window.location.pathname === '/admin/warung' ? 'text-[#1d9bf0] font-black bg-[#e8f5fd]' : 'text-[#0f1419] hover:text-[#1d9bf0]'"
            >
                <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                <span class="text-[9px] tracking-tight font-bold whitespace-nowrap">Warung</span>
            </a>

            <!-- 6. Laporan Fee Platform -->
            <a 
                href="/superadmin/laporan" 
                class="flex flex-col items-center justify-center py-1 px-2.5 min-w-[58px] shrink-0 transition-colors cursor-pointer rounded-xl"
                :class="window.location.pathname === '/superadmin/laporan' ? 'text-[#1d9bf0] font-black bg-[#e8f5fd]' : 'text-[#0f1419] hover:text-[#1d9bf0]'"
            >
                <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span class="text-[9px] tracking-tight font-bold whitespace-nowrap">Fee Platform</span>
            </a>

            <!-- 7. Simulasi Kasir -->
            <a 
                href="/superadmin/kasir" 
                class="flex flex-col items-center justify-center py-1 px-2.5 min-w-[58px] shrink-0 transition-colors cursor-pointer rounded-xl"
                :class="window.location.pathname.includes('/kasir') ? 'text-[#1d9bf0] font-black bg-[#e8f5fd]' : 'text-[#0f1419] hover:text-[#1d9bf0]'"
            >
                <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                <span class="text-[9px] tracking-tight font-bold whitespace-nowrap">Kasir POS</span>
            </a>

            <!-- 8. Helpdesk -->
            <a 
                href="/superadmin/helpdesk" 
                class="flex flex-col items-center justify-center py-1 px-2.5 min-w-[58px] shrink-0 transition-colors cursor-pointer rounded-xl"
                :class="window.location.pathname.includes('/helpdesk') ? 'text-[#1d9bf0] font-black bg-[#e8f5fd]' : 'text-[#0f1419] hover:text-[#1d9bf0]'"
            >
                <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
                <span class="text-[9px] tracking-tight font-bold whitespace-nowrap">Helpdesk</span>
            </a>

            <!-- 9. SOP & Panduan -->
            <a 
                href="/superadmin/panduan" 
                class="flex flex-col items-center justify-center py-1 px-2.5 min-w-[58px] shrink-0 transition-colors cursor-pointer rounded-xl"
                :class="window.location.pathname.includes('/panduan') ? 'text-[#1d9bf0] font-black bg-[#e8f5fd]' : 'text-[#0f1419] hover:text-[#1d9bf0]'"
            >
                <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                <span class="text-[9px] tracking-tight font-bold whitespace-nowrap">SOP</span>
            </a>
        </div>
    </template>
</div>
