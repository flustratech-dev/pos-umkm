@extends('layouts.app')

@section('title', 'Detail Event: ' . $event->name)

@section('content')
<div x-data="{
    tenantForm: {
        owner_name: '',
        store_name: '',
        booth_code: ''
    },
    isSubmitting: false,
    generatedLink: null,

    async submitTenant() {
        if (!this.tenantForm.owner_name || !this.tenantForm.store_name || !this.tenantForm.booth_code) {
            Swal.fire({
                icon: 'warning',
                title: 'Data Tidak Lengkap',
                text: 'Harap isi semua kolom pendaftaran.',
                confirmButtonColor: '#1d9bf0'
            });
            return;
        }

        this.isSubmitting = true;
        try {
            const response = await fetch('{{ route('admin.events.register-tenant', $event->id) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(this.tenantForm)
            });

            const data = await response.json();

            if (response.ok && data.success) {
                this.generatedLink = data.access_url || data.access_link;
                Swal.fire({
                    icon: 'success',
                    title: 'Tenant Berhasil Didaftarkan!',
                    text: 'Link akses berhasil dibuat. Halaman akan diperbarui.',
                    confirmButtonColor: '#1d9bf0'
                }).then(() => {
                    window.location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Mendaftar',
                    text: data.message || 'Terjadi kesalahan sistem.',
                    confirmButtonColor: '#f4212e'
                });
            }
        } catch (error) {
            Swal.fire({
                icon: 'error',
                title: 'Terjadi Kesalahan',
                text: 'Gagal terhubung ke server.',
                confirmButtonColor: '#f4212e'
            });
        } finally {
            this.isSubmitting = false;
        }
    },

    copyLink(link) {
        if (!link || link === '#') {
            Swal.fire('Info', 'Link akses belum tersedia.', 'info');
            return;
        }
        navigator.clipboard.writeText(link).then(() => {
            Swal.fire({
                icon: 'success',
                title: 'Disalin!',
                text: 'Link akses berhasil disalin ke clipboard.',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 2000
            });
        });
    },

    async regenerateLink(storeId) {
        const confirm = await Swal.fire({
            title: 'Regenerate Link?',
            text: 'Link lama akan menjadi tidak aktif. Apakah Anda yakin?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#1d9bf0',
            cancelButtonColor: '#eff3f4',
            confirmButtonText: 'Ya, Regenerate!',
            cancelButtonText: '<span class=\'text-[#0f1419]\'>Batal</span>'
        });

        if (confirm.isConfirmed) {
            try {
                const response = await fetch(`/admin/events/{{ $event->id }}/tenants/${storeId}/regenerate-link`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: 'Link akses baru berhasil dibuat.',
                        confirmButtonColor: '#1d9bf0'
                    }).then(() => window.location.reload());
                } else {
                    Swal.fire('Gagal', data.message || 'Terjadi kesalahan.', 'error');
                }
            } catch (e) {
                Swal.fire('Error', 'Gagal terhubung ke server.', 'error');
            }
        }
    },

    async deleteTenant(storeId) {
        const confirm = await Swal.fire({
            title: 'Hapus Tenant?',
            text: 'Data tenant dan warung akan dihapus permanen. Apakah Anda yakin?',
            icon: 'error',
            showCancelButton: true,
            confirmButtonColor: '#f4212e',
            cancelButtonColor: '#eff3f4',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: '<span class=\'text-[#0f1419]\'>Batal</span>'
        });

        if (confirm.isConfirmed) {
            try {
                const response = await fetch(`/admin/events/{{ $event->id }}/tenants/${storeId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Terhapus!',
                        text: 'Data tenant berhasil dihapus.',
                        confirmButtonColor: '#1d9bf0'
                    }).then(() => window.location.reload());
                } else {
                    Swal.fire('Gagal', data.message || 'Terjadi kesalahan.', 'error');
                }
            } catch (e) {
                Swal.fire('Error', 'Gagal terhubung ke server.', 'error');
            }
        }
    }
}">

    <!-- Event Info Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <a href="{{ route('admin.events.index') }}" class="text-[#536471] hover:text-[#1d9bf0] transition-colors bg-white p-1.5 rounded-full border border-[#eff3f4]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                </a>
                <h2 class="text-xl sm:text-2xl font-black text-[#0f1419] tracking-tight">{{ $event->name }}</h2>
            </div>
            <p class="text-xs sm:text-sm text-[#536471] font-semibold mt-0.5 ml-9">
                {{ \Carbon\Carbon::parse($event->start_date)->format('d M Y') }} - {{ \Carbon\Carbon::parse($event->end_date)->format('d M Y') }} • {{ $event->location }}
            </p>
        </div>
        <div>
            @if($event->is_active)
                <span class="px-4 py-2 rounded-full text-xs font-black bg-[#e8f5fd] text-[#1d9bf0] border border-[#bde2f9] shadow-2xs flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-[#1d9bf0] animate-pulse"></span>
                    Event Aktif
                </span>
            @else
                <span class="px-4 py-2 rounded-full text-xs font-black bg-[#f7f9f9] text-[#536471] border border-[#eff3f4] shadow-2xs flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-[#536471]"></span>
                    Event Selesai
                </span>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Daftarkan Tenant Baru -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-3xl border border-[#eff3f4] p-5 sm:p-6 shadow-xs sticky top-6">
                <h3 class="text-lg font-black text-[#0f1419] mb-1">Daftarkan Tenant Baru</h3>
                <p class="text-xs text-[#536471] font-medium mb-5">Buat akses link unik untuk tenant baru.</p>

                <form @submit.prevent="submitTenant" class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-[#0f1419] mb-1.5">Nama Pelaku Usaha</label>
                        <input type="text" x-model="tenantForm.owner_name" required class="w-full px-4 py-2.5 bg-[#f7f9f9] border border-[#eff3f4] rounded-xl text-xs sm:text-sm text-[#0f1419] placeholder-[#536471] focus:ring-2 focus:ring-[#1d9bf0] focus:outline-none font-semibold transition-colors" placeholder="Contoh: Budi Santoso">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-[#0f1419] mb-1.5">Nama Warung / Stand</label>
                        <input type="text" x-model="tenantForm.store_name" required class="w-full px-4 py-2.5 bg-[#f7f9f9] border border-[#eff3f4] rounded-xl text-xs sm:text-sm text-[#0f1419] placeholder-[#536471] focus:ring-2 focus:ring-[#1d9bf0] focus:outline-none font-semibold transition-colors" placeholder="Contoh: Nasi Goreng Budi">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-[#0f1419] mb-1.5">Kode Tenda / Nomor Booth</label>
                        <input type="text" x-model="tenantForm.booth_code" required class="w-full px-4 py-2.5 bg-[#f7f9f9] border border-[#eff3f4] rounded-xl text-xs sm:text-sm text-[#0f1419] placeholder-[#536471] focus:ring-2 focus:ring-[#1d9bf0] focus:outline-none font-semibold transition-colors" placeholder="Contoh: A01">
                    </div>

                    <div class="pt-2">
                        <button type="submit" :disabled="isSubmitting" class="w-full flex items-center justify-center gap-2 px-4 py-3 rounded-full bg-[#1d9bf0] hover:bg-[#1a8cd8] disabled:opacity-50 text-white text-sm font-black transition-colors shadow-xs active:scale-95 cursor-pointer">
                            <template x-if="!isSubmitting">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                            </template>
                            <template x-if="isSubmitting">
                                <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            </template>
                            <span x-text="isSubmitting ? 'Memproses...' : 'Daftarkan Tenant'"></span>
                        </button>
                    </div>
                </form>

                <!-- Generated Link Result -->
                <div x-show="generatedLink" x-transition class="mt-4 p-4 rounded-2xl bg-[#e8f5fd] border border-[#bde2f9]">
                    <p class="text-[11px] font-bold text-[#1d9bf0] mb-2 uppercase tracking-wide">Berhasil! Link Akses:</p>
                    <div class="flex items-center gap-2 bg-white rounded-xl border border-[#eff3f4] p-2">
                        <input type="text" readonly :value="generatedLink" class="flex-1 bg-transparent text-xs font-semibold text-[#0f1419] focus:outline-none px-1">
                        <button @click="copyLink(generatedLink)" class="shrink-0 p-1.5 rounded-lg bg-[#f7f9f9] hover:bg-[#eff3f4] text-[#536471] hover:text-[#1d9bf0] transition-colors" title="Copy Link">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabel Tenant Terdaftar -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-3xl border border-[#eff3f4] overflow-hidden shadow-xs flex flex-col h-full">
                <div class="p-5 border-b border-[#eff3f4]">
                    <h3 class="text-lg font-black text-[#0f1419]">Tenant Terdaftar</h3>
                    <p class="text-xs text-[#536471] font-medium mt-0.5">Daftar tenant yang terdaftar pada event {{ $event->name }}.</p>
                </div>
                
                <div class="flex-1 overflow-x-auto">
                    <table class="w-full text-left border-collapse min-w-[600px]">
                        <thead>
                            <tr class="bg-[#f7f9f9] border-b border-[#eff3f4]">
                                <th class="px-5 py-3 text-[10px] font-black uppercase tracking-wider text-[#536471]">Tenda</th>
                                <th class="px-5 py-3 text-[10px] font-black uppercase tracking-wider text-[#536471]">Tenant</th>
                                <th class="px-5 py-3 text-[10px] font-black uppercase tracking-wider text-[#536471]">Akses Link</th>
                                <th class="px-5 py-3 text-[10px] font-black uppercase tracking-wider text-[#536471] text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#eff3f4]">
                            @forelse($tenants as $tenant)
                            <tr class="hover:bg-[#f7f9f9] transition-colors group">
                                <td class="px-5 py-4 align-top">
                                    <span class="inline-block px-2.5 py-1 rounded-lg bg-[#e8f5fd] text-[#1d9bf0] text-xs font-black border border-[#bde2f9]">
                                        {{ $tenant->booth_number }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 align-top">
                                    <div class="font-black text-sm text-[#0f1419]">{{ $tenant->name }}</div>
                                    <div class="text-xs text-[#536471] font-semibold mt-0.5">{{ $tenant->owner->name ?? 'Pemilik' }}</div>
                                </td>
                                <td class="px-5 py-4 align-top max-w-[200px]">
                                    @php
                                        $link = $tenant->access_uuid ? route('tenant.access', ['uuid' => $tenant->access_uuid]) : '#';
                                    @endphp
                                    <div class="flex items-center gap-2 bg-white rounded-lg border border-[#eff3f4] p-1.5 shadow-2xs">
                                        <div class="flex-1 min-w-0">
                                            <div class="text-[10px] font-medium text-[#536471] truncate">{{ $link }}</div>
                                        </div>
                                        <button @click="copyLink('{{ $link }}')" class="shrink-0 p-1.5 rounded-md bg-[#f7f9f9] hover:bg-[#1d9bf0] text-[#536471] hover:text-white transition-colors cursor-pointer" title="Copy Link">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                                        </button>
                                    </div>
                                </td>
                                <td class="px-5 py-4 align-top text-right">
                                    <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <button @click="regenerateLink({{ $tenant->id }})" class="p-2 rounded-full hover:bg-[#e8f5fd] text-[#1d9bf0] transition-colors cursor-pointer" title="Regenerate Link">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                        </button>
                                        <button @click="deleteTenant({{ $tenant->id }})" class="p-2 rounded-full hover:bg-rose-50 text-[#f4212e] transition-colors cursor-pointer" title="Hapus Tenant">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-5 py-8 text-center text-sm text-[#536471] font-medium bg-[#f7f9f9]">
                                    Belum ada tenant yang terdaftar pada event ini.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
