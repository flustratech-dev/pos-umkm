<!-- Topbar Header Component (Twitter UI Theme) -->
<header class="sticky top-0 z-30 bg-white/95 backdrop-blur-md border-b border-[#eff3f4] px-4 sm:px-6 py-2.5 transition-all">
    <div class="flex items-center justify-between gap-4">
        <!-- Left: Brand / Active Event Pill -->
        <div class="flex items-center gap-3">
            <a href="/" class="flex items-center gap-2.5 lg:hidden">
                <div class="w-8 h-8 rounded-full bg-[#1d9bf0] flex items-center justify-center text-white font-black text-base shadow-sm shadow-[#1d9bf0]/20">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                </div>
                <span class="font-black text-[#0f1419] tracking-tight text-sm">POS UMKM</span>
            </a>

            <!-- Active Event Badge (Twitter Style Pill) -->
            <div class="hidden sm:flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-[#f7f9f9] border border-[#eff3f4] text-xs text-[#0f1419]">
                <span class="w-2 h-2 rounded-full bg-[#1d9bf0] animate-pulse"></span>
                <span class="font-bold text-[#536471]">Event:</span>
                <span class="font-black text-[#0f1419] truncate max-w-[220px]" x-text="$store.app.getActiveEvent()?.name"></span>
                <svg class="w-3.5 h-3.5 text-[#1d9bf0]" fill="currentColor" viewBox="0 0 24 24"><path d="M22.5 12.5c0-1.58-.875-2.95-2.148-3.6.154-.435.238-.905.238-1.4 0-2.21-1.79-4-4-4-.495 0-.965.084-1.4.238C14.55 2.475 13.18 1.6 11.6 1.6c-1.58 0-2.95.875-3.6 2.148-.435-.154-.905-.238-1.4-.238-2.21 0-4 1.79-4 4 0 .495.084.965.238 1.4C1.575 9.55.7 10.92.7 12.5c0 1.58.875 2.95 2.148 3.6-.154.435-.238.905-.238 1.4 0 2.21 1.79 4 4 4 .495 0 .965-.084 1.4-.238.65 1.273 2.02 2.148 3.6 2.148 1.58 0 2.95-.875 3.6-2.148.435.154.905.238 1.4.238 2.21 0 4-1.79 4-4 0-.495-.084-.965-.238-1.4 1.273-.65 2.148-2.02 2.148-3.6zm-12.28 4.22l-4.22-4.22 1.414-1.414 2.806 2.806 6.806-6.806 1.414 1.414-8.22 8.22z"></path></svg>
            </div>
        </div>

        <!-- Center / Right: Demo Role Switcher & Profile -->
        <div class="flex items-center gap-2 sm:gap-3">
            <!-- Quick Role Switcher (Twitter Pill Group) -->
            <div class="flex items-center bg-[#eff3f4] p-1 rounded-full text-xs font-semibold">
                <button 
                    @click="$store.app.switchRole('user')"
                    type="button" 
                    class="px-3.5 py-1.5 rounded-full transition-all flex items-center gap-1.5 cursor-pointer"
                    :class="$store.app.currentRole === 'user' ? 'bg-[#1d9bf0] text-white shadow-xs font-black' : 'text-[#0f1419] hover:text-[#1d9bf0]'"
                    title="Beralih ke Peran Pemilik Warung"
                >
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                    <span class="hidden md:inline">Warung</span>
                </button>

                <button 
                    @click="$store.app.switchRole('admin')"
                    type="button" 
                    class="px-3.5 py-1.5 rounded-full transition-all flex items-center gap-1.5 relative cursor-pointer"
                    :class="$store.app.currentRole === 'admin' ? 'bg-[#1d9bf0] text-white shadow-xs font-black' : 'text-[#0f1419] hover:text-[#1d9bf0]'"
                    title="Beralih ke Peran Admin EO"
                >
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                    <span class="hidden md:inline">Admin EO</span>
                    <template x-if="$store.app.transactions.filter(t => t.status === 'pending_verification').length > 0">
                        <span class="w-2 h-2 rounded-full bg-[#ff7a00] absolute -top-0.5 -right-0.5"></span>
                    </template>
                </button>

                <button 
                    @click="$store.app.switchRole('superadmin')"
                    type="button" 
                    class="px-3.5 py-1.5 rounded-full transition-all flex items-center gap-1.5 cursor-pointer"
                    :class="$store.app.currentRole === 'superadmin' ? 'bg-[#1d9bf0] text-white shadow-xs font-black' : 'text-[#0f1419] hover:text-[#1d9bf0]'"
                    title="Beralih ke Peran Super Admin"
                >
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                    <span class="hidden md:inline">Super Admin</span>
                </button>
            </div>

            <!-- User Info Pill (Twitter Style) -->
            <div class="flex items-center gap-2 pl-2 border-l border-[#eff3f4]">
                <div class="w-8 h-8 rounded-full bg-[#1d9bf0] text-white flex items-center justify-center font-black text-xs shadow-xs">
                    <span x-text="$store.app.getCurrentUser()?.name?.charAt(0) || 'U'"></span>
                </div>
                <div class="hidden sm:block text-left">
                    <p class="text-xs font-black text-[#0f1419] leading-tight" x-text="$store.app.getCurrentUser()?.name"></p>
                    <p class="text-[10px] text-[#536471] font-bold leading-tight" x-text="$store.app.getRoleLabel($store.app.currentRole)"></p>
                </div>
            </div>
        </div>
    </div>
</header>
