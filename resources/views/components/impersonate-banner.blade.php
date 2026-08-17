@if(session()->has('impersonator_id'))
    <div class="bg-[#0f1419] text-white px-4 py-2.5 text-xs font-semibold flex flex-col sm:flex-row items-center justify-between gap-2.5 sticky top-0 z-50 shadow-md border-b-2 border-amber-400">
        <div class="flex items-center gap-2 flex-wrap text-center sm:text-left">
            <span class="px-2.5 py-0.5 rounded-full bg-amber-400 text-[#0f1419] font-black text-[10px] uppercase tracking-wider flex items-center gap-1 shadow-2xs">
                <span class="w-1.5 h-1.5 rounded-full bg-[#0f1419] animate-ping"></span>
                Mode Inspeksi Stand
            </span>
            <span class="text-white text-xs">
                Anda sedang melihat sistem sebagai stand <strong class="text-amber-300 font-bold">{{ auth()->user()->store->name ?? auth()->user()->name }}</strong> (Admin: {{ session('impersonator_name') }})
            </span>
        </div>

        <form action="{{ route('impersonate.leave') }}" method="POST" class="shrink-0">
            @csrf
            <button 
                type="submit" 
                class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-full bg-amber-400 hover:bg-amber-300 text-[#0f1419] font-black text-xs transition-all shadow-sm active:scale-95 cursor-pointer"
                title="Selesai inspeksi dan kembali ke dashboard admin"
            >
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                <span>Kembali ke Dashboard Admin</span>
            </button>
        </form>
    </div>
@endif
