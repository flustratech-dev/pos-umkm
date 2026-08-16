@extends('layouts.app')

@section('title', 'Helpdesk Bantuan')
@section('page_title', 'Pusat Bantuan & Tiket')

@section('content')
<div x-data="{
    activeTab: 'all',
    replyMessage: '',
    selectedTicket: null,

    get myTickets() {
        return $store.app.helpdesk.filter(t => t.store_id === $store.app.getCurrentStore()?.id);
    },

    get filteredTickets() {
        if (this.activeTab === 'all') return this.myTickets;
        return this.myTickets.filter(t => t.status === this.activeTab);
    },

    sendReply(ticketId) {
        if (!this.replyMessage.trim()) return;
        $store.app.addHelpdeskReply(ticketId, this.replyMessage.trim());
        this.replyMessage = '';
    }
}">
    <!-- Header Banner -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h2 class="text-xl sm:text-2xl font-black text-[#0f1419] tracking-tight">Pusat Bantuan Stand</h2>
            <p class="text-xs sm:text-sm text-[#536471] font-semibold mt-0.5">Sampaikan kendala operasional, pembayaran, atau teknis ke panitia EO</p>
        </div>

        <button 
            x-show="$store.app.activeStoreEventActive"
            @click="$store.app.openNewTicketModal()"
            class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-full bg-[#1d9bf0] hover:bg-[#1a8cd8] text-white text-xs sm:text-sm font-black shadow-md shadow-[#1d9bf0]/25 transition-all shrink-0 cursor-pointer"
        >
            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
            <span>Buat Tiket Bantuan</span>
        </button>
    </div>

    <!-- Readonly Banner -->
    <div x-show="!$store.app.activeStoreEventActive" class="mb-6 p-4 rounded-2xl bg-[#f4212e]/10 border border-[#f4212e]/20 flex gap-3">
        <svg class="w-5 h-5 text-[#f4212e] shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
        <div>
            <h3 class="text-sm font-black text-[#f4212e]">Pusat Bantuan Ditutup</h3>
            <p class="text-xs text-[#f4212e] mt-1 font-medium">Event ini telah berakhir. Anda tidak dapat membuat atau membalas tiket kendala baru untuk event ini.</p>
        </div>
    </div>

    <!-- Status Tabs (Twitter Pills) -->
    <div class="flex items-center gap-2 mb-6 overflow-x-auto no-scrollbar py-0.5">
        <button 
            @click="activeTab = 'all'" 
            class="px-4 py-2 rounded-full text-xs font-black transition-all shrink-0 cursor-pointer"
            :class="activeTab === 'all' ? 'bg-[#1d9bf0] text-white shadow-2xs' : 'bg-white hover:bg-[#eff3f4] text-[#0f1419] border border-[#eff3f4]'"
        >
            Semua Tiket (<span x-text="myTickets.length"></span>)
        </button>
        <button 
            @click="activeTab = 'open'" 
            class="px-4 py-2 rounded-full text-xs font-black transition-all shrink-0 cursor-pointer"
            :class="activeTab === 'open' ? 'bg-[#1d9bf0] text-white shadow-2xs' : 'bg-white hover:bg-[#eff3f4] text-[#0f1419] border border-[#eff3f4]'"
        >
            Menunggu Respon (<span x-text="myTickets.filter(t => t.status === 'open').length"></span>)
        </button>
        <button 
            @click="activeTab = 'resolved'" 
            class="px-4 py-2 rounded-full text-xs font-black transition-all shrink-0 cursor-pointer"
            :class="activeTab === 'resolved' ? 'bg-[#1d9bf0] text-white shadow-2xs' : 'bg-white hover:bg-[#eff3f4] text-[#0f1419] border border-[#eff3f4]'"
        >
            Selesai (<span x-text="myTickets.filter(t => t.status === 'resolved').length"></span>)
        </button>
    </div>

    <!-- Tickets List -->
    <div class="space-y-4">
        <template x-for="ticket in filteredTickets" :key="ticket.id">
            <div class="bg-white rounded-2xl sm:rounded-3xl border border-[#eff3f4] p-4 sm:p-6 hover:border-[#bde2f9] transition-all shadow-2xs">
                <div class="flex items-start justify-between gap-3">
                    <div class="space-y-1">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-[#e8f5fd] text-[#1d9bf0] border border-[#bde2f9]" x-text="ticket.category"></span>
                            <span 
                                class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider"
                                :class="ticket.status === 'open' ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800'"
                                x-text="ticket.status === 'open' ? 'Menunggu Tanggapan' : 'Selesai'"
                            ></span>
                            <span class="text-[11px] text-[#536471] font-semibold" x-text="formatDateTime(ticket.created_at)"></span>
                        </div>
                        <h3 class="font-black text-sm sm:text-base text-[#0f1419]" x-text="ticket.subject"></h3>
                        <p class="text-xs text-[#0f1419] font-medium leading-relaxed" x-text="ticket.message"></p>
                    </div>
                </div>

                <!-- Thread Replies -->
                <div class="mt-4 pt-4 border-t border-[#eff3f4] space-y-3" x-show="ticket.replies && ticket.replies.length > 0">
                    <p class="text-[11px] font-black text-[#536471] uppercase tracking-wider">Riwayat Tanggapan Panitia:</p>
                    <template x-for="reply in ticket.replies" :key="reply.id">
                        <div 
                            class="p-3 rounded-2xl text-xs space-y-1"
                            :class="reply.user_role === 'admin' ? 'bg-[#e8f5fd] text-[#0f1419] border border-[#bde2f9]/60' : 'bg-[#f7f9f9] text-[#0f1419] border border-[#eff3f4]'"
                        >
                            <div class="flex items-center justify-between text-[10px] font-bold text-[#536471]">
                                <span class="flex items-center gap-1">
                                    <span class="font-black text-[#0f1419]" x-text="reply.user_name"></span>
                                    <template x-if="reply.user_role === 'admin'">
                                        <span class="text-[#1d9bf0] font-black">(Panitia EO)</span>
                                    </template>
                                </span>
                                <span x-text="formatDateTime(reply.created_at)"></span>
                            </div>
                            <p class="text-xs font-semibold leading-relaxed" x-text="reply.message"></p>
                        </div>
                    </template>
                </div>

                <!-- Fast Reply Form -->
                <div class="mt-3 pt-3 border-t border-[#eff3f4] flex gap-2" x-show="ticket.status === 'open' && $store.app.activeStoreEventActive">
                    <input 
                        type="text" 
                        x-model="replyMessage"
                        placeholder="Ketik balasan atau info tambahan..."
                        class="flex-1 px-4 py-2 bg-[#f7f9f9] border border-[#eff3f4] rounded-full text-xs text-[#0f1419] placeholder-[#536471] focus:ring-2 focus:ring-[#1d9bf0] focus:bg-white font-semibold focus:outline-none"
                        @keydown.enter.prevent="sendReply(ticket.id)"
                    >
                    <button 
                        type="button" 
                        @click="sendReply(ticket.id)"
                        class="px-5 py-2 rounded-full bg-[#1d9bf0] hover:bg-[#1a8cd8] text-white text-xs font-black transition-colors shrink-0 cursor-pointer"
                    >
                        Kirim
                    </button>
                </div>
            </div>
        </template>
    </div>

    <!-- Empty State -->
    <template x-if="filteredTickets.length === 0">
        <div class="bg-white rounded-3xl border border-[#eff3f4] p-12 text-center max-w-sm mx-auto my-6">
            <div class="w-14 h-14 bg-[#e8f5fd] rounded-full text-[#1d9bf0] flex items-center justify-center mx-auto mb-3">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            </div>
            <h4 class="text-sm font-black text-[#0f1419]">Tidak Ada Tiket Kendala Aktif</h4>
            <p class="text-xs text-[#536471] font-semibold mt-1">Stand Anda beroperasi lancar. Jika ada kendala, buat tiket bantuan baru.</p>
        </div>
    </template>

    <!-- NEW TICKET MODAL (SLIDE UP BOTTOM SHEET ON MOBILE, CENTERED ON DESKTOP) -->
    <div 
        x-show="$store.app.ticketModalOpen" 
        x-cloak 
        class="fixed inset-0 z-50 overflow-y-auto"
        aria-labelledby="modal-title"
        role="dialog"
        aria-modal="true"
    >
        <!-- Backdrop -->
        <div 
            x-show="$store.app.ticketModalOpen"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-[#0f1419]/60 backdrop-blur-xs transition-opacity"
            @click="$store.app.ticketModalOpen = false"
        ></div>

        <!-- Position: Bottom on Mobile (`items-end p-0`), Center on Desktop (`sm:items-center sm:p-4`) -->
        <div class="flex min-h-full items-end sm:items-center justify-center p-0 sm:p-4 text-left">
            <div 
                x-show="$store.app.ticketModalOpen"
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
                    <h3 class="text-base font-black text-[#0f1419]">Buat Tiket Bantuan Panitia</h3>
                    <button @click="$store.app.ticketModalOpen = false" class="text-[#0f1419] hover:text-[#1d9bf0] p-1 rounded-full hover:bg-[#eff3f4] cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <form @submit.prevent="$store.app.saveNewTicket()" class="space-y-3.5">
                    <div>
                        <label class="block text-xs font-bold text-[#0f1419] mb-1">Kategori Kendala</label>
                        <select 
                            x-model="$store.app.ticketFormData.category"
                            class="w-full px-3.5 py-2.5 bg-[#f7f9f9] border border-[#eff3f4] rounded-xl text-xs text-[#0f1419] focus:ring-2 focus:ring-[#1d9bf0] focus:outline-none font-semibold"
                        >
                            <option value="Kasir & Pembayaran">Kasir & Pembayaran QRIS</option>
                            <option value="Operasional Event">Operasional Stand & Listrik</option>
                            <option value="Produk & Menu">Produk & Menu</option>
                            <option value="Lainnya">Lainnya</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-[#0f1419] mb-1">Subjek Kendala</label>
                        <input 
                            type="text" 
                            x-model="$store.app.ticketFormData.subject"
                            required
                            placeholder="Contoh: Butuh bantuan verifikasi QRIS nominal Rp..." 
                            class="w-full px-3.5 py-2.5 bg-[#f7f9f9] border border-[#eff3f4] rounded-xl text-xs text-[#0f1419] focus:ring-2 focus:ring-[#1d9bf0] focus:outline-none font-semibold"
                        >
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-[#0f1419] mb-1">Rincian Pesan / Deskripsi</label>
                        <textarea 
                            x-model="$store.app.ticketFormData.message"
                            rows="3"
                            required
                            placeholder="Jelaskan kendala Anda selengkap mungkin..."
                            class="w-full px-3.5 py-2.5 bg-[#f7f9f9] border border-[#eff3f4] rounded-2xl text-xs text-[#0f1419] focus:ring-2 focus:ring-[#1d9bf0] focus:outline-none font-semibold"
                        ></textarea>
                    </div>

                    <div class="pt-2 flex gap-3">
                        <button 
                            type="button" 
                            @click="$store.app.ticketModalOpen = false"
                            class="flex-1 py-3 rounded-full bg-[#eff3f4] hover:bg-slate-200 text-[#0f1419] text-xs font-black transition-colors cursor-pointer"
                        >
                            Batal
                        </button>
                        <button 
                            type="submit" 
                            class="flex-1 py-3 rounded-full bg-[#1d9bf0] hover:bg-[#1a8cd8] text-white text-xs font-black shadow-md shadow-[#1d9bf0]/25 transition-all cursor-pointer"
                        >
                            Kirim Tiket
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
