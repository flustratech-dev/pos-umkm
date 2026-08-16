<!-- Topbar Header Component (Modern Curved Floating Dock on Mobile, Clean on Desktop) -->
<header class="sticky top-0 z-30 transition-all p-3 sm:p-4 lg:p-0">
    <div class="bg-white/95 backdrop-blur-xl border border-[#eff3f4] rounded-[24px] sm:rounded-[28px] px-3.5 sm:px-4 py-2 sm:py-2.5 shadow-[0_8px_24px_rgba(15,20,25,0.08),0_1px_4px_rgba(0,0,0,0.02)] lg:rounded-none lg:border-0 lg:border-b lg:shadow-none lg:px-6 lg:py-2.5 lg:bg-white/95 flex items-center justify-between gap-4">
        <!-- Left: Brand & Active Event Pill (Mobile Only) -->
        <div class="flex items-center gap-3">
            <a href="/" class="flex items-center gap-2.5 lg:hidden">
                <div class="w-9 h-9 rounded-2xl overflow-hidden shrink-0 flex items-center justify-center shadow-2xs bg-white border border-[#eff3f4] p-1">
                    <img src="{{ asset('images/logo_jadisatu.png') }}" alt="Logo JADISATU" class="w-full h-full object-contain">
                </div>
                <div class="flex flex-col">
                    <span class="font-black text-[#0f1419] tracking-tight text-sm leading-none">Kasir</span>
                    <span class="text-[10px] text-[#1d9bf0] font-black tracking-wide">JADISATU</span>
                </div>
            </a>
        </div>

        <!-- Right: Authenticated User Profile & Logout -->
        <div class="flex items-center gap-2.5 sm:gap-3">
            @auth
                @php
                    $activeEvent = \App\Models\Event::getActive();
                @endphp

                @if(auth()->user()->isUser())
                    <!-- Interactive Store & Event Switcher Pill in Topbar (Mobile Only) -->
                    <div x-data="{ switcherOpen: false }" class="relative lg:hidden">
                        <button 
                            @click="switcherOpen = !switcherOpen" 
                            @click.outside="switcherOpen = false"
                            class="flex items-center gap-2 px-3 py-1.5 rounded-full bg-[#f7f9f9] hover:bg-[#eff3f4] border border-[#eff3f4] transition-all cursor-pointer text-left max-w-[160px] sm:max-w-[240px] shadow-2xs group"
                            title="Beralih Event / Stand"
                        >
                            <span class="w-2 h-2 rounded-full shrink-0" :class="$store.app.activeStoreEventActive ? 'bg-[#00ba7c]' : 'bg-[#f4212e]'"></span>
                            <div class="truncate min-w-0">
                                <p class="text-xs font-black text-[#0f1419] truncate leading-tight flex items-center gap-1">
                                    <span class="truncate">{{ auth()->user()->store->name ?? 'Stand Warung' }}</span>
                                    <svg class="w-3 h-3 text-[#1d9bf0] shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M22.5 12.5c0-1.58-.875-2.95-2.148-3.6.154-.435.238-.905.238-1.4 0-2.21-1.79-4-4-4-.495 0-.965.084-1.4.238C14.55 2.475 13.18 1.6 11.6 1.6c-1.58 0-2.95.875-3.6 2.148-.435-.154-.905-.238-1.4-.238-2.21 0-4 1.79-4 4 0 .495.084.965.238 1.4C1.575 9.55.7 10.92.7 12.5c0 1.58.875 2.95 2.148 3.6-.154.435-.238.905-.238 1.4 0 2.21 1.79 4 4 4 .495 0 .965-.084 1.4-.238.65 1.273 2.02 2.148 3.6 2.148 1.58 0 2.95-.875 3.6-2.148.435.154.905.238 1.4.238 2.21 0 4-1.79 4-4 0-.495-.084-.965-.238-1.4 1.273-.65 2.148-2.02 2.148-3.6zm-12.28 4.22l-4.22-4.22 1.414-1.414 2.806 2.806 6.806-6.806 1.414 1.414-8.22 8.22z"></path></svg>
                                </p>
                                <p class="text-[10px] text-[#536471] font-semibold truncate leading-tight mt-0.5">
                                    {{ auth()->user()->store->event->name ?? 'Tidak Ada Event' }}
                                </p>
                            </div>
                            <svg class="w-3.5 h-3.5 text-[#536471] shrink-0 ml-0.5" :class="{'rotate-180': switcherOpen}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>

                        <!-- Dropdown List of Events -->
                        <div 
                            x-show="switcherOpen" 
                            x-cloak 
                            x-transition:enter="transition ease-out duration-100"
                            x-transition:enter-start="transform opacity-0 scale-95"
                            x-transition:enter-end="transform opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-75"
                            x-transition:leave-start="transform opacity-100 scale-100"
                            x-transition:leave-end="transform opacity-0 scale-95"
                            class="absolute right-0 mt-2 w-64 bg-white rounded-2xl shadow-xl border border-[#eff3f4] p-1.5 z-50 overflow-hidden"
                        >
                            <div class="px-3 py-1.5 text-[10px] font-black uppercase text-[#536471] border-b border-[#eff3f4]">
                                Riwayat Event & Stand Saya
                            </div>
                            <div class="max-h-56 overflow-y-auto custom-scrollbar">
                                <template x-for="userStore in $store.app.userStores" :key="userStore.id">
                                    <form action="{{ route('user.switch-store') }}" method="POST" class="border-b border-[#eff3f4] last:border-0">
                                        @csrf
                                        <input type="hidden" name="store_id" :value="userStore.id">
                                        <button type="submit" class="w-full text-left px-3 py-2 rounded-xl hover:bg-[#f7f9f9] transition-colors cursor-pointer" :class="{'bg-[#e8f5fd]': userStore.id == $store.app.user.store_id}">
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
                    <!-- Admin / SuperAdmin Pill Badge (Mobile Only) -->
                    <div class="px-3 py-1 rounded-full bg-[#f7f9f9] border border-[#eff3f4] text-[10px] sm:text-xs font-bold text-[#536471] flex items-center gap-1.5 lg:hidden">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#00ba7c]"></span>
                        <span>
                            @if(auth()->user()->isSuperAdmin())
                                Developer
                            @else
                                Admin EO
                            @endif
                        </span>
                    </div>
                @endif

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
                        class="absolute right-0 mt-2 w-56 bg-white rounded-[24px] shadow-xl border border-[#eff3f4] p-2 z-50 overflow-hidden"
                    >
                        <!-- User Info Snippet -->
                        <div class="px-3 py-2.5 border-b border-[#eff3f4] mb-1 flex items-center gap-3">
                            <div class="w-10 h-10 rounded-2xl bg-[#1d9bf0] text-white flex items-center justify-center font-black text-sm shrink-0 overflow-hidden">
                                @if(auth()->user()->avatar)
                                    <img src="{{ asset('storage/' . auth()->user()->avatar) }}" alt="Avatar" class="w-full h-full object-cover">
                                @else
                                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                @endif
                            </div>
                            <div class="truncate">
                                <p class="text-xs font-black text-[#0f1419] truncate">{{ auth()->user()->name }}</p>
                                <p class="text-[10px] text-[#536471] font-bold truncate mt-0.5">
                                    @if(auth()->user()->isSuperAdmin())
                                        👑 Developer
                                    @elseif(auth()->user()->isAdmin())
                                        🛡️ Admin EO
                                    @else
                                        🛒 {{ auth()->user()->store->name ?? 'Kasir Stand' }}
                                    @endif
                                </p>
                            </div>
                        </div>

                        <!-- Profil Link -->
                        <a href="{{ route('profile.edit') }}" class="flex items-center gap-2.5 px-3.5 py-2 rounded-xl text-xs font-bold text-[#0f1419] hover:bg-[#f7f9f9] transition-colors">
                            <svg class="w-4 h-4 text-[#536471]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                            Profil
                        </a>

                        <!-- Logout Form -->
                        <form action="{{ route('logout') }}" method="POST" class="block w-full border-t border-[#eff3f4] mt-1 pt-1">
                            @csrf
                            <button type="submit" class="w-full flex items-center gap-2.5 px-3.5 py-2 rounded-xl text-xs font-bold text-[#f4212e] hover:bg-[#fff3f4] transition-colors text-left cursor-pointer">
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
