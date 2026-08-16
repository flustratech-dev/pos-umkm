<!-- Desktop Sidebar (Twitter UI Design System - Production Ready) -->
<aside class="hidden lg:flex lg:flex-col w-64 bg-white text-[#0f1419] shrink-0 h-screen sticky top-0 z-40 border-r border-[#eff3f4]">
    <!-- Brand Logo & App Name (Twitter Style) -->
    <div class="p-5 border-b border-[#eff3f4] flex items-center gap-3">
        <div class="w-10 h-10 rounded-2xl overflow-hidden shrink-0 flex items-center justify-center shadow-md shadow-[#1d9bf0]/10 hover:scale-105 transition-transform cursor-pointer bg-white border border-[#eff3f4]">
            <img src="{{ asset('images/logo_jadisatu.png') }}" alt="Logo JADISATU" class="w-full h-full object-contain p-1">
        </div>
        <div>
            <h1 class="font-black text-base tracking-tight text-[#0f1419]">
                Kasir
            </h1>
            <p class="text-xs text-[#536471] font-semibold">JADISATU</p>
        </div>
    </div>

    <!-- Active Store / Organization Banner -->
    @php
        $activeEvent = \App\Models\Event::getActive();
        $user = auth()->user();
    @endphp
    <div class="px-3.5 py-2.5 mx-4 my-2.5 rounded-2xl bg-[#f7f9f9] border border-[#eff3f4]" x-data="{ switcherOpen: false }">
        @if($user && $user->isUser())
            <div class="relative">
                <button @click="switcherOpen = !switcherOpen" class="w-full flex items-center justify-between text-left rounded-xl hover:bg-[#eff3f4]/60 transition-colors cursor-pointer group">
                    <div class="overflow-hidden min-w-0 pr-2">
                        <p class="text-xs font-black text-[#0f1419] truncate flex items-center gap-1">
                            <span class="truncate">{{ $user->store->name ?? 'Belum Didaftarkan' }}</span>
                            <svg class="w-3.5 h-3.5 text-[#1d9bf0] shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M22.5 12.5c0-1.58-.875-2.95-2.148-3.6.154-.435.238-.905.238-1.4 0-2.21-1.79-4-4-4-.495 0-.965.084-1.4.238C14.55 2.475 13.18 1.6 11.6 1.6c-1.58 0-2.95.875-3.6 2.148-.435-.154-.905-.238-1.4-.238-2.21 0-4 1.79-4 4 0 .495.084.965.238 1.4C1.575 9.55.7 10.92.7 12.5c0 1.58.875 2.95 2.148 3.6-.154.435-.238.905-.238 1.4 0 2.21 1.79 4 4 4 .495 0 .965-.084 1.4-.238.65 1.273 2.02 2.148 3.6 2.148 1.58 0 2.95-.875 3.6-2.148.435.154.905.238 1.4.238 2.21 0 4-1.79 4-4 0-.495-.084-.965-.238-1.4 1.273-.65 2.148-2.02 2.148-3.6zm-12.28 4.22l-4.22-4.22 1.414-1.414 2.806 2.806 6.806-6.806 1.414 1.414-8.22 8.22z"></path></svg>
                        </p>
                        <p class="text-[11px] text-[#536471] font-semibold truncate mt-0.5">{{ $user->store->event->name ?? 'Tidak Ada Event' }}</p>
                        <template x-if="!$store.app.activeStoreEventActive">
                            <span class="inline-block mt-1 px-1.5 py-0.5 bg-[#f4212e]/10 text-[#f4212e] text-[9px] font-black uppercase rounded">Event Inaktif (Readonly)</span>
                        </template>
                    </div>
                    <svg class="w-4 h-4 text-[#536471] shrink-0" :class="{'rotate-180': switcherOpen}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </button>
                
                <div x-show="switcherOpen" @click.away="switcherOpen = false" x-cloak class="absolute top-full left-0 right-0 mt-1 bg-white border border-[#eff3f4] rounded-2xl shadow-xl z-50 overflow-hidden">
                    <div class="max-h-48 overflow-y-auto">
                        <template x-for="userStore in $store.app.userStores" :key="userStore.id">
                            <form action="{{ route('user.switch-store') }}" method="POST" class="border-b border-[#eff3f4] last:border-0">
                                @csrf
                                <input type="hidden" name="store_id" :value="userStore.id">
                                <button type="submit" class="w-full text-left px-4 py-2.5 hover:bg-[#f7f9f9] transition-colors cursor-pointer" :class="{'bg-[#e8f5fd]': userStore.id == $store.app.user.store_id}">
                                    <p class="text-xs font-black text-[#0f1419] truncate" x-text="userStore.name"></p>
                                    <p class="text-[10px] text-[#536471] font-semibold truncate" x-text="userStore.event_name"></p>
                                    <span class="inline-block mt-0.5 px-1.5 rounded text-[9px] font-bold" 
                                          :class="userStore.event_is_active ? 'bg-[#00ba7c]/10 text-[#00ba7c]' : 'bg-[#f4212e]/10 text-[#f4212e]'"
                                          x-text="userStore.event_is_active ? 'Event Aktif' : 'Event Selesai'"></span>
                                </button>
                            </form>
                        </template>
                    </div>
                </div>
            </div>
        @else
            <div class="overflow-hidden">
                <p class="text-xs font-black text-[#0f1419] truncate flex items-center gap-1">
                    <span>
                        @if($user && $user->isAdmin())
                            Panitia Admin EO
                        @else
                            Developer Platform
                        @endif
                    </span>
                    <svg class="w-3.5 h-3.5 text-[#1d9bf0] shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M22.5 12.5c0-1.58-.875-2.95-2.148-3.6.154-.435.238-.905.238-1.4 0-2.21-1.79-4-4-4-.495 0-.965.084-1.4.238C14.55 2.475 13.18 1.6 11.6 1.6c-1.58 0-2.95.875-3.6 2.148-.435-.154-.905-.238-1.4-.238-2.21 0-4 1.79-4 4 0 .495.084.965.238 1.4C1.575 9.55.7 10.92.7 12.5c0 1.58.875 2.95 2.148 3.6-.154.435-.238.905-.238 1.4 0 2.21 1.79 4 4 4 .495 0 .965-.084 1.4-.238.65 1.273 2.02 2.148 3.6 2.148 1.58 0 2.95-.875 3.6-2.148.435.154.905.238 1.4.238 2.21 0 4-1.79 4-4 0-.495-.084-.965-.238-1.4 1.273-.65 2.148-2.02 2.148-3.6zm-12.28 4.22l-4.22-4.22 1.414-1.414 2.806 2.806 6.806-6.806 1.414 1.414-8.22 8.22z"></path></svg>
                </p>
                <p class="text-[11px] text-[#536471] font-semibold truncate mt-0.5">{{ $activeEvent ? $activeEvent->name : 'Tidak Ada Event Aktif' }}</p>
            </div>
        @endif
    </div>

    <!-- Navigation Links based on Authenticated Role -->
    <nav class="flex-1 px-3 space-y-1 overflow-y-auto custom-scrollbar py-2">
        @if($user && $user->isUser())
            <!-- 1. USER / WARUNG MENU -->
            <div class="space-y-1">
                <div class="px-4 py-1 text-[11px] font-bold uppercase tracking-wider text-[#536471]">Operasional Kasir</div>
                
                <a 
                    href="/user/kasir" 
                    class="flex items-center gap-3.5 px-4 py-2.5 rounded-full text-sm font-medium transition-all group cursor-pointer {{ request()->is('user/kasir*') ? 'bg-[#e8f5fd] text-[#1d9bf0] font-bold' : 'text-[#0f1419] hover:bg-[#eff3f4]' }}"
                >
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span>Kasir & POS</span>
                </a>

                <a 
                    href="/user/produk" 
                    class="flex items-center gap-3.5 px-4 py-2.5 rounded-full text-sm font-medium transition-all group cursor-pointer {{ request()->is('user/produk*') ? 'bg-[#e8f5fd] text-[#1d9bf0] font-bold' : 'text-[#0f1419] hover:bg-[#eff3f4]' }}"
                >
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                    <span>Kelola Produk</span>
                </a>

                <a 
                    href="/user/laporan" 
                    class="flex items-center gap-3.5 px-4 py-2.5 rounded-full text-sm font-medium transition-all group cursor-pointer {{ request()->is('user/laporan*') ? 'bg-[#e8f5fd] text-[#1d9bf0] font-bold' : 'text-[#0f1419] hover:bg-[#eff3f4]' }}"
                >
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                    <span>Laporan Saya</span>
                </a>

                <div class="pt-3 px-4 py-1 text-[11px] font-bold uppercase tracking-wider text-[#536471]">Bantuan</div>

                <a 
                    href="/user/helpdesk" 
                    class="flex items-center gap-3.5 px-4 py-2.5 rounded-full text-sm font-medium transition-all group cursor-pointer {{ request()->is('user/helpdesk*') ? 'bg-[#e8f5fd] text-[#1d9bf0] font-bold' : 'text-[#0f1419] hover:bg-[#eff3f4]' }}"
                >
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    <span>Helpdesk</span>
                </a>

                <a 
                    href="/user/panduan" 
                    class="flex items-center gap-3.5 px-4 py-2.5 rounded-full text-sm font-medium transition-all group cursor-pointer {{ request()->is('user/panduan*') ? 'bg-[#e8f5fd] text-[#1d9bf0] font-bold' : 'text-[#0f1419] hover:bg-[#eff3f4]' }}"
                >
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                    <span>Panduan Jualan</span>
                </a>
            </div>

        @elseif($user && $user->isAdmin())
            <!-- 2. ADMIN EO MENU -->
            <div class="space-y-1">
                <div class="px-4 py-1 text-[11px] font-bold uppercase tracking-wider text-[#536471]">Pusat Kendali EO</div>

                <a 
                    href="/admin/dashboard" 
                    class="flex items-center gap-3.5 px-4 py-2.5 rounded-full text-sm font-medium transition-all group cursor-pointer {{ request()->is('admin/dashboard') ? 'bg-[#e8f5fd] text-[#1d9bf0] font-bold' : 'text-[#0f1419] hover:bg-[#eff3f4]' }}"
                >
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                    <span>Dashboard Event</span>
                </a>

                <a 
                    href="/admin/events" 
                    class="flex items-center gap-3.5 px-4 py-2.5 rounded-full text-sm font-medium transition-all group cursor-pointer {{ request()->is('admin/events*') ? 'bg-[#e8f5fd] text-[#1d9bf0] font-bold' : 'text-[#0f1419] hover:bg-[#eff3f4]' }}"
                >
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    <span>Kelola Multi-Event</span>
                </a>

                <a 
                    href="/admin/verifikasi-qris" 
                    class="flex items-center justify-between px-4 py-2.5 rounded-full text-sm font-medium transition-all group cursor-pointer {{ request()->is('admin/verifikasi-qris*') ? 'bg-[#e8f5fd] text-[#1d9bf0] font-bold' : 'text-[#0f1419] hover:bg-[#eff3f4]' }}"
                >
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 shrink-0 text-[#1d9bf0]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                        <span>Verifikasi QRIS</span>
                    </div>
                </a>

                <a 
                    href="/admin/produk" 
                    class="flex items-center gap-3.5 px-4 py-2.5 rounded-full text-sm font-medium transition-all group cursor-pointer {{ request()->is('admin/produk*') ? 'bg-[#e8f5fd] text-[#1d9bf0] font-bold' : 'text-[#0f1419] hover:bg-[#eff3f4]' }}"
                >
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path></svg>
                    <span>Produk Semua Warung</span>
                </a>

                <a 
                    href="/admin/warung" 
                    class="flex items-center gap-3.5 px-4 py-2.5 rounded-full text-sm font-medium transition-all group cursor-pointer {{ request()->is('admin/warung*') ? 'bg-[#e8f5fd] text-[#1d9bf0] font-bold' : 'text-[#0f1419] hover:bg-[#eff3f4]' }}"
                >
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                    <span>Warung & Pemilik</span>
                </a>

                <a 
                    href="/admin/laporan" 
                    class="flex items-center gap-3.5 px-4 py-2.5 rounded-full text-sm font-medium transition-all group cursor-pointer {{ request()->is('admin/laporan*') ? 'bg-[#e8f5fd] text-[#1d9bf0] font-bold' : 'text-[#0f1419] hover:bg-[#eff3f4]' }}"
                >
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    <span>Laporan & Bagi Hasil</span>
                </a>

                <div class="pt-3 px-4 py-1 text-[11px] font-bold uppercase tracking-wider text-[#536471]">Bantuan & SOP</div>

                <a 
                    href="/admin/helpdesk" 
                    class="flex items-center gap-3.5 px-4 py-2.5 rounded-full text-sm font-medium transition-all group cursor-pointer {{ request()->is('admin/helpdesk*') ? 'bg-[#e8f5fd] text-[#1d9bf0] font-bold' : 'text-[#0f1419] hover:bg-[#eff3f4]' }}"
                >
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
                    <span>Helpdesk Masuk</span>
                </a>

                <a 
                    href="/admin/panduan" 
                    class="flex items-center gap-3.5 px-4 py-2.5 rounded-full text-sm font-medium transition-all group cursor-pointer {{ request()->is('admin/panduan*') ? 'bg-[#e8f5fd] text-[#1d9bf0] font-bold' : 'text-[#0f1419] hover:bg-[#eff3f4]' }}"
                >
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                    <span>SOP Operasional EO</span>
                </a>
            </div>

        @elseif($user && $user->isSuperAdmin())
            <!-- 3. SUPER ADMIN MENU -->
            <div class="space-y-1">
                <div class="px-4 py-1 text-[11px] font-bold uppercase tracking-wider text-[#536471]">Platform Master</div>

                <a 
                    href="/superadmin/dashboard" 
                    class="flex items-center gap-3.5 px-4 py-2.5 rounded-full text-sm font-medium transition-all group cursor-pointer {{ request()->is('superadmin/dashboard') ? 'bg-[#e8f5fd] text-[#1d9bf0] font-bold' : 'text-[#0f1419] hover:bg-[#eff3f4]' }}"
                >
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                    <span>Dashboard Platform</span>
                </a>

                <a 
                    href="/superadmin/events" 
                    class="flex items-center gap-3.5 px-4 py-2.5 rounded-full text-sm font-medium transition-all group cursor-pointer {{ request()->is('superadmin/events*') ? 'bg-[#e8f5fd] text-[#1d9bf0] font-bold' : 'text-[#0f1419] hover:bg-[#eff3f4]' }}"
                >
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    <span>Kelola Multi-Event</span>
                </a>

                <a 
                    href="/superadmin/laporan" 
                    class="flex items-center gap-3.5 px-4 py-2.5 rounded-full text-sm font-medium transition-all group cursor-pointer {{ request()->is('superadmin/laporan*') ? 'bg-[#e8f5fd] text-[#1d9bf0] font-bold' : 'text-[#0f1419] hover:bg-[#eff3f4]' }}"
                >
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span>Laporan Fee Platform</span>
                </a>

                <div class="pt-3 px-4 py-1 text-[11px] font-bold uppercase tracking-wider text-[#536471]">Pengawasan EO & Warung</div>

                <a 
                    href="/superadmin/verifikasi-qris" 
                    class="flex items-center justify-between px-4 py-2.5 rounded-full text-sm font-medium transition-all group cursor-pointer {{ request()->is('superadmin/verifikasi-qris*') ? 'bg-[#e8f5fd] text-[#1d9bf0] font-bold' : 'text-[#0f1419] hover:bg-[#eff3f4]' }}"
                >
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 shrink-0 text-[#1d9bf0]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                        <span>Verifikasi QRIS (Audit)</span>
                    </div>
                </a>

                <a 
                    href="/superadmin/warung" 
                    class="flex items-center gap-3.5 px-4 py-2.5 rounded-full text-sm font-medium transition-all group cursor-pointer {{ request()->is('superadmin/warung*') ? 'bg-[#e8f5fd] text-[#1d9bf0] font-bold' : 'text-[#0f1419] hover:bg-[#eff3f4]' }}"
                >
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                    <span>Warung & Pemilik</span>
                </a>

                <a 
                    href="/superadmin/produk" 
                    class="flex items-center gap-3.5 px-4 py-2.5 rounded-full text-sm font-medium transition-all group cursor-pointer {{ request()->is('superadmin/produk*') ? 'bg-[#e8f5fd] text-[#1d9bf0] font-bold' : 'text-[#0f1419] hover:bg-[#eff3f4]' }}"
                >
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path></svg>
                    <span>Produk Semua Warung</span>
                </a>

                <div class="pt-3 px-4 py-1 text-[11px] font-bold uppercase tracking-wider text-[#536471]">Layanan & Bantuan</div>

                <a 
                    href="/superadmin/helpdesk" 
                    class="flex items-center gap-3.5 px-4 py-2.5 rounded-full text-sm font-medium transition-all group cursor-pointer {{ request()->is('superadmin/helpdesk*') ? 'bg-[#e8f5fd] text-[#1d9bf0] font-bold' : 'text-[#0f1419] hover:bg-[#eff3f4]' }}"
                >
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
                    <span>Helpdesk Lintas Tenant</span>
                </a>

                <a 
                    href="/superadmin/panduan" 
                    class="flex items-center gap-3.5 px-4 py-2.5 rounded-full text-sm font-medium transition-all group cursor-pointer {{ request()->is('superadmin/panduan*') ? 'bg-[#e8f5fd] text-[#1d9bf0] font-bold' : 'text-[#0f1419] hover:bg-[#eff3f4]' }}"
                >
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                    <span>SOP & Panduan Sistem</span>
                </a>
            </div>
        @endif
    </nav>

    <!-- Footer Logout -->
    <div class="p-4 border-t border-[#eff3f4]">
        @auth
            <form action="{{ route('logout') }}" method="POST" class="block w-full">
                @csrf
                <button type="submit" class="w-full flex items-center justify-center gap-2.5 px-4 py-2.5 rounded-full bg-[#eff3f4] text-[#536471] hover:bg-rose-50 hover:text-[#f4212e] text-sm font-bold transition-colors cursor-pointer" title="Keluar dari akun">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                    Keluar
                </button>
            </form>
        @endauth
    </div>
</aside>
