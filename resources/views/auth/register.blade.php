@extends('layouts.auth')

@section('title', 'Registrasi Warung Baru')

@section('content')
<div x-data="{
    name: '',
    username: '',
    store_name: '',
    phone: '',
    password: '',
    password_confirmation: '',
    agreeTerms: true,
    isLoading: false,

    get strength() {
        return window.evaluatePasswordStrength ? window.evaluatePasswordStrength(this.password) : { score: 0, label: 'Lemah', width: '0%', checks: {} };
    },

    get isMatch() {
        if (!this.password_confirmation) return null;
        return this.password === this.password_confirmation;
    },

    get isFormValid() {
        return this.name.trim() && 
               this.username.trim() && 
               this.store_name.trim() && 
               this.phone.trim() && 
               this.password.length >= 8 && 
               this.isMatch === true && 
               this.agreeTerms;
    },

    async handleRegister() {
        if (!this.isFormValid) {
            $store.app.notify('error', 'Form Belum Lengkap', 'Harap periksa kelengkapan input dan konfirmasi kata sandi.');
            return;
        }

        this.isLoading = true;

        try {
            const token = document.querySelector('meta[name=csrf-token]')?.getAttribute('content') || '';
            const emailGenerated = `${this.username.trim()}@umkm.id`;

            const res = await fetch('/register', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token
                },
                body: JSON.stringify({
                    name: this.name.trim(),
                    store_name: this.store_name.trim(),
                    username: this.username.trim(),
                    email: emailGenerated,
                    phone: this.phone.trim(),
                    password: this.password,
                    password_confirmation: this.password_confirmation
                })
            });

            const data = await res.json();

            if (res.ok && data.success) {
                if (window.Alpine && Alpine.store('app')) {
                    Alpine.store('app').currentRole = 'user';
                    localStorage.setItem('pos_umkm_role', 'user');
                }
                window.location.href = data.redirect || '/user/kasir';
            } else {
                let errMsg = data.message || 'Registrasi gagal. Silakan periksa kembali input Anda.';
                if (data.errors) {
                    const firstKey = Object.keys(data.errors)[0];
                    errMsg = data.errors[firstKey][0];
                }
                alert(errMsg);
            }
        } catch (err) {
            console.error(err);
            alert('Terjadi kesalahan koneksi server. Silakan coba lagi.');
        } finally {
            this.isLoading = false;
        }
    }
}">
    <!-- Heading -->
    <div class="mb-5 text-center">
        <h2 class="text-2xl font-black text-[#0f1419] tracking-tight">Daftar Warung / Stand Baru</h2>
        <p class="text-xs text-[#0f1419] font-semibold mt-1">Registrasi mandiri langsung aktif ke event yang sedang berjalan</p>
    </div>

    <form @submit.prevent="handleRegister()" class="space-y-3.5">
        <!-- Nama Lengkap & Username (2 Columns on SM) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
                <label for="name" class="block text-xs font-bold text-[#0f1419] mb-1">Nama Pemilik</label>
                <input 
                    id="name" 
                    type="text" 
                    x-model="name" 
                    required 
                    class="w-full px-3 py-2 bg-[#f7f9f9] border border-[#eff3f4] rounded-xl text-xs text-[#0f1419] placeholder-[#536471] focus:outline-none focus:ring-2 focus:ring-[#1d9bf0] focus:bg-white transition-all font-semibold"
                    placeholder="Contoh: Siti Rahmawati"
                >
            </div>
            <div>
                <label for="username" class="block text-xs font-bold text-[#0f1419] mb-1">Username Kasir</label>
                <input 
                    id="username" 
                    type="text" 
                    x-model="username" 
                    required 
                    class="w-full px-3 py-2 bg-[#f7f9f9] border border-[#eff3f4] rounded-xl text-xs text-[#0f1419] placeholder-[#536471] focus:outline-none focus:ring-2 focus:ring-[#1d9bf0] focus:bg-white transition-all font-semibold"
                    placeholder="sitirahma123"
                >
            </div>
        </div>

        <!-- Nama Toko & Nomor HP/WA -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
                <label for="store_name" class="block text-xs font-bold text-[#0f1419] mb-1">Nama Toko / Warung</label>
                <input 
                    id="store_name" 
                    type="text" 
                    x-model="store_name" 
                    required 
                    class="w-full px-3 py-2 bg-[#f7f9f9] border border-[#eff3f4] rounded-xl text-xs text-[#0f1419] placeholder-[#536471] focus:outline-none focus:ring-2 focus:ring-[#1d9bf0] focus:bg-white transition-all font-semibold"
                    placeholder="Warung Nasi & Kopi"
                >
            </div>
            <div>
                <label for="phone" class="block text-xs font-bold text-[#0f1419] mb-1">Nomor WhatsApp / HP</label>
                <input 
                    id="phone" 
                    type="tel" 
                    x-model="phone" 
                    required 
                    class="w-full px-3 py-2 bg-[#f7f9f9] border border-[#eff3f4] rounded-xl text-xs text-[#0f1419] placeholder-[#536471] focus:outline-none focus:ring-2 focus:ring-[#1d9bf0] focus:bg-white transition-all font-semibold"
                    placeholder="081234567890"
                >
            </div>
        </div>

        <!-- Kata Sandi -->
        <div>
            <label for="reg-password" class="block text-xs font-bold text-[#0f1419] mb-1">Kata Sandi</label>
            <input 
                id="reg-password" 
                type="password" 
                x-model="password" 
                required 
                class="w-full px-3 py-2 bg-[#f7f9f9] border border-[#eff3f4] rounded-xl text-xs text-[#0f1419] placeholder-[#536471] focus:outline-none focus:ring-2 focus:ring-[#1d9bf0] focus:bg-white transition-all font-semibold"
                placeholder="Minimal 8 karakter..."
            >
            
            <!-- LIVE PASSWORD STRENGTH METER (Twitter UI Style) -->
            <div class="mt-2 p-3 bg-[#f7f9f9] rounded-2xl border border-[#eff3f4] space-y-1.5" x-show="password.length > 0" x-transition>
                <div class="flex items-center justify-between text-[11px]">
                    <span class="text-[#536471] font-bold">Kekuatan Sandi:</span>
                    <span class="font-black uppercase tracking-wider text-[#1d9bf0]" x-text="strength.label"></span>
                </div>
                <!-- Strength Meter Bar -->
                <div class="w-full bg-[#eff3f4] rounded-full h-1.5 overflow-hidden">
                    <div class="h-1.5 rounded-full transition-all duration-300 bg-[#1d9bf0]" :style="`width: ${strength.width}`"></div>
                </div>
                <!-- Live Checklist Indicator -->
                <div class="grid grid-cols-2 gap-1 text-[10px] pt-1">
                    <div class="flex items-center gap-1.5 font-bold" :class="strength.checks?.length ? 'text-[#1d9bf0]' : 'text-[#536471]'">
                        <span>✓</span> <span>Min. 8 karakter</span>
                    </div>
                    <div class="flex items-center gap-1.5 font-bold" :class="strength.checks?.uppercase && strength.checks?.lowercase ? 'text-[#1d9bf0]' : 'text-[#536471]'">
                        <span>✓</span> <span>Huruf besar & kecil</span>
                    </div>
                    <div class="flex items-center gap-1.5 font-bold" :class="strength.checks?.number ? 'text-[#1d9bf0]' : 'text-[#536471]'">
                        <span>✓</span> <span>Mengandung angka</span>
                    </div>
                    <div class="flex items-center gap-1.5 font-bold" :class="strength.checks?.special ? 'text-[#1d9bf0]' : 'text-[#536471]'">
                        <span>✓</span> <span>Simbol khusus (@, #, $)</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Konfirmasi Kata Sandi -->
        <div>
            <div class="flex items-center justify-between mb-1">
                <label for="password_confirmation" class="block text-xs font-bold text-[#0f1419]">Ulangi Kata Sandi</label>
                <!-- Real-time Match Indicator -->
                <template x-if="password_confirmation.length > 0">
                    <span class="text-[11px] font-black" :class="isMatch ? 'text-[#1d9bf0]' : 'text-[#f4212e]'" x-text="isMatch ? '✓ Cocok' : '✗ Belum Cocok'"></span>
                </template>
            </div>
            <input 
                id="password_confirmation" 
                type="password" 
                x-model="password_confirmation" 
                required 
                class="w-full px-3 py-2 bg-[#f7f9f9] border rounded-xl text-xs text-[#0f1419] placeholder-[#536471] focus:outline-none focus:ring-2 focus:ring-[#1d9bf0] focus:bg-white transition-all font-semibold"
                :class="{
                    'border-[#eff3f4]': isMatch === null,
                    'border-[#1d9bf0] bg-[#e8f5fd]/30': isMatch === true,
                    'border-[#f4212e] bg-rose-50/30': isMatch === false
                }"
                placeholder="Ulangi kata sandi di atas..."
            >
        </div>

        <!-- Agreement Terms -->
        <div class="pt-1">
            <label class="flex items-start gap-2 cursor-pointer">
                <input 
                    type="checkbox" 
                    x-model="agreeTerms" 
                    class="w-4 h-4 mt-0.5 rounded border-slate-300 text-[#1d9bf0] focus:ring-[#1d9bf0]"
                >
                <span class="text-[11px] text-[#0f1419] font-medium leading-snug">
                    Saya menyetujui ketentuan bagi hasil <strong>75% Warung</strong> dan <strong>25% EO</strong> untuk transaksi di event ini.
                </span>
            </label>
        </div>

        <!-- Submit Button (Twitter Style Pill) -->
        <button 
            type="submit" 
            :disabled="!isFormValid || isLoading"
            class="w-full mt-2 py-3 px-4 rounded-full bg-[#1d9bf0] hover:bg-[#1a8cd8] text-white text-xs sm:text-sm font-black shadow-md shadow-[#1d9bf0]/25 transition-all flex items-center justify-center gap-2 disabled:opacity-40 disabled:cursor-not-allowed active:scale-[0.98] cursor-pointer"
        >
            <template x-if="isLoading">
                <svg class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
            </template>
            <span x-text="isLoading ? 'Membuat Akun...' : 'Daftar & Langsung Buka Kasir'"></span>
        </button>
    </form>

    <!-- Login Link -->
    <div class="mt-5 pt-4 border-t border-[#eff3f4] text-center">
        <p class="text-xs text-[#0f1419] font-medium">
            Sudah memiliki akun kasir?
            <a href="/login" class="font-black text-[#1d9bf0] hover:underline ml-1 transition-colors">
                Masuk di sini &rarr;
            </a>
        </p>
    </div>
</div>
@endsection
