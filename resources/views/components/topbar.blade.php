<!-- Topbar Header Component (Twitter UI Theme - Production Ready) -->
<header class="sticky top-0 z-30 bg-white/95 backdrop-blur-md border-b border-[#eff3f4] px-4 sm:px-6 py-2.5 transition-all">
    <div class="flex items-center justify-between gap-4">
        <!-- Left: Brand & Active Event Pill -->
        <div class="flex items-center gap-3">
            <a href="/" class="flex items-center gap-2.5 lg:hidden">
                <div class="w-8 h-8 rounded-full bg-[#1d9bf0] flex items-center justify-center text-white font-black text-base shadow-sm shadow-[#1d9bf0]/20">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                </div>
                <span class="font-black text-[#0f1419] tracking-tight text-sm">POS UMKM</span>
            </a>

            <!-- Active Event Badge -->
            @php
                $activeEvent = \App\Models\Event::getActive();
            @endphp
            <div class="hidden sm:flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-[#f7f9f9] border border-[#eff3f4] text-xs text-[#0f1419]">
                <span class="w-2 h-2 rounded-full {{ $activeEvent ? 'bg-[#1d9bf0] animate-pulse' : 'bg-slate-400' }}"></span>
                <span class="font-bold text-[#536471]">Event:</span>
                <span class="font-black text-[#0f1419] truncate max-w-[260px]">{{ $activeEvent ? $activeEvent->name : 'Tidak Ada Event Aktif' }}</span>
                @if($activeEvent)
                    <svg class="w-3.5 h-3.5 text-[#1d9bf0]" fill="currentColor" viewBox="0 0 24 24"><path d="M22.5 12.5c0-1.58-.875-2.95-2.148-3.6.154-.435.238-.905.238-1.4 0-2.21-1.79-4-4-4-.495 0-.965.084-1.4.238C14.55 2.475 13.18 1.6 11.6 1.6c-1.58 0-2.95.875-3.6 2.148-.435-.154-.905-.238-1.4-.238-2.21 0-4 1.79-4 4 0 .495.084.965.238 1.4C1.575 9.55.7 10.92.7 12.5c0 1.58.875 2.95 2.148 3.6-.154.435-.238.905-.238 1.4 0 2.21 1.79 4 4 4 .495 0 .965-.084 1.4-.238.65 1.273 2.02 2.148 3.6 2.148 1.58 0 2.95-.875 3.6-2.148.435.154.905.238 1.4.238 2.21 0 4-1.79 4-4 0-.495-.084-.965-.238-1.4 1.273-.65 2.148-2.02 2.148-3.6zm-12.28 4.22l-4.22-4.22 1.414-1.414 2.806 2.806 6.806-6.806 1.414 1.414-8.22 8.22z"></path></svg>
                @endif
            </div>
        </div>

        <!-- Right: Authenticated User Profile & Logout -->
        <div class="flex items-center gap-3">
            @auth
                <!-- User Info Badge -->
                <div class="flex items-center gap-2.5 px-3 py-1.5 rounded-full bg-[#f7f9f9] border border-[#eff3f4]">
                    <div class="w-7 h-7 rounded-full bg-[#1d9bf0] text-white flex items-center justify-center font-black text-xs shadow-xs">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    <div class="text-left hidden sm:block">
                        <p class="text-xs font-black text-[#0f1419] leading-tight">{{ auth()->user()->name }}</p>
                        <p class="text-[10px] text-[#536471] font-bold leading-tight">
                            @if(auth()->user()->isSuperAdmin())
                                👑 Super Admin Platform
                            @elseif(auth()->user()->isAdmin())
                                🛡️ Panitia Admin EO
                            @else
                                🛒 {{ auth()->user()->store->name ?? 'Kasir Stand' }}
                            @endif
                        </p>
                    </div>
                </div>

                <!-- Secure Logout Form -->
                <form action="{{ route('logout') }}" method="POST" class="inline">
                    @csrf
                    <button 
                        type="submit" 
                        class="px-3.5 py-1.5 rounded-full bg-[#eff3f4] hover:bg-rose-50 text-[#536471] hover:text-[#f4212e] text-xs font-black transition-all flex items-center gap-1.5 cursor-pointer"
                        title="Keluar dari akun"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                        <span class="hidden md:inline">Keluar</span>
                    </button>
                </form>
            @endauth
        </div>
    </div>
</header>
