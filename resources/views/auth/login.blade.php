@extends('layouts.auth')

@section('title', 'Masuk ke Akun')

@section('content')
<div x-data="{
    email: 'warung.busiti@gmail.com',
    password: 'password123',
    remember: true,
    isLoading: false,

    handleLogin(role = null) {
        this.isLoading = true;
        
        let targetRole = role || 'user';
        if (!role) {
            if (this.email.includes('admin@')) targetRole = 'admin';
            else if (this.email.includes('superadmin@')) targetRole = 'superadmin';
            else targetRole = 'user';
        }

        $store.app.currentRole = targetRole;
        localStorage.setItem('pos_umkm_role', targetRole);

        setTimeout(() => {
            this.isLoading = false;
            $store.app.notify('success', 'Login Berhasil', `Selamat datang kembali di ${$store.app.getRoleLabel(targetRole)}`);
            if (targetRole === 'user') window.location.href = '/user/kasir';
            else if (targetRole === 'admin') window.location.href = '/admin/dashboard';
            else if (targetRole === 'superadmin') window.location.href = '/superadmin/dashboard';
        }, 400);
    },

    setDemoCredentials(role) {
        if (role === 'user') {
            this.email = 'warung.busiti@gmail.com';
            this.password = 'password123';
            this.handleLogin('user');
        } else if (role === 'admin') {
            this.email = 'admin@pos-umkm.id';
            this.password = 'password123';
            this.handleLogin('admin');
        } else if (role === 'superadmin') {
            this.email = 'superadmin@pos-umkm.id';
            this.password = 'password123';
            this.handleLogin('superadmin');
        }
    }
}">
    <!-- Heading -->
    <div class="mb-6 text-center">
        <h2 class="text-2xl font-black text-[#0f1419] tracking-tight">Masuk ke Akun Anda</h2>
        <p class="text-sm text-[#0f1419] font-semibold mt-1">Gunakan akun tenant stand atau panitia event</p>
    </div>

    <!-- Quick Demo Role Switcher / Shortcut Buttons (Twitter Style Pills) -->
    <div class="mb-6 p-3.5 bg-[#f7f9f9] rounded-2xl border border-[#eff3f4]">
        <p class="text-xs font-black text-[#0f1419] mb-2 flex items-center justify-between">
            <span>⚡ Demo Akses Cepat (1-Klik):</span>
            <span class="text-[10px] text-[#1d9bf0] font-black">Pilih Role</span>
        </p>
        <div class="grid grid-cols-3 gap-2 text-xs">
            <button 
                type="button" 
                @click="setDemoCredentials('user')" 
                class="py-2.5 px-2 rounded-full bg-white hover:bg-[#e8f5fd] text-[#1d9bf0] border border-[#bde2f9] font-black transition-all text-center shadow-2xs cursor-pointer"
            >
                🛒 Warung
            </button>
            <button 
                type="button" 
                @click="setDemoCredentials('admin')" 
                class="py-2.5 px-2 rounded-full bg-white hover:bg-[#e8f5fd] text-[#1d9bf0] border border-[#bde2f9] font-black transition-all text-center shadow-2xs cursor-pointer"
            >
                🛡️ Admin EO
            </button>
            <button 
                type="button" 
                @click="setDemoCredentials('superadmin')" 
                class="py-2.5 px-2 rounded-full bg-white hover:bg-[#e8f5fd] text-[#1d9bf0] border border-[#bde2f9] font-black transition-all text-center shadow-2xs cursor-pointer"
            >
                👑 Superadmin
            </button>
        </div>
    </div>

    <form @submit.prevent="handleLogin()" class="space-y-4">
        <!-- Email Input -->
        <div>
            <label for="email" class="block text-xs font-bold text-[#0f1419] mb-1.5">Alamat Email / Akun</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#536471]">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.206"></path></svg>
                </div>
                <input 
                    id="email" 
                    type="email" 
                    x-model="email" 
                    required 
                    class="w-full pl-10 pr-4 py-2.5 bg-[#f7f9f9] border border-[#eff3f4] rounded-xl text-sm text-[#0f1419] placeholder-[#536471] focus:outline-none focus:ring-2 focus:ring-[#1d9bf0] focus:bg-white transition-all font-semibold"
                    placeholder="nama@email.com"
                >
            </div>
        </div>

        <!-- Password Input -->
        <div>
            <div class="flex items-center justify-between mb-1.5">
                <label for="password" class="block text-xs font-bold text-[#0f1419]">Kata Sandi</label>
                <a href="#" @click.prevent="$store.app.notify('info', 'Lupa Sandi', 'Silakan hubungi panitia EO di tenda informasi.')" class="text-xs text-[#1d9bf0] hover:underline font-bold transition-colors">Lupa sandi?</a>
            </div>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#536471]">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                </div>
                <input 
                    id="password" 
                    type="password" 
                    x-model="password" 
                    required 
                    class="w-full pl-10 pr-4 py-2.5 bg-[#f7f9f9] border border-[#eff3f4] rounded-xl text-sm text-[#0f1419] placeholder-[#536471] focus:outline-none focus:ring-2 focus:ring-[#1d9bf0] focus:bg-white transition-all font-semibold"
                    placeholder="••••••••"
                >
            </div>
        </div>

        <!-- Remember Me Checkbox -->
        <div class="flex items-center justify-between pt-1">
            <label class="flex items-center gap-2 cursor-pointer">
                <input 
                    type="checkbox" 
                    x-model="remember" 
                    class="w-4 h-4 rounded border-slate-300 text-[#1d9bf0] focus:ring-[#1d9bf0]"
                >
                <span class="text-xs text-[#0f1419] font-medium">Ingat sesi saya di perangkat ini</span>
            </label>
        </div>

        <!-- Submit Button (Twitter Style Pill Button) -->
        <button 
            type="submit" 
            :disabled="isLoading"
            class="w-full mt-2 py-3.5 px-4 rounded-full bg-[#1d9bf0] hover:bg-[#1a8cd8] text-white text-sm font-black shadow-md shadow-[#1d9bf0]/25 transition-all flex items-center justify-center gap-2 disabled:opacity-50 active:scale-[0.98] cursor-pointer"
        >
            <template x-if="isLoading">
                <svg class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
            </template>
            <span x-text="isLoading ? 'Memverifikasi...' : 'Masuk Sekarang'"></span>
        </button>
    </form>

    <!-- Register Link -->
    <div class="mt-6 pt-5 border-t border-[#eff3f4] text-center">
        <p class="text-xs text-[#0f1419] font-medium">
            Belum mendaftar sebagai tenant warung?
            <a href="/register" class="font-black text-[#1d9bf0] hover:underline ml-1 transition-colors">
                Daftar Warung Baru &rarr;
            </a>
        </p>
    </div>
</div>
@endsection
