@extends('layouts.app')

@section('title', 'Verifikasi Cash EO')
@section('page_title', 'Verifikasi Pembayaran Cash')

@section('content')
<div x-data="{
    isConfirming: false,

    async confirmCash(transactionId) {
        if (this.isConfirming) return;
        
        const result = await Swal.fire({
            title: 'Konfirmasi Pembayaran?',
            text: 'Pastikan uang tunai sudah diterima oleh kasir warung sesuai jumlah.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#00ba7c',
            cancelButtonColor: '#eff3f4',
            confirmButtonText: 'Ya, Sudah Dibayar',
            cancelButtonText: '<span class=\'text-[#0f1419]\'>Batal</span>'
        });

        if (result.isConfirmed) {
            this.isConfirming = true;
            try {
                const response = await fetch(`/admin/verifikasi-cash/${transactionId}/confirm`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (response.ok) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: 'Transaksi cash telah dikonfirmasi.',
                        confirmButtonColor: '#1d9bf0'
                    }).then(() => window.location.reload());
                } else {
                    Swal.fire('Gagal', data.message || 'Terjadi kesalahan.', 'error');
                }
            } catch (e) {
                Swal.fire('Error', 'Gagal terhubung ke server.', 'error');
            } finally {
                this.isConfirming = false;
            }
        }
    }
}">
    <!-- Header Banner -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h2 class="text-xl sm:text-2xl font-black text-[#0f1419] tracking-tight">Antrean Verifikasi Cash</h2>
            <p class="text-xs sm:text-sm text-[#536471] font-semibold mt-0.5">Konfirmasi transaksi tunai yang dilaporkan oleh tenant/warung.</p>
        </div>

        <div class="flex items-center gap-2">
            <span class="px-4 py-2 rounded-full text-xs font-black bg-[#e8f5fd] text-[#1d9bf0] border border-[#bde2f9] shadow-2xs">
                ⚡ Menunggu: <strong>{{ count($pendingTransactions ?? []) }}</strong> Transaksi
            </span>
        </div>
    </div>

    <!-- Info Box -->
    <div class="mb-6 p-4 rounded-2xl bg-[#f7f9f9] border border-[#eff3f4] flex items-start gap-3">
        <div class="p-2 rounded-full bg-[#e8f5fd] text-[#1d9bf0] shrink-0 mt-0.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        </div>
        <div class="text-xs text-[#0f1419] space-y-1">
            <p class="font-black text-[#0f1419]">Prosedur Verifikasi Cash:</p>
            <p class="text-[#536471] font-medium leading-relaxed">
                Pastikan kasir tenant telah menerima uang tunai dari pelanggan. Klik <strong>'Konfirmasi Sudah Dibayar'</strong> untuk menyelesaikan pesanan dan merekam pembagian hasil secara sistem.
            </p>
        </div>
    </div>

    <!-- Verification Queue Cards List -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3.5 sm:gap-4 mb-10">
        @forelse($pendingTransactions ?? [] as $trx)
            <div class="bg-white rounded-2xl sm:rounded-3xl border border-[#eff3f4] p-4 sm:p-5 hover:border-[#00ba7c] hover:shadow-md transition-all flex flex-col justify-between shadow-2xs group relative">
                <div class="space-y-3">
                    <!-- Card Header -->
                    <div class="flex items-start justify-between pb-2.5 border-b border-[#eff3f4]">
                        <div class="min-w-0 flex-1 pr-2">
                            <span class="text-xs font-black text-[#0f1419] block truncate">{{ $trx->invoice_code }}</span>
                            <div class="flex items-center gap-1.5 mt-0.5">
                                <span class="text-[11px] text-[#00ba7c] font-black truncate">{{ $trx->store->name ?? 'Warung' }}</span>
                                <span class="text-[10px] text-[#536471]">•</span>
                                <span class="text-[10px] text-[#536471] font-semibold truncate">Booth {{ $trx->store->booth_number ?? '-' }}</span>
                            </div>
                        </div>
                        <span class="text-[10px] text-[#536471] font-bold shrink-0 bg-[#f7f9f9] px-2 py-0.5 rounded-full border border-[#eff3f4]">{{ $trx->created_at->format('H:i') }}</span>
                    </div>

                    <!-- Total Tagihan Box -->
                    <div class="flex items-center justify-between p-2.5 bg-[#f6fbf9] rounded-xl border border-[#a6e9d5]">
                        <span class="text-xs font-bold text-[#536471]">Total Tagihan:</span>
                        <span class="text-base font-black text-[#00ba7c] tracking-tight">Rp {{ number_format($trx->total_amount, 0, ',', '.') }}</span>
                    </div>
                    
                    <!-- Uang Bayar & Kembalian -->
                    <div class="grid grid-cols-2 gap-2 text-xs">
                        <div class="bg-[#f7f9f9] p-2 rounded-lg border border-[#eff3f4]">
                            <span class="block text-[9px] text-[#536471] font-bold uppercase mb-0.5">Uang Bayar</span>
                            <span class="font-black text-[#0f1419]">Rp {{ number_format($trx->amount_paid ?? 0, 0, ',', '.') }}</span>
                        </div>
                        <div class="bg-[#f7f9f9] p-2 rounded-lg border border-[#eff3f4]">
                            <span class="block text-[9px] text-[#536471] font-bold uppercase mb-0.5">Kembalian</span>
                            <span class="font-black text-[#0f1419]">Rp {{ number_format($trx->change_due ?? 0, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <!-- Clearly Visible Item List -->
                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between text-[10px] font-black uppercase tracking-wider text-[#536471]">
                            <span>Daftar Item:</span>
                            <span>{{ $trx->items->count() }} menu</span>
                        </div>
                        <div class="space-y-1.5 max-h-[90px] overflow-y-auto custom-scrollbar pr-1 bg-[#f7f9f9] p-2.5 rounded-xl border border-[#eff3f4]">
                            @foreach($trx->items as $item)
                                <div class="flex items-center justify-between text-xs py-0.5">
                                    <span class="font-bold text-[#0f1419] truncate pr-2">{{ $item->qty }}x {{ $item->title }}</span>
                                    <span class="font-semibold text-[#536471] shrink-0">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Action Button -->
                <div class="pt-3 border-t border-[#eff3f4] mt-3.5">
                    <button 
                        @click="confirmCash({{ $trx->id }})"
                        :disabled="isConfirming"
                        class="w-full py-2.5 px-3 rounded-full bg-[#00ba7c] hover:bg-[#009b67] disabled:opacity-50 text-white text-xs font-black shadow-xs transition-all flex items-center justify-center gap-1.5 active:scale-95 cursor-pointer"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                        <span>Konfirmasi Sudah Dibayar</span>
                    </button>
                </div>
            </div>
        @empty
            <div class="col-span-full bg-white rounded-3xl border border-[#eff3f4] p-12 text-center max-w-md mx-auto my-8 shadow-2xs">
                <div class="w-16 h-16 bg-[#f6fbf9] rounded-full text-[#00ba7c] flex items-center justify-center mx-auto mb-3">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <h4 class="text-sm font-black text-[#0f1419]">Antrean Verifikasi Kosong</h4>
                <p class="text-xs text-[#536471] font-semibold mt-1">Belum ada transaksi cash baru yang perlu dikonfirmasi.</p>
            </div>
        @endforelse
    </div>

    <!-- History Section -->
    <div class="mb-4">
        <h3 class="text-lg font-black text-[#0f1419]">Riwayat Konfirmasi Terbaru</h3>
    </div>
    
    <div class="bg-white rounded-3xl border border-[#eff3f4] overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse min-w-[600px]">
                <thead>
                    <tr class="bg-[#f7f9f9] border-b border-[#eff3f4]">
                        <th class="px-5 py-3 text-[10px] font-black uppercase tracking-wider text-[#536471]">Waktu</th>
                        <th class="px-5 py-3 text-[10px] font-black uppercase tracking-wider text-[#536471]">Invoice</th>
                        <th class="px-5 py-3 text-[10px] font-black uppercase tracking-wider text-[#536471]">Tenant</th>
                        <th class="px-5 py-3 text-[10px] font-black uppercase tracking-wider text-[#536471] text-right">Total</th>
                        <th class="px-5 py-3 text-[10px] font-black uppercase tracking-wider text-[#536471] text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#eff3f4]">
                    @forelse($historyTransactions ?? [] as $history)
                        <tr class="hover:bg-[#f7f9f9] transition-colors">
                            <td class="px-5 py-3 text-xs text-[#536471] font-semibold">{{ $history->updated_at->format('d M, H:i') }}</td>
                            <td class="px-5 py-3 text-xs font-black text-[#0f1419]">{{ $history->invoice_code }}</td>
                            <td class="px-5 py-3 text-xs text-[#536471] font-semibold">{{ $history->store->name ?? '-' }}</td>
                            <td class="px-5 py-3 text-xs font-black text-[#00ba7c] text-right">Rp {{ number_format($history->total_amount, 0, ',', '.') }}</td>
                            <td class="px-5 py-3 text-center">
                                <span class="inline-block px-2.5 py-1 rounded-lg bg-[#e6f8f2] text-[#00ba7c] text-[10px] font-black border border-[#a6e9d5]">
                                    LUNAS
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-6 text-center text-sm text-[#536471] font-medium">
                                Belum ada riwayat konfirmasi.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
