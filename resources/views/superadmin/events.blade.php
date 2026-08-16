@extends('layouts.app')

@section('title', 'Kelola Multi-Event Platform')

@php
    $rolePrefix = auth()->user()->isSuperAdmin() ? 'superadmin' : 'admin';
@endphp

@section('content')
<div x-data class="space-y-6">

    <!-- Header & Action (Twitter UI) -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-3.5 py-0.5 rounded-full bg-[#e8f5fd] text-[#1d9bf0] text-[10px] font-black uppercase border border-[#bde2f9]">Multi-Event Platform</span>
                <span class="text-xs text-[#0f1419] font-semibold">Single Active Rule</span>
            </div>
            <h2 class="text-xl sm:text-2xl font-black text-[#0f1419] tracking-tight mt-1">Daftar Event & Pengelolaan</h2>
            <p class="text-xs sm:text-sm text-[#0f1419] font-medium mt-0.5">Buat event baru atau aktifkan event yang siap beroperasi</p>
        </div>

        <button 
            @click="$store.app.openCreateEventModal()"
            type="button" 
            class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-full bg-[#1d9bf0] hover:bg-[#1a8cd8] text-white text-xs sm:text-sm font-black shadow-md shadow-[#1d9bf0]/25 transition-all cursor-pointer active:scale-95"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
            <span>Buat Event Baru</span>
        </button>
    </div>

    <!-- Events List Grid (Twitter UI) -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        <template x-for="event in $store.app.events" :key="event.id">
            <div 
                class="bg-white rounded-3xl border p-6 shadow-xs flex flex-col justify-between space-y-4 relative transition-all"
                :class="event.is_active ? 'border-2 border-[#1d9bf0] shadow-md ring-4 ring-[#1d9bf0]/10' : 'border-[#eff3f4]'"
            >
                <div>
                    <!-- Status Badge -->
                    <div class="flex items-center justify-between">
                        <span 
                            class="px-3.5 py-1 rounded-full text-xs font-black flex items-center gap-1.5"
                            :class="event.is_active ? 'bg-[#e8f5fd] text-[#1d9bf0] border border-[#bde2f9]' : 'bg-[#eff3f4] text-[#536471]'"
                        >
                            <span class="w-2 h-2 rounded-full" :class="event.is_active ? 'bg-[#1d9bf0] animate-pulse' : 'bg-[#536471]'"></span>
                            <span x-text="event.is_active ? 'Sedang Berjalan (Aktif)' : 'Diarsipkan (Selesai)'"></span>
                        </span>

                        <span class="text-[11px] font-mono text-[#536471]" x-text="`#${event.id}`"></span>
                    </div>

                    <!-- Title & Slug -->
                    <h3 class="text-base sm:text-lg font-black text-[#0f1419] mt-3 leading-snug" x-text="event.name"></h3>
                    <p class="text-xs text-[#536471] font-mono mt-0.5" x-text="`/${event.slug}`"></p>
                </div>

                <!-- Event Details -->
                <div class="space-y-2 text-xs text-[#536471] p-3.5 bg-[#f7f9f9] rounded-2xl border border-[#eff3f4]">
                    <div class="flex items-center gap-2">
                        <span class="text-[#536471]">📅</span>
                        <span class="font-bold text-[#0f1419]" x-text="(event.start_date && event.end_date) ? `${(event.start_date||'').substring(0,10)} s/d ${(event.end_date||'').substring(0,10)}` : 'Jadwal belum diatur'"></span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-[#536471]">📍</span>
                        <span class="font-bold text-[#0f1419] truncate" x-text="event.location || 'Lokasi Event'"></span>
                    </div>
                </div>

                <!-- Activate Action (Twitter UI Pill) -->
                <div class="pt-2 border-t border-[#eff3f4] flex items-center justify-between gap-2">
                    <template x-if="event.is_active">
                        <span class="text-xs font-black text-[#1d9bf0] flex items-center gap-1">
                            ✓ Event Aktif Saat Ini
                        </span>
                    </template>

                    <div class="flex items-center gap-2">
                        <button 
                            @click="$store.app.openEditEventModal(event)"
                            type="button" 
                            class="p-2 bg-[#f7f9f9] hover:bg-[#eff3f4] text-[#0f1419] rounded-full transition-colors cursor-pointer"
                            title="Edit Event"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                        </button>
                        <template x-if="!event.is_active">
                            <form :action="`/{{ $rolePrefix }}/events/${event.id}`" method="POST" class="inline" onsubmit="return confirm('Yakin ingin menghapus event ini? Semua data terkait (warung, produk, transaksi) mungkin akan terpengaruh jika ada.');">
                                @csrf
                                @method('DELETE')
                                <button 
                                    type="submit" 
                                    class="p-2 bg-[#fef2f2] hover:bg-[#fee2e2] text-[#ef4444] rounded-full transition-colors cursor-pointer"
                                    title="Hapus Event"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </form>
                        </template>
                    </div>

                    <template x-if="!event.is_active">
                        <form :action="`/{{ $rolePrefix }}/events/${event.id}/activate`" method="POST" class="w-full mt-3 sm:mt-0 sm:w-auto">
                            @csrf
                            <button 
                                type="submit" 
                                class="w-full py-2.5 px-3 bg-[#1d9bf0] hover:bg-[#1a8cd8] text-white text-xs font-black rounded-full transition-colors text-center shadow-xs cursor-pointer"
                            >
                                Aktifkan Event Ini &rarr;
                            </button>
                        </form>
                    </template>
                </div>
            </div>
        </template>
    </div>

    <!-- CREATE EVENT MODAL (SLIDE UP BOTTOM SHEET ON MOBILE) -->
    <div 
        x-show="$store.app.eventModalOpen" 
        x-cloak 
        class="fixed inset-0 z-50 overflow-y-auto"
        aria-labelledby="modal-title"
        role="dialog"
        aria-modal="true"
    >
        <!-- Backdrop -->
        <div 
            x-show="$store.app.eventModalOpen"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-[#0f1419]/60 backdrop-blur-xs transition-opacity" 
            @click="$store.app.eventModalOpen = false"
        ></div>

        <!-- Position: Bottom on Mobile (`items-end p-0`), Center on Desktop (`sm:items-center sm:p-4`) -->
        <div class="flex min-h-full items-end sm:items-center justify-center p-0 sm:p-4 text-left">
            <div 
                x-show="$store.app.eventModalOpen"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-full sm:translate-y-4 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-full sm:translate-y-4 sm:scale-95"
                class="relative w-full max-w-md bg-white rounded-t-3xl sm:rounded-3xl p-6 sm:p-8 shadow-2xl space-y-4 border-t sm:border border-[#eff3f4] text-left max-h-[92vh] sm:max-h-none overflow-y-auto custom-scrollbar"
            >
                <!-- Mobile Drag / Pull Indicator Handle -->
                <div class="w-12 h-1.5 bg-[#cfd9de] rounded-full mx-auto mb-2 sm:hidden"></div>

                <div class="flex items-center justify-between pb-3 border-b border-[#eff3f4]">
                    <h3 class="text-base font-black text-[#0f1419]" x-text="$store.app.isEditingEvent ? 'Edit Event' : 'Buat Event Baru'"></h3>
                    <button @click="$store.app.eventModalOpen = false" class="text-[#0f1419] hover:text-[#1d9bf0] p-1.5 rounded-full hover:bg-[#eff3f4] cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <form 
                    :action="$store.app.isEditingEvent ? `/{{ $rolePrefix }}/events/${$store.app.eventFormData.id}` : '{{ route($rolePrefix . '.events.store') }}'" 
                    method="POST" 
                    enctype="multipart/form-data" 
                    class="space-y-3.5"
                >
                    @csrf
                    <input type="hidden" name="_method" :value="$store.app.isEditingEvent ? 'PUT' : 'POST'">
                    <div>
                        <label class="block text-xs font-bold text-[#0f1419] mb-1">Nama Event</label>
                        <input 
                            type="text" 
                            name="name"
                            x-model="$store.app.eventFormData.name"
                            required
                            placeholder="Nama event" 
                            class="w-full px-3.5 py-2.5 bg-[#f7f9f9] border border-[#eff3f4] rounded-xl text-xs text-[#0f1419] focus:ring-2 focus:ring-[#1d9bf0] focus:outline-none font-semibold"
                        >
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-[#0f1419] mb-1">Gambar QRIS (Opsional)</label>
                        <template x-if="$store.app.isEditingEvent && $store.app.eventFormData.qris_image_url">
                            <div class="mb-3">
                                <p class="text-[10px] text-[#536471] mb-1">QRIS Saat Ini:</p>
                                <img :src="$store.app.eventFormData.qris_image_url" alt="QRIS Event" class="w-24 h-24 object-contain rounded-xl border border-[#eff3f4] bg-white p-1">
                            </div>
                        </template>
                        <input 
                            type="file" 
                            name="qris_image"
                            accept="image/*"
                            class="w-full text-xs text-[#0f1419] file:mr-3 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-black file:bg-[#e8f5fd] file:text-[#1d9bf0] hover:file:bg-[#d8eefc] cursor-pointer"
                        >
                        <p class="text-[10px] text-[#536471] mt-1">Kosongkan jika tidak ingin mengubah QRIS.</p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-[#0f1419] mb-1">Lokasi Penyelenggaraan</label>
                        <input 
                            type="text" 
                            name="location"
                            x-model="$store.app.eventFormData.location"
                            placeholder="Lokasi event" 
                            class="w-full px-3.5 py-2.5 bg-[#f7f9f9] border border-[#eff3f4] rounded-xl text-xs text-[#0f1419] focus:ring-2 focus:ring-[#1d9bf0] focus:outline-none font-semibold"
                        >
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-[#0f1419] mb-1">Tanggal Mulai</label>
                            <input 
                                type="date" 
                                name="start_date"
                                x-model="$store.app.eventFormData.start_date"
                                class="w-full px-3.5 py-2.5 bg-[#f7f9f9] border border-[#eff3f4] rounded-xl text-xs text-[#0f1419] focus:ring-2 focus:ring-[#1d9bf0] focus:outline-none font-semibold"
                            >
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-[#0f1419] mb-1">Tanggal Selesai</label>
                            <input 
                                type="date" 
                                name="end_date"
                                x-model="$store.app.eventFormData.end_date"
                                class="w-full px-3.5 py-2.5 bg-[#f7f9f9] border border-[#eff3f4] rounded-xl text-xs text-[#0f1419] focus:ring-2 focus:ring-[#1d9bf0] focus:outline-none font-semibold"
                            >
                        </div>
                    </div>

                    <div class="pt-2 flex gap-3">
                        <button 
                            type="button" 
                            @click="$store.app.eventModalOpen = false"
                            class="flex-1 py-3 rounded-full bg-[#eff3f4] hover:bg-slate-200 text-[#0f1419] text-xs font-black cursor-pointer"
                        >
                            Batal
                        </button>
                        <button 
                            type="submit" 
                            class="flex-1 py-3 rounded-full bg-[#1d9bf0] hover:bg-[#1a8cd8] text-white text-xs font-black shadow-md shadow-[#1d9bf0]/25 cursor-pointer"
                        >
                            Simpan Event
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ACTIVATE EVENT CONFIRMATION MODAL (SLIDE UP BOTTOM SHEET ON MOBILE) -->
    <div 
        x-show="$store.app.activateEventConfirmOpen" 
        x-cloak 
        class="fixed inset-0 z-50 overflow-y-auto"
        aria-labelledby="modal-title"
        role="dialog"
        aria-modal="true"
    >
        <div class="fixed inset-0 bg-[#0f1419]/60 backdrop-blur-xs" @click="$store.app.activateEventConfirmOpen = false"></div>
        <div class="flex min-h-full items-end sm:items-center justify-center p-0 sm:p-4 text-center">
            <div 
                x-show="$store.app.activateEventConfirmOpen"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-full sm:translate-y-4 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-full sm:translate-y-4 sm:scale-95"
                class="relative w-full max-w-sm bg-white rounded-t-3xl sm:rounded-3xl p-6 shadow-2xl text-center space-y-4 border-t sm:border border-[#eff3f4]"
            >
                <div class="w-12 h-1.5 bg-[#cfd9de] rounded-full mx-auto mb-2 sm:hidden"></div>
                <div class="w-12 h-12 rounded-full bg-[#e8f5fd] text-[#1d9bf0] flex items-center justify-center mx-auto">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <h4 class="text-base font-black text-[#0f1419]">Aktifkan Event Ini?</h4>
                <p class="text-xs text-[#0f1419] leading-relaxed font-medium">
                    Event <strong class="text-[#0f1419] font-black" x-text="$store.app.eventToActivate?.name"></strong> akan dijadikan event aktif. Event sebelumnya akan otomatis diarsipkan.
                </p>
                <div class="flex gap-2.5 pt-2">
                    <button @click="$store.app.activateEventConfirmOpen = false" class="flex-1 py-3 rounded-full bg-[#eff3f4] font-black text-xs text-[#0f1419] cursor-pointer">Batal</button>
                    <button @click="$store.app.confirmActivateEvent()" class="flex-1 py-3 rounded-full bg-[#1d9bf0] hover:bg-[#1a8cd8] font-black text-xs text-white cursor-pointer shadow-md shadow-[#1d9bf0]/25">Ya, Aktifkan</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
