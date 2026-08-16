<!-- Mobile Bottom Navigation Bar (Twitter UI Theme - Production Ready) -->
@auth
    @php
        $user = auth()->user();
        $rolePrefix = $user->isSuperAdmin() ? 'superadmin' : ($user->isAdmin() ? 'admin' : 'user');
    @endphp
    <div 
        x-data="{ adminMenuOpen: false }"
        class="lg:hidden"
    >
        <div class="fixed bottom-0 left-0 right-0 z-40 bg-white/95 backdrop-blur-lg border-t border-[#eff3f4] px-1.5 py-1 shadow-[0_-4px_20px_rgba(0,0,0,0.03)] safe-area-pb">
            @if($user->isUser())
                <!-- 1. USER (Pemilik Warung) Bottom Nav with Center Kasir Button -->
                <div class="grid grid-cols-5 items-center">
                    <!-- 1. Produk -->
                    <a 
                        href="/user/produk" 
                        class="flex flex-col items-center justify-center py-1 transition-colors group cursor-pointer {{ request()->is('user/produk*') ? 'text-[#1d9bf0] font-black' : 'text-[#0f1419] hover:text-[#1d9bf0]' }}"
                    >
                        <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                        <span class="text-[10px] tracking-tight font-bold">Produk</span>
                    </a>

                    <!-- 2. Laporan -->
                    <a 
                        href="/user/laporan" 
                        class="flex flex-col items-center justify-center py-1 transition-colors group cursor-pointer {{ request()->is('user/laporan*') ? 'text-[#1d9bf0] font-black' : 'text-[#0f1419] hover:text-[#1d9bf0]' }}"
                    >
                        <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                        <span class="text-[10px] tracking-tight font-bold">Laporan</span>
                    </a>

                    <!-- 3. CENTER ELEVATED FAB: Kasir / Checkout -->
                    <div class="flex justify-center -mt-6">
                        <a 
                            href="/user/kasir" 
                            class="w-14 h-14 rounded-full bg-[#1d9bf0] hover:bg-[#1a8cd8] text-white flex flex-col items-center justify-center shadow-lg shadow-[#1d9bf0]/40 border-4 border-white active:scale-95 transition-all cursor-pointer {{ request()->is('user/kasir*') ? 'ring-2 ring-[#1d9bf0] ring-offset-2' : '' }}"
                        >
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </a>
                    </div>

                    <!-- 4. Helpdesk -->
                    <a 
                        href="/user/helpdesk" 
                        class="flex flex-col items-center justify-center py-1 transition-colors group cursor-pointer {{ request()->is('user/helpdesk*') ? 'text-[#1d9bf0] font-black' : 'text-[#0f1419] hover:text-[#1d9bf0]' }}"
                    >
                        <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                        <span class="text-[10px] tracking-tight font-bold">Helpdesk</span>
                    </a>

                    <!-- 5. Panduan -->
                    <a 
                        href="/user/panduan" 
                        class="flex flex-col items-center justify-center py-1 transition-colors group cursor-pointer {{ request()->is('user/panduan*') ? 'text-[#1d9bf0] font-black' : 'text-[#0f1419] hover:text-[#1d9bf0]' }}"
                    >
                        <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                        <span class="text-[10px] tracking-tight font-bold">Panduan</span>
                    </a>
                </div>

            @else
                <!-- 2. ADMIN / SUPERADMIN 5-Item Grid Bottom Nav -->
                <div class="grid grid-cols-5 items-center">
                    <!-- 1. Dashboard -->
                    <a 
                        href="/{{ $rolePrefix }}/dashboard" 
                        class="flex flex-col items-center justify-center py-1 transition-colors cursor-pointer {{ request()->is($rolePrefix.'/dashboard') ? 'text-[#1d9bf0] font-black' : 'text-[#0f1419] hover:text-[#1d9bf0]' }}"
                    >
                        <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                        <span class="text-[10px] tracking-tight font-bold">Dashboard</span>
                    </a>

                    <!-- 2. Verif QRIS -->
                    <a 
                        href="/{{ $rolePrefix }}/verifikasi-qris" 
                        class="flex flex-col items-center justify-center py-1 relative transition-colors cursor-pointer {{ request()->is($rolePrefix.'/verifikasi-qris*') ? 'text-[#1d9bf0] font-black' : 'text-[#0f1419] hover:text-[#1d9bf0]' }}"
                    >
                        <div class="relative">
                            <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                            <template x-if="$store.app?.stats?.pendingCount > 0">
                                <span class="absolute -top-1 -right-1 w-2.5 h-2.5 bg-[#ff7a00] rounded-full ring-2 ring-white"></span>
                            </template>
                        </div>
                        <span class="text-[10px] tracking-tight font-bold">Verif QRIS</span>
                    </a>

                    <!-- 3. Produk -->
                    <a 
                        href="/{{ $rolePrefix }}/produk" 
                        class="flex flex-col items-center justify-center py-1 transition-colors cursor-pointer {{ request()->is($rolePrefix.'/produk*') ? 'text-[#1d9bf0] font-black' : 'text-[#0f1419] hover:text-[#1d9bf0]' }}"
                    >
                        <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                        <span class="text-[10px] tracking-tight font-bold">Produk</span>
                    </a>

                    <!-- 4. Laporan -->
                    <a 
                        href="/{{ $rolePrefix }}/laporan" 
                        class="flex flex-col items-center justify-center py-1 transition-colors cursor-pointer {{ request()->is($rolePrefix.'/laporan*') ? 'text-[#1d9bf0] font-black' : 'text-[#0f1419] hover:text-[#1d9bf0]' }}"
                    >
                        <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                        <span class="text-[10px] tracking-tight font-bold">Laporan</span>
                    </a>

                    <!-- 5. Menu (Triggers Bottom Sheet Modal) -->
                    @php
                        $isMenuChildActive = request()->is($rolePrefix.'/events*') || request()->is($rolePrefix.'/helpdesk*') || request()->is($rolePrefix.'/panduan*') || request()->is($rolePrefix.'/warung*');
                    @endphp
                    <button 
                        @click="adminMenuOpen = true"
                        type="button" 
                        class="flex flex-col items-center justify-center py-1 transition-colors cursor-pointer {{ $isMenuChildActive ? 'text-[#1d9bf0] font-black' : 'text-[#0f1419] hover:text-[#1d9bf0]' }}"
                    >
                        <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                        </svg>
                        <span class="text-[10px] tracking-tight font-bold">Menu</span>
                    </button>
                </div>
            @endif
        </div>

        <!-- ADMIN MENU BOTTOM SHEET POP-UP (Slide Up from Bottom) -->
        @if(!$user->isUser())
            <!-- Backdrop -->
            <div 
                x-show="adminMenuOpen"
                x-cloak
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                @click="adminMenuOpen = false"
                class="fixed inset-0 z-50 bg-[#0f1419]/60 backdrop-blur-xs"
            ></div>

            <!-- Bottom Sheet Content -->
            <div 
                x-show="adminMenuOpen"
                x-cloak
                x-transition:enter="transition ease-out duration-250 transform"
                x-transition:enter-start="translate-y-full"
                x-transition:enter-end="translate-y-0"
                x-transition:leave="transition ease-in duration-200 transform"
                x-transition:leave-start="translate-y-0"
                x-transition:leave-end="translate-y-full"
                class="fixed inset-x-0 bottom-0 z-50 bg-white rounded-t-3xl border-t border-[#eff3f4] p-5 shadow-2xl safe-area-pb max-h-[85vh] overflow-y-auto"
            >
                <!-- Pull Handle -->
                <div class="w-12 h-1.5 bg-[#cfd9de] rounded-full mx-auto mb-4 cursor-pointer" @click="adminMenuOpen = false"></div>

                <!-- Header -->
                <div class="flex items-center justify-between pb-3 border-b border-[#eff3f4] mb-4">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-full bg-[#e8f5fd] flex items-center justify-center text-[#1d9bf0]">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                        </div>
                        <div>
                            <h3 class="font-black text-sm text-[#0f1419]">Menu Tambahan {{ $user->isSuperAdmin() ? 'Super Admin' : 'Admin EO' }}</h3>
                            <p class="text-[10px] text-[#536471] font-semibold">Akses cepat menu manajemen event & operasional</p>
                        </div>
                    </div>
                    <button 
                        @click="adminMenuOpen = false" 
                        class="p-1.5 text-[#536471] hover:text-[#0f1419] hover:bg-[#eff3f4] rounded-full transition-colors cursor-pointer"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <!-- Action Links Grid -->
                <div class="grid grid-cols-1 gap-2.5">
                    <!-- 1. Event -->
                    <a 
                        href="/{{ $rolePrefix }}/events"
                        class="flex items-center gap-3.5 p-3.5 rounded-2xl border transition-all cursor-pointer {{ request()->is($rolePrefix.'/events*') ? 'bg-[#e8f5fd] border-[#bde2f9]' : 'bg-[#f7f9f9] border-[#eff3f4] hover:bg-[#e8f5fd]/50 hover:border-[#bde2f9]' }}"
                    >
                        <div class="w-10 h-10 rounded-xl bg-white border border-[#eff3f4] flex items-center justify-center text-[#1d9bf0] shrink-0 shadow-2xs">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <span class="font-black text-xs text-[#0f1419] block">Event & Bazaar</span>
                            <span class="text-[10px] text-[#536471] font-medium block truncate">Kelola nama event, lokasi, jadwal & status aktif</span>
                        </div>
                        <svg class="w-4 h-4 text-[#536471] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </a>

                    <!-- 2. Helpdesk -->
                    <a 
                        href="/{{ $rolePrefix }}/helpdesk"
                        class="flex items-center gap-3.5 p-3.5 rounded-2xl border transition-all cursor-pointer {{ request()->is($rolePrefix.'/helpdesk*') ? 'bg-[#e8f5fd] border-[#bde2f9]' : 'bg-[#f7f9f9] border-[#eff3f4] hover:bg-[#e8f5fd]/50 hover:border-[#bde2f9]' }}"
                    >
                        <div class="w-10 h-10 rounded-xl bg-white border border-[#eff3f4] flex items-center justify-center text-[#1d9bf0] shrink-0 shadow-2xs">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <span class="font-black text-xs text-[#0f1419] block">Helpdesk & Dukungan</span>
                            <span class="text-[10px] text-[#536471] font-medium block truncate">Layanan tiket bantuan dan komunikasi dengan tenant</span>
                        </div>
                        <svg class="w-4 h-4 text-[#536471] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </a>

                    <!-- 3. SOP & Panduan -->
                    <a 
                        href="/{{ $rolePrefix }}/panduan"
                        class="flex items-center gap-3.5 p-3.5 rounded-2xl border transition-all cursor-pointer {{ request()->is($rolePrefix.'/panduan*') ? 'bg-[#e8f5fd] border-[#bde2f9]' : 'bg-[#f7f9f9] border-[#eff3f4] hover:bg-[#e8f5fd]/50 hover:border-[#bde2f9]' }}"
                    >
                        <div class="w-10 h-10 rounded-xl bg-white border border-[#eff3f4] flex items-center justify-center text-[#1d9bf0] shrink-0 shadow-2xs">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <span class="font-black text-xs text-[#0f1419] block">SOP & Panduan Kasir</span>
                            <span class="text-[10px] text-[#536471] font-medium block truncate">Standar operasional kasir, verifikasi & rekonsiliasi</span>
                        </div>
                        <svg class="w-4 h-4 text-[#536471] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </a>

                    <!-- 4. Warung Stand -->
                    <a 
                        href="/{{ $rolePrefix }}/warung"
                        class="flex items-center gap-3.5 p-3.5 rounded-2xl border transition-all cursor-pointer {{ request()->is($rolePrefix.'/warung*') ? 'bg-[#e8f5fd] border-[#bde2f9]' : 'bg-[#f7f9f9] border-[#eff3f4] hover:bg-[#e8f5fd]/50 hover:border-[#bde2f9]' }}"
                    >
                        <div class="w-10 h-10 rounded-xl bg-white border border-[#eff3f4] flex items-center justify-center text-[#1d9bf0] shrink-0 shadow-2xs">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <span class="font-black text-xs text-[#0f1419] block">Stand / Warung Tenant</span>
                            <span class="text-[10px] text-[#536471] font-medium block truncate">Daftar booth UMKM terdaftar dan rincian omzet</span>
                        </div>
                        <svg class="w-4 h-4 text-[#536471] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </a>
                </div>

                <!-- Footer Quick Logout / Profile -->
                <div class="mt-4 pt-3 border-t border-[#eff3f4] flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-full bg-[#1d9bf0] text-white flex items-center justify-center font-bold text-[10px]">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                        <span class="text-[11px] font-bold text-[#0f1419]">{{ $user->name }}</span>
                    </div>

                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button 
                            type="submit" 
                            class="px-3.5 py-1.5 bg-rose-50 hover:bg-[#f4212e] text-[#f4212e] hover:text-white rounded-full font-black text-[10px] transition-colors cursor-pointer"
                        >
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        @endif
    </div>
@endauth
