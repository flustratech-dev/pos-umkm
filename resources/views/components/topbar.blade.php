<!-- Topbar Header Component (Twitter UI Theme - Production Ready) -->
<header class="sticky top-0 z-30 bg-white/95 backdrop-blur-md border-b border-[#eff3f4] px-4 sm:px-6 py-2.5 transition-all">
    <div class="flex items-center justify-between gap-4">
        <!-- Left: Brand & Active Event Pill -->
        <div class="flex items-center gap-3">
            <a href="/" class="flex items-center gap-2.5 lg:hidden">
                <div class="w-8 h-8 rounded-xl overflow-hidden shrink-0 flex items-center justify-center shadow-xs bg-white border border-[#eff3f4]">
                    <img src="{{ asset('images/logo_jadisatu.png') }}" alt="Logo JADISATU" class="w-full h-full object-contain p-0.5">
                </div>
                <div class="flex flex-col">
                    <span class="font-black text-[#0f1419] tracking-tight text-sm leading-none">Kasir</span>
                    <span class="text-[10px] text-[#536471] font-bold">JADISATU</span>
                </div>
            </a>

        </div>

        <!-- Right: Authenticated User Profile & Logout -->
        <div class="flex items-center gap-3">
            @auth
                <!-- User Dropdown Menu -->
                <div x-data="{ openDropdown: false }" class="relative">
                    <button 
                        @click="openDropdown = !openDropdown"
                        @click.outside="openDropdown = false"
                        class="w-9 h-9 rounded-full bg-[#1d9bf0] text-white flex items-center justify-center font-black text-sm shadow-xs hover:ring-2 hover:ring-offset-2 hover:ring-[#1d9bf0] transition-all cursor-pointer overflow-hidden border-2 border-transparent hover:border-white"
                        title="Menu Pengguna"
                    >
                        @if(auth()->user()->avatar)
                            <img src="{{ asset('storage/' . auth()->user()->avatar) }}" alt="Avatar" class="w-full h-full object-cover">
                        @else
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        @endif
                    </button>

                    <!-- Dropdown -->
                    <div 
                        x-show="openDropdown"
                        x-cloak
                        x-transition:enter="transition ease-out duration-100"
                        x-transition:enter-start="transform opacity-0 scale-95"
                        x-transition:enter-end="transform opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-75"
                        x-transition:leave-start="transform opacity-100 scale-100"
                        x-transition:leave-end="transform opacity-0 scale-95"
                        class="absolute right-0 mt-2 w-56 bg-white rounded-2xl shadow-xl border border-[#eff3f4] py-1 z-50 overflow-hidden"
                    >
                        <!-- User Info Snippet -->
                        <div class="px-4 py-3 border-b border-[#eff3f4] mb-1 flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-[#1d9bf0] text-white flex items-center justify-center font-black text-sm shrink-0 overflow-hidden">
                                @if(auth()->user()->avatar)
                                    <img src="{{ asset('storage/' . auth()->user()->avatar) }}" alt="Avatar" class="w-full h-full object-cover">
                                @else
                                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                @endif
                            </div>
                            <div class="truncate">
                                <p class="text-sm font-black text-[#0f1419] truncate">{{ auth()->user()->name }}</p>
                                <p class="text-[10px] text-[#536471] font-bold truncate mt-0.5">
                                    @if(auth()->user()->isSuperAdmin())
                                        👑 Super Admin
                                    @elseif(auth()->user()->isAdmin())
                                        🛡️ Admin EO
                                    @else
                                        🛒 {{ auth()->user()->store->name ?? 'Kasir Stand' }}
                                    @endif
                                </p>
                            </div>
                        </div>

                        <!-- Profil Link -->
                        <a href="{{ route('profile.edit') }}" class="flex items-center gap-2.5 px-4 py-2.5 text-xs font-bold text-[#0f1419] hover:bg-[#f7f9f9] transition-colors">
                            <svg class="w-4 h-4 text-[#536471]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                            Profil
                        </a>

                        <!-- Logout Form -->
                        <form action="{{ route('logout') }}" method="POST" class="block w-full border-t border-[#eff3f4] mt-1">
                            @csrf
                            <button type="submit" class="w-full flex items-center gap-2.5 px-4 py-2.5 text-xs font-bold text-[#f4212e] hover:bg-[#fff3f4] transition-colors text-left cursor-pointer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                                Keluar
                            </button>
                        </form>
                    </div>
                </div>
            @endauth
        </div>
    </div>
</header>
