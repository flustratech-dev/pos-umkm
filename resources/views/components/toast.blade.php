<!-- Reusable Toast Notification Component -->
<div 
    class="fixed top-4 right-4 z-50 flex flex-col gap-2 max-w-sm w-full pointer-events-none"
    aria-live="polite"
>
    <template x-for="toast in $store.app.toasts" :key="toast.id">
        <div 
            x-transition:enter="transform ease-out duration-300 transition"
            x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-4"
            x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="pointer-events-auto w-full bg-white rounded-xl shadow-xl border p-4 flex items-start gap-3 relative overflow-hidden"
            :class="{
                'border-emerald-200 bg-emerald-50/50 text-emerald-900': toast.type === 'success',
                'border-rose-200 bg-rose-50/50 text-rose-900': toast.type === 'error',
                'border-amber-200 bg-amber-50/50 text-amber-900': toast.type === 'warning',
                'border-blue-200 bg-blue-50/50 text-blue-900': toast.type === 'info'
            }"
        >
            <!-- Toast Icon -->
            <div class="shrink-0 mt-0.5">
                <template x-if="toast.type === 'success'">
                    <div class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                    </div>
                </template>
                <template x-if="toast.type === 'error'">
                    <div class="w-8 h-8 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </div>
                </template>
                <template x-if="toast.type === 'warning'">
                    <div class="w-8 h-8 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    </div>
                </template>
                <template x-if="toast.type === 'info'">
                    <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                </template>
            </div>

            <!-- Toast Content -->
            <div class="flex-1 min-w-0 pr-4">
                <h4 class="text-sm font-semibold leading-snug" x-text="toast.title"></h4>
                <p class="text-xs mt-0.5 leading-relaxed opacity-90" x-text="toast.message"></p>
            </div>

            <!-- Close Button -->
            <button 
                @click="$store.app.removeToast(toast.id)" 
                class="shrink-0 text-gray-400 hover:text-gray-600 p-1 rounded-lg transition-colors"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
    </template>
</div>
