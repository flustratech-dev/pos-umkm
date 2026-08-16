<!-- Mobile Bottom Navigation Bar (Twitter UI Theme - Modern Curved Floating Dock) -->
@auth
    @php
        $user = auth()->user();
        $rolePrefix = $user->isSuperAdmin() ? 'superadmin' : ($user->isAdmin() ? 'admin' : 'user');
    @endphp
    <div class="lg:hidden">
        <!-- Floating Curved Dock Container -->
        <div class="fixed bottom-3.5 sm:bottom-5 inset-x-3 sm:inset-x-6 max-w-md sm:max-w-lg mx-auto z-40 bg-white/95 backdrop-blur-xl border border-[#eff3f4] px-2 py-1.5 rounded-[28px] shadow-[0_12px_32px_rgba(15,20,25,0.12),0_2px_6px_rgba(0,0,0,0.04)]">
            @if($user->isUser())
                <!-- 1. USER (Pemilik Warung) Floating Curved Dock -->
                <div class="grid grid-cols-5 items-center gap-1">
                    <!-- 1. Produk -->
                    <a 
                        href="/user/produk" 
                        class="flex flex-col items-center justify-center py-1.5 px-1 rounded-2xl transition-all duration-200 cursor-pointer active:scale-95 {{ request()->is('user/produk*') ? 'text-[#1d9bf0] font-black bg-[#e8f5fd]' : 'text-[#536471] hover:text-[#0f1419] hover:bg-[#f7f9f9]' }}"
                    >
                        <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                        <span class="text-[9.5px] tracking-tight font-bold">Produk</span>
                    </a>

                    <!-- 2. Laporan -->
                    <a 
                        href="/user/laporan" 
                        class="flex flex-col items-center justify-center py-1.5 px-1 rounded-2xl transition-all duration-200 cursor-pointer active:scale-95 {{ request()->is('user/laporan*') ? 'text-[#1d9bf0] font-black bg-[#e8f5fd]' : 'text-[#536471] hover:text-[#0f1419] hover:bg-[#f7f9f9]' }}"
                    >
                        <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                        <span class="text-[9.5px] tracking-tight font-bold">Laporan</span>
                    </a>

                    <!-- 3. CENTER ELEVATED CURVED FAB: Kasir / Checkout -->
                    <div class="flex justify-center -mt-7">
                        <a 
                            href="/user/kasir" 
                            class="w-13 h-13 rounded-full bg-gradient-to-tr from-[#1d9bf0] to-[#1a8cd8] hover:from-[#1a8cd8] hover:to-[#1271b3] text-white flex flex-col items-center justify-center shadow-lg shadow-[#1d9bf0]/35 border-4 border-white active:scale-90 transition-all cursor-pointer {{ request()->is('user/kasir*') ? 'ring-2 ring-[#1d9bf0] ring-offset-2 scale-105' : '' }}"
                            title="Buka Kasir"
                        >
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </a>
                    </div>

                    <!-- 4. Helpdesk -->
                    <a 
                        href="/user/helpdesk" 
                        class="flex flex-col items-center justify-center py-1.5 px-1 rounded-2xl transition-all duration-200 cursor-pointer active:scale-95 {{ request()->is('user/helpdesk*') ? 'text-[#1d9bf0] font-black bg-[#e8f5fd]' : 'text-[#536471] hover:text-[#0f1419] hover:bg-[#f7f9f9]' }}"
                    >
                        <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                        <span class="text-[9.5px] tracking-tight font-bold">Helpdesk</span>
                    </a>

                    <!-- 5. Panduan -->
                    <a 
                        href="/user/panduan" 
                        class="flex flex-col items-center justify-center py-1.5 px-1 rounded-2xl transition-all duration-200 cursor-pointer active:scale-95 {{ request()->is('user/panduan*') ? 'text-[#1d9bf0] font-black bg-[#e8f5fd]' : 'text-[#536471] hover:text-[#0f1419] hover:bg-[#f7f9f9]' }}"
                    >
                        <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                        <span class="text-[9.5px] tracking-tight font-bold">Panduan</span>
                    </a>
                </div>

            @else
                <!-- 2. ADMIN / SUPERADMIN 5-Item Floating Curved Dock -->
                <div class="grid grid-cols-5 items-center gap-1">
                    <!-- 1. Dashboard -->
                    <a 
                        href="/{{ $rolePrefix }}/dashboard" 
                        class="flex flex-col items-center justify-center py-1.5 px-1 rounded-2xl transition-all duration-200 cursor-pointer active:scale-95 {{ request()->is($rolePrefix.'/dashboard') ? 'text-[#1d9bf0] font-black bg-[#e8f5fd]' : 'text-[#536471] hover:text-[#0f1419] hover:bg-[#f7f9f9]' }}"
                    >
                        <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                        <span class="text-[9.5px] tracking-tight font-bold">Dashboard</span>
                    </a>

                    <!-- 2. Verifikasi (Combined) -->
                    <div class="relative flex flex-col items-center justify-center" x-data="{ openVerif: false }">
                        <a 
                            @click.prevent="openVerif = !openVerif"
                            href="#" 
                            class="flex flex-col items-center justify-center py-1.5 px-1 rounded-2xl relative transition-all duration-200 cursor-pointer active:scale-95 {{ request()->is($rolePrefix.'/verifikasi-*') ? 'text-[#1d9bf0] font-black bg-[#e8f5fd]' : 'text-[#536471] hover:text-[#0f1419] hover:bg-[#f7f9f9]' }}"
                        >
                            <div class="relative">
                                <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                                <template x-if="($store.app?.stats?.pendingCount || 0) + ($store.app?.stats?.pendingCashCount || 0) > 0">
                                    <span class="absolute -top-1 -right-1 w-2 h-2 bg-[#ff7a00] rounded-full ring-2 ring-white"></span>
                                </template>
                            </div>
                            <span class="text-[9.5px] tracking-tight font-bold">Verifikasi</span>
                        </a>
                        
                        <!-- Combined Verif Dropdown Menu -->
                        <div x-show="openVerif" @click.away="openVerif = false" class="absolute bottom-[110%] left-1/2 -translate-x-1/2 w-36 bg-white rounded-xl shadow-xl border border-[#eff3f4] overflow-hidden" x-cloak>
                            <a href="/{{ $rolePrefix }}/verifikasi-qris" class="block px-3 py-2.5 text-[11px] font-bold text-[#0f1419] hover:bg-[#f7f9f9] border-b border-[#eff3f4]">
                                Verif QRIS
                                <template x-if="$store.app?.stats?.pendingCount > 0">
                                    <span class="ml-1 px-1.5 py-0.5 rounded-full bg-[#ff7a00] text-white text-[9px]" x-text="$store.app.stats.pendingCount"></span>
                                </template>
                            </a>
                            <a href="/{{ $rolePrefix }}/verifikasi-cash" class="block px-3 py-2.5 text-[11px] font-bold text-[#0f1419] hover:bg-[#f7f9f9]">
                                Verif Cash
                                <template x-if="$store.app?.stats?.pendingCashCount > 0">
                                    <span class="ml-1 px-1.5 py-0.5 rounded-full bg-[#ff7a00] text-white text-[9px]" x-text="$store.app.stats.pendingCashCount"></span>
                                </template>
                            </a>
                        </div>
                    </div>

                    <!-- 3. Produk -->
                    <a 
                        href="/{{ $rolePrefix }}/produk" 
                        class="flex flex-col items-center justify-center py-1.5 px-1 rounded-2xl transition-all duration-200 cursor-pointer active:scale-95 {{ request()->is($rolePrefix.'/produk*') ? 'text-[#1d9bf0] font-black bg-[#e8f5fd]' : 'text-[#536471] hover:text-[#0f1419] hover:bg-[#f7f9f9]' }}"
                    >
                        <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                        <span class="text-[9.5px] tracking-tight font-bold">Produk</span>
                    </a>

                    <!-- 4. Laporan -->
                    <a 
                        href="/{{ $rolePrefix }}/laporan" 
                        class="flex flex-col items-center justify-center py-1.5 px-1 rounded-2xl transition-all duration-200 cursor-pointer active:scale-95 {{ request()->is($rolePrefix.'/laporan*') ? 'text-[#1d9bf0] font-black bg-[#e8f5fd]' : 'text-[#536471] hover:text-[#0f1419] hover:bg-[#f7f9f9]' }}"
                    >
                        <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                        <span class="text-[9.5px] tracking-tight font-bold">Laporan</span>
                    </a>

                    <!-- 5. Pengaturan -->
                    <a 
                        href="{{ route('profile.edit') }}" 
                        class="flex flex-col items-center justify-center py-1.5 px-1 rounded-2xl transition-all duration-200 cursor-pointer active:scale-95 {{ request()->is('profile*') ? 'text-[#1d9bf0] font-black bg-[#e8f5fd]' : 'text-[#536471] hover:text-[#0f1419] hover:bg-[#f7f9f9]' }}"
                    >
                        <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        <span class="text-[9.5px] tracking-tight font-bold">Pengaturan</span>
                    </a>
                </div>
            @endif
        </div>
    </div>
@endauth

