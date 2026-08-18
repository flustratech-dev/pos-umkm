@extends('layouts.app')

@section('title', 'Profil Anda')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <!-- Header -->
    <div>
        <h1 class="text-xl sm:text-2xl font-black text-[#0f1419] tracking-tight">Profil Anda</h1>
        <p class="text-xs sm:text-sm text-[#536471] font-semibold mt-0.5">Kelola informasi pribadi dan pengaturan akun</p>
    </div>

    <div class="bg-white rounded-3xl border border-[#eff3f4] overflow-hidden">
        <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="p-5 sm:p-6 space-y-6">
            @csrf
            @method('PUT')

            <!-- Foto Profil -->
            <div class="space-y-4">
                <h3 class="text-sm font-black text-[#0f1419] border-b border-[#eff3f4] pb-2">Foto Profil</h3>
                
                <div x-data="{ 
                    previewUrl: '{{ $user->avatar ? asset('storage/' . $user->avatar) : '' }}',
                    handleFileChange(event) {
                        const file = event.target.files[0];
                        if (file) {
                            this.previewUrl = URL.createObjectURL(file);
                        }
                    }
                }" class="flex items-center gap-4">
                    <!-- Avatar Preview -->
                    <div class="w-16 h-16 rounded-full bg-[#eff3f4] border-2 border-white shadow-md overflow-hidden shrink-0 relative flex items-center justify-center">
                        <template x-if="previewUrl">
                            <img :src="previewUrl" alt="Preview" class="w-full h-full object-cover">
                        </template>
                        <template x-if="!previewUrl">
                            <span class="text-[#1d9bf0] font-black text-xl">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                        </template>
                    </div>
                    
                    <!-- File Input -->
                    <div>
                        <label for="avatar_upload" class="inline-flex items-center justify-center px-4 py-2 bg-[#eff3f4] hover:bg-[#e8f5fd] text-[#0f1419] text-xs font-bold rounded-full transition-colors cursor-pointer">
                            Pilih Foto
                        </label>
                        <input type="file" id="avatar_upload" name="avatar" accept="image/png, image/jpeg, image/jpg" class="hidden" @change="handleFileChange">
                        <p class="text-[10px] text-[#536471] font-semibold mt-1">JPG, JPEG, atau PNG. Maks 2MB.</p>
                    </div>
                </div>
            </div>

            <!-- Data Pribadi -->
            <div class="space-y-4 pt-2">
                <h3 class="text-sm font-black text-[#0f1419] border-b border-[#eff3f4] pb-2">Informasi Pribadi</h3>
                
                <div>
                    <label class="block text-xs font-bold text-[#0f1419] mb-1.5">Nama Lengkap</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="w-full px-4 py-2.5 bg-[#f7f9f9] border border-[#eff3f4] rounded-xl text-sm text-[#0f1419] font-medium focus:ring-2 focus:ring-[#1d9bf0] focus:outline-none transition-all">
                </div>

                <div>
                    <label class="block text-xs font-bold text-[#0f1419] mb-1.5">Username (Untuk Login)</label>
                    <input type="text" name="username" value="{{ old('username', $user->username) }}" required class="w-full px-4 py-2.5 bg-[#f7f9f9] border border-[#eff3f4] rounded-xl text-sm text-[#0f1419] font-medium focus:ring-2 focus:ring-[#1d9bf0] focus:outline-none transition-all">
                </div>

                <div>
                    <label class="block text-xs font-bold text-[#0f1419] mb-1.5">Email</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="w-full px-4 py-2.5 bg-[#f7f9f9] border border-[#eff3f4] rounded-xl text-sm text-[#0f1419] font-medium focus:ring-2 focus:ring-[#1d9bf0] focus:outline-none transition-all">
                </div>

                <div>
                    <label class="block text-xs font-bold text-[#0f1419] mb-1.5">Nomor WhatsApp</label>
                    <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" class="w-full px-4 py-2.5 bg-[#f7f9f9] border border-[#eff3f4] rounded-xl text-sm text-[#0f1419] font-medium focus:ring-2 focus:ring-[#1d9bf0] focus:outline-none transition-all">
                </div>
            </div>

            <!-- Ganti Password -->
            <div class="space-y-4 pt-2">
                <h3 class="text-sm font-black text-[#0f1419] border-b border-[#eff3f4] pb-2">Ganti Kata Sandi (Opsional)</h3>
                <p class="text-[10px] text-[#536471]">Kosongkan jika Anda tidak ingin mengubah kata sandi.</p>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-[#0f1419] mb-1.5">Kata Sandi Baru</label>
                        <input type="password" name="password" placeholder="Minimal 8 karakter" class="w-full px-4 py-2.5 bg-[#f7f9f9] border border-[#eff3f4] rounded-xl text-sm text-[#0f1419] font-medium focus:ring-2 focus:ring-[#1d9bf0] focus:outline-none transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-[#0f1419] mb-1.5">Konfirmasi Kata Sandi Baru</label>
                        <input type="password" name="password_confirmation" placeholder="Ulangi kata sandi baru" class="w-full px-4 py-2.5 bg-[#f7f9f9] border border-[#eff3f4] rounded-xl text-sm text-[#0f1419] font-medium focus:ring-2 focus:ring-[#1d9bf0] focus:outline-none transition-all">
                    </div>
                </div>
            </div>

            <!-- Data Toko (Hanya untuk User / Pemilik Stand) -->
            @if($user->isUser() && $user->store)
            <div class="space-y-4 pt-2">
                <h3 class="text-sm font-black text-[#0f1419] border-b border-[#eff3f4] pb-2">Informasi Usaha / Stand</h3>

                <div>
                    <label class="block text-xs font-bold text-[#0f1419] mb-1.5">Nama Usaha / Stand</label>
                    <input type="text" name="store_name" value="{{ old('store_name', $user->store->name) }}" required class="w-full px-4 py-2.5 bg-[#f7f9f9] border border-[#eff3f4] rounded-xl text-sm text-[#0f1419] font-medium focus:ring-2 focus:ring-[#1d9bf0] focus:outline-none transition-all">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-[#0f1419] mb-1.5">Nomor Stand (Opsional)</label>
                        <input type="text" name="store_booth_number" value="{{ old('store_booth_number', $user->store->booth_number) }}" class="w-full px-4 py-2.5 bg-[#f7f9f9] border border-[#eff3f4] rounded-xl text-sm text-[#0f1419] font-medium focus:ring-2 focus:ring-[#1d9bf0] focus:outline-none transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-[#0f1419] mb-1.5">Kategori Produk</label>
                        <select name="store_category" required class="w-full px-4 py-2.5 bg-[#f7f9f9] border border-[#eff3f4] rounded-xl text-sm text-[#0f1419] font-medium focus:ring-2 focus:ring-[#1d9bf0] focus:outline-none transition-all">
                            <option value="makanan" {{ old('store_category', $user->store->category) == 'makanan' ? 'selected' : '' }}>Makanan</option>
                            <option value="minuman" {{ old('store_category', $user->store->category) == 'minuman' ? 'selected' : '' }}>Minuman</option>
                            <option value="snack" {{ old('store_category', $user->store->category) == 'snack' ? 'selected' : '' }}>Snack</option>
                            <option value="merchandise" {{ old('store_category', $user->store->category) == 'merchandise' ? 'selected' : '' }}>Merchandise</option>
                            <option value="lainnya" {{ old('store_category', $user->store->category) == 'lainnya' ? 'selected' : '' }}>Lainnya</option>
                        </select>
                    </div>
                </div>
            </div>
            @endif

            <!-- Submit Button -->
            <div class="pt-4 border-t border-[#eff3f4] flex justify-end">
                <button type="submit" class="px-6 py-2.5 bg-[#1d9bf0] hover:bg-[#1a8cd8] text-white rounded-full font-bold text-sm shadow-sm transition-colors focus:ring-2 focus:ring-offset-2 focus:ring-[#1d9bf0] cursor-pointer">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
