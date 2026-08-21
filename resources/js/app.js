import './bootstrap';
import Alpine from 'alpinejs';
import Swal from 'sweetalert2';
import 'sweetalert2/dist/sweetalert2.min.css';
import { evaluatePasswordStrength } from './password-meter';
import QRCode from 'qrcode';
window.QRCode = QRCode;

// Lazy loader for Chart.js (drastically cuts initial bundle size)
window.loadChartJs = async function() {
    if (window.Chart) return window.Chart;
    if (window.__chartPromise) return window.__chartPromise;
    window.__chartPromise = import('chart.js/auto').then(m => {
        window.Chart = m.default || m;
        return window.Chart;
    });
    return window.__chartPromise;
};

// Helper for making API calls with CSRF
const httpErrorMessage = (status) => {
    switch (status) {
        case 413:
            return 'File yang dikirim terlalu besar untuk server. Ulangi dengan foto yang lebih kecil.';
        case 419:
            return 'Sesi kedaluwarsa atau file terlalu besar sehingga data tidak terkirim utuh. Muat ulang halaman lalu coba lagi.';
        case 401:
            return 'Sesi Anda sudah berakhir. Silakan masuk kembali.';
        case 403:
            return 'Akses ditolak untuk tindakan ini.';
        case 404:
            return 'Alamat tujuan tidak ditemukan di server.';
        case 500:
        case 502:
        case 503:
            return 'Server sedang bermasalah (kode ' + status + '). Coba lagi sebentar lagi.';
        default:
            return 'Terjadi kesalahan pada server (kode ' + status + ').';
    }
};

const apiFetch = async (url, options = {}) => {
    const skipLoading = options.skipLoading || false;
    const loadingText = options.loadingText || 'Memproses...';


    const timeoutMs = options.timeout || 30000; // 30 detik default timeout

    if (!skipLoading && typeof window.showLoading === 'function') {
        window.showLoading(loadingText);
    }

    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const headers = {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        ...(options.headers || {})
    };
    
    if (token) {
        headers['X-CSRF-TOKEN'] = token;
    }

    if (!(options.body instanceof FormData)) {
        headers['Content-Type'] = 'application/json';
        if (options.body && typeof options.body === 'object') {
            options.body = JSON.stringify(options.body);
        }
    }

    // Timeout controller agar request tidak hang selamanya di koneksi lambat
    const controller = new AbortController();
    const timeoutId = setTimeout(() => controller.abort(), timeoutMs);

    try {
        const res = await fetch(url, { ...options, headers, signal: controller.signal });
        clearTimeout(timeoutId);

        // Handle HTTP 413: file terlalu besar (Nginx/PHP limit)
        if (res.status === 413) {
            throw new Error('Ukuran file terlalu besar. Coba foto dengan resolusi lebih rendah atau gunakan screenshot.');
        }

        // Respons non-JSON (halaman error 419/500 dari server web) tidak boleh
        // berakhir jadi pesan generik tanpa petunjuk buat kasir.
        const data = await res.json().catch(() => ({ success: false, message: null }));
        
        if (!res.ok) {
            throw new Error(data.message || httpErrorMessage(res.status));
        }
        
        return data;
    } catch (err) {
        clearTimeout(timeoutId);
        // Network error atau timeout
        if (err.name === 'AbortError') {
            throw new Error('Koneksi timeout. Periksa jaringan internet Anda dan coba lagi.');
        }
        if (err instanceof TypeError && err.message === 'Failed to fetch') {
            throw new Error('Koneksi terputus. Pastikan Anda terhubung ke internet dan coba lagi.');
        }
        throw err;
    } finally {
        if (!skipLoading && typeof window.hideLoading === 'function') {
            window.hideLoading();
        }
    }
};

window.Alpine = Alpine;
window.Swal = Swal;
window.evaluatePasswordStrength = evaluatePasswordStrength;

// Modern custom SweetAlert helper functions matching Twitter/JADISATU design theme:
const swalCustomClass = {
    popup: 'rounded-3xl shadow-2xl border border-[#eff3f4] font-sans p-6 text-center',
    title: 'text-lg sm:text-xl font-black text-[#0f1419] tracking-tight',
    htmlContainer: 'text-xs sm:text-sm text-[#536471] font-medium leading-relaxed mt-2',
    confirmButton: 'px-6 py-2.5 rounded-full bg-[#1d9bf0] hover:bg-[#1a8cd8] text-white text-xs sm:text-sm font-black shadow-md shadow-[#1d9bf0]/25 transition-all cursor-pointer mx-1.5 active:scale-95',
    cancelButton: 'px-6 py-2.5 rounded-full bg-[#eff3f4] hover:bg-slate-200 text-[#0f1419] text-xs sm:text-sm font-black transition-all cursor-pointer mx-1.5 active:scale-95'
};

export const showSwal = (type = 'success', title = '', text = '', timer = null) => {
    let icon = type;
    if (type === 'danger') icon = 'error';
    if (type === 'warn') icon = 'warning';

    const defaultTimer = type === 'success' ? 2200 : (type === 'info' ? 3000 : undefined);
    const finalTimer = timer !== null ? timer : defaultTimer;

    return Swal.fire({
        icon,
        title: title || (type === 'success' ? 'Berhasil' : (type === 'error' ? 'Perhatian' : 'Pemberitahuan')),
        text: text || '',
        buttonsStyling: false,
        customClass: swalCustomClass,
        timer: finalTimer,
        timerProgressBar: !!finalTimer,
        confirmButtonText: 'Oke, Mengerti'
    });
};

export const showConfirm = async (title = 'Konfirmasi', text = '', confirmText = 'Ya, Lanjutkan', cancelText = 'Batal') => {
    const result = await Swal.fire({
        title,
        text,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: confirmText,
        cancelButtonText: cancelText,
        buttonsStyling: false,
        customClass: swalCustomClass,
        reverseButtons: true
    });
    return result.isConfirmed;
};

window.showSwal = showSwal;
window.showConfirm = showConfirm;
window.apiFetch = apiFetch;

// Utility formatters
export const formatRupiah = (number) => {
    if (number === null || number === undefined || isNaN(number)) return 'Rp 0';
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(number);
};

export const compressImage = (file, maxWidth = 600, maxHeight = 600, quality = 0.8) => {
    return new Promise((resolve, reject) => {
        if (!file || !file.type.startsWith('image/')) {
            return reject(new Error('File is not an image'));
        }
        const reader = new FileReader();
        reader.readAsDataURL(file);
        reader.onload = (event) => {
            const img = new Image();
            img.src = event.target.result;
            img.onload = () => {
                const canvas = document.createElement('canvas');
                let width = img.width;
                let height = img.height;

                if (width > height) {
                    if (width > maxWidth) {
                        height = Math.round((height *= maxWidth / width));
                        width = maxWidth;
                    }
                } else {
                    if (height > maxHeight) {
                        width = Math.round((width *= maxHeight / height));
                        height = maxHeight;
                    }
                }

                canvas.width = width;
                canvas.height = height;
                const ctx = canvas.getContext('2d');
                ctx.drawImage(img, 0, 0, width, height);

                // High-performance WebP compression (80% smaller bandwidth, 0ms render)
                const mimeType = 'image/webp';
                const outputName = file.name.replace(/\.[^/.]+$/, "") + '.webp';

                canvas.toBlob((blob) => {
                    if (!blob) {
                        canvas.toBlob((fallbackBlob) => {
                            resolve({
                                file: new File([fallbackBlob], file.name, { type: 'image/jpeg', lastModified: Date.now() }),
                                previewUrl: canvas.toDataURL('image/jpeg', quality)
                            });
                        }, 'image/jpeg', quality);
                        return;
                    }

                    resolve({
                        file: new File([blob], outputName, {
                            type: mimeType,
                            lastModified: Date.now()
                        }),
                        previewUrl: canvas.toDataURL(mimeType, quality)
                    });
                }, mimeType, quality);
            };
            img.onerror = (e) => reject(e);
        };
        reader.onerror = (e) => reject(e);
    });
};

// Automatic Background Image Preloader for 0ms Instant Card Rendering
export const preloadImage = (url) => {
    if (!url || typeof url !== 'string') return;
    const img = new Image();
    img.decoding = 'async';
    img.src = url;
};

export const preloadProductImages = (products) => {
    if (!Array.isArray(products)) return;
    products.forEach(p => {
        if (p && p.photo) {
            const photoUrl = (p.photo.startsWith('http') || p.photo.startsWith('/')) ? p.photo : ('/storage/' + p.photo);
            preloadImage(photoUrl);
        }
    });
};

window.preloadImage = preloadImage;
window.preloadProductImages = preloadProductImages;

export const formatNumber = (number) => {
    if (number === null || number === undefined || number === '') return '';
    let parsed;
    if (typeof number === 'number') {
        parsed = number;
    } else if (typeof number === 'string') {
        const trimmed = number.trim();
        // If string is pure decimal representation from database (e.g. "10000.00" or "10000")
        if (/^\d+(\.\d+)?$/.test(trimmed)) {
            parsed = Math.round(parseFloat(trimmed));
        } else {
            // Otherwise it's a formatted string from user input (e.g. "10.000"), strip non-digits
            parsed = parseFloat(trimmed.replace(/\D/g, ''));
        }
    }
    if (isNaN(parsed) || parsed === undefined) return '';
    return new Intl.NumberFormat('id-ID').format(parsed);
};

// Seluruh tampilan waktu memakai WIB, tidak ikut zona waktu perangkat kasir.
export const WIB = 'Asia/Jakarta';

export const formatDateTime = (dateStr) => {
    if (!dateStr) return '-';
    try {
        const d = new Date(dateStr);
        if (isNaN(d.getTime())) return dateStr;
        return d.toLocaleDateString('id-ID', {
            day: 'numeric',
            month: 'short',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            timeZone: WIB
        });
    } catch {
        return dateStr;
    }
};

window.formatRupiah = formatRupiah;
window.formatNumber = formatNumber;
window.formatDateTime = formatDateTime;


// Purge stale demo data from localStorage on load
try {
    ['events', 'stores', 'products', 'transactions', 'helpdesk', 'role'].forEach(k => {
        localStorage.removeItem(`pos_umkm_${k}`);
        localStorage.removeItem(k);
    });
} catch (e) {}

// Global Store setup in Alpine
Alpine.store('app', {
        // Authenticated Session State (Strictly Server Driven)
        currentUser: window.__AUTH_USER__ || null,
        currentRole: window.__AUTH_USER__?.role || 'user',
        sidebarOpen: false,
        user: window.__AUTH_USER__ || null,
        userStores: window.__USER_STORES__ || [],
        activeEvent: window.__ACTIVE_EVENT__ || null,
        events: window.__INITIAL_EVENTS__ || [],
        stores: window.__INITIAL_STORES__ || [],
        products: window.__INITIAL_PRODUCTS__ || [],
        transactions: window.__INITIAL_TRANSACTIONS__ || [],
        helpdeskTickets: window.__INITIAL_TICKETS__ || [],

        get activeStoreEventActive() {
            if (!this.user || this.user.role !== 'user' || !this.user.store_id) return true;
            const userStore = this.userStores.find(s => Number(s.id) === Number(this.user.store_id));
            return userStore ? Boolean(userStore.event_is_active) : false;
        },

        get stats() {
            const pendingCashCount = this.transactions.filter(t => t.status === 'pending' && t.payment_method === 'cash').length;
            return {
                pendingCashCount,
                pendingCount: pendingCashCount,
            };
        },

        // Cart state for User POS
        cart: [],
        
        // Active UI & Modals State
        isCartOpen: false,
        isCheckoutOpen: false,
        activePaymentTab: 'cash', // 'cash' or 'qris'
        cashAmountPaid: '',
        qrisProofPreview: null,
        qrisProofFile: null,
        // Terisi saat pengiriman QRIS gagal, memunculkan tombol darurat.
        qrisUploadFailed: false,
        qrisFailureReason: '',
        dynamicQrisLoading: false,
        dynamicQrisDataUrl: null,
        
        // Global Branded Circular Logo Loading State
        globalLoading: false,
        globalLoadingText: 'Memproses...',
        showLoading(text = 'Memproses...') {
            this.globalLoadingText = text;
            this.globalLoading = true;
        },
        hideLoading() {
            this.globalLoading = false;
        },

        // Active receipt modal for transaction preview
        receiptModalOpen: false,
        activeReceiptTransaction: null,

        // QRIS Verification Modal & Reject Modal for Admin
        qrisModalOpen: false,
        selectedQrisTransaction: null,
        rejectModalOpen: false,
        rejectionReason: '',

        // Cancel Paid Transaction Modal for Admin
        cancelModalOpen: false,
        transactionToCancel: null,
        cancelReasonCategory: '',
        cancelCustomNote: '',
        cancelRefundConfirmed: false,

        // Product CRUD Modal for User
        productModalOpen: false,
        isEditingProduct: false,
        productFormData: {
            id: null,
            title: '',
            price: '',
            is_negotiable: false,
            min_price: '',
            max_price: '',
            category: 'Makanan',
            description: '',
            photo: '',
            stock_badge: 'Tersedia'
        },
        deleteProductConfirmOpen: false,
        productToDelete: null,

        // Event Management Modal for Super Admin
        eventModalOpen: false,
        isEditingEvent: false,
        eventFormData: {
            name: '',
            slug: '',
            start_date: '',
            end_date: '',
            location: ''
        },
        activateEventConfirmOpen: false,
        eventToActivate: null,

        // Helpdesk New Ticket Modal
        ticketModalOpen: false,
        ticketFormData: {
            category: 'Kasir & Pembayaran',
            subject: '',
            message: ''
        },
        selectedTicket: null,
        ticketReplyText: '',

        // Notification Toasts
        toasts: [],

        init() {
            if (window.__AUTH_USER__) {
                this.currentUser = window.__AUTH_USER__;
                this.currentRole = window.__AUTH_USER__.role;
            }
            if (window.__ACTIVE_EVENT__) {
                this.activeEvent = window.__ACTIVE_EVENT__;
            }
            if (window.__INITIAL_EVENTS__) this.events = window.__INITIAL_EVENTS__;
            if (window.__INITIAL_STORES__) this.stores = window.__INITIAL_STORES__;
            if (window.__INITIAL_PRODUCTS__) {
                this.products = window.__INITIAL_PRODUCTS__.map(p => ({
                    ...p,
                    photo: this.getProductPhoto(p.photo)
                }));
            }
            if (window.__INITIAL_TRANSACTIONS__) this.transactions = window.__INITIAL_TRANSACTIONS__;
            if (window.__INITIAL_HELPDESK__) this.helpdesk = window.__INITIAL_HELPDESK__;

            if (window.__FLASH_SUCCESS__) {
                this.notify('success', 'Berhasil', window.__FLASH_SUCCESS__);
            }
            if (window.__FLASH_ERROR__) {
                this.notify('error', 'Perhatian', window.__FLASH_ERROR__);
            }
        },

        formatRupiah(n) {
            return formatRupiah(n);
        },

        formatDateTime(d) {
            return formatDateTime(d);
        },

        formatNumber(n) {
            return formatNumber(n);
        },

        getProductPhoto(photo) {
            if (!photo) return 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=400&q=80';
            if (photo.startsWith('http://') || photo.startsWith('https://') || photo.startsWith('data:')) {
                return photo;
            }
            if (photo.startsWith('/')) {
                return photo;
            }
            return '/storage/' + photo;
        },

        getRoleLabel(role) {
            if (role === 'user') return 'Pemilik Warung (User)';
            if (role === 'admin') return 'Admin EO';
            if (role === 'superadmin') return 'Developer Platform';
            return role;
        },

        /**
         * Normalisasi format transaksi dari API response (Eloquent) ke format flat
         * yang sama dengan layout blade mapping, agar tampilan laporan konsisten.
         *
         * API response mengembalikan nested relations (store, cashier, payment_proof, revenue_split)
         * dan string decimal values, sedangkan views mengharapkan flat fields seperti
         * store_name, cashier_name, proof_image (URL), dan numeric total_amount.
         */
        normalizeTransaction(tx) {
            if (!tx) return tx;

            // Kalau sudah punya store_name (format layout), kembalikan apa adanya
            if (tx.store_name !== undefined && tx.cashier_name !== undefined) {
                return tx;
            }

            const normalized = { ...tx };

            // Flatten store → store_name
            if (tx.store && typeof tx.store === 'object') {
                normalized.store_name = tx.store.name || '';
                normalized.store_id = tx.store_id || tx.store.id;
            }
            if (!normalized.store_name) {
                normalized.store_name = tx.store_name || '';
            }

            // Flatten cashier → cashier_name
            if (tx.cashier && typeof tx.cashier === 'object') {
                normalized.cashier_name = tx.cashier.name || '';
                normalized.cashier_id = tx.cashier_id || tx.cashier.id;
            }
            if (!normalized.cashier_name) {
                normalized.cashier_name = tx.cashier_name || '';
            }

            // Flatten payment_proof relation → proof_image & payment_proof (URL string)
            if (tx.payment_proof && typeof tx.payment_proof === 'object') {
                const proofUrl = tx.payment_proof.proof_url
                    || (tx.payment_proof.proof_path ? '/storage/' + tx.payment_proof.proof_path : null);
                normalized.payment_proof = proofUrl;
                normalized.proof_image = proofUrl;
            } else if (typeof tx.payment_proof === 'string') {
                normalized.proof_image = normalized.proof_image || tx.payment_proof;
            }

            // Normalize revenue_split (Eloquent uses snake_case keys in JSON)
            if (tx.revenue_split && typeof tx.revenue_split === 'object') {
                normalized.revenue_split = {
                    owner_share: parseFloat(tx.revenue_split.owner_share) || 0,
                    admin_gross_share: parseFloat(tx.revenue_split.admin_gross_share) || 0,
                    superadmin_share: parseFloat(tx.revenue_split.superadmin_share) || 0,
                    admin_net_share: parseFloat(tx.revenue_split.admin_net_share) || 0,
                };
            }

            // Convert string decimals to numbers
            normalized.total_amount = parseFloat(tx.total_amount) || 0;
            normalized.amount_paid = tx.amount_paid != null ? parseFloat(tx.amount_paid) : null;
            normalized.change_due = tx.change_due != null ? parseFloat(tx.change_due) : null;

            // Normalize items prices
            if (Array.isArray(tx.items)) {
                normalized.items = tx.items.map(item => ({
                    ...item,
                    price: parseFloat(item.price) || 0,
                    original_price: item.original_price != null ? parseFloat(item.original_price) : null,
                    qty: parseInt(item.qty) || 0,
                    subtotal: parseFloat(item.subtotal) || 0,
                }));
            }

            // Preserve date formats
            normalized.paid_at = tx.paid_at || null;
            normalized.created_at = tx.created_at || null;

            return normalized;
        },

        getCurrentUser() {
            return this.currentUser || window.__AUTH_USER__ || { name: 'User', email: '', role: this.currentRole };
        },

        getActiveEvent() {
            return this.activeEvent || window.__ACTIVE_EVENT__ || { name: 'Event Belum Aktif' };
        },

        // Kode unik nominal QRIS mengikuti kode tenda stand (mis. tenda 019 -> +19).
        storeUniqueCode(store) {
            if (!store) return 0;
            if (store.unique_code !== null && store.unique_code !== undefined) {
                return parseInt(store.unique_code, 10) || 0;
            }
            const digits = String(store.booth_number ?? '').replace(/\D/g, '');
            return digits ? (parseInt(digits, 10) || 0) : (parseInt(store.id, 10) || 0);
        },

        getCurrentStore() {
            const user = this.getCurrentUser();
            if (user && (user.store_id || user.store_name)) {
                const foundStore = this.stores.find(s => s.id == user.store_id) || this.userStores.find(s => s.id == user.store_id);
                if (foundStore) return foundStore;
                
                return {
                    id: user.store_id,
                    name: user.store_name || 'Stand Saya',
                    booth_number: user.booth_number || '-'
                };
            }
            return this.stores[0] || null;
        },

        // SweetAlert notifications
        notify(type = 'success', title = 'Pemberitahuan', message = '') {
            showSwal(type, title, message);
        },

        removeToast(id) {
            // Deprecated
        },

        // CART MANAGEMENT (for User POS)
        _refreshQrisIfActive() {
            if (this.activePaymentTab === 'qris') {
                this.generateDynamicQris();
            }
        },

        // Rentang harga yang boleh dipakai kasir. Produk harga pas terkunci di harganya.
        priceRangeOf(product) {
            const listPrice = parseFloat(product?.price) || 0;
            if (!product?.is_negotiable) {
                return { min: listPrice, max: listPrice };
            }
            const min = product.min_price !== null && product.min_price !== undefined ? parseFloat(product.min_price) : 0;
            const max = product.max_price !== null && product.max_price !== undefined ? parseFloat(product.max_price) : listPrice;
            return { min: isNaN(min) ? 0 : min, max: isNaN(max) ? listPrice : max };
        },

        cartItemPrice(item) {
            const price = parseFloat(item?.price);
            return isNaN(price) ? (parseFloat(item?.product?.price) || 0) : price;
        },

        addToCart(product) {
            const existing = this.cart.find(item => item.product.id === product.id);
            if (existing) {
                existing.qty++;
            } else {
                this.cart.push({
                    product,
                    qty: 1,
                    // Harga acuan jadi nilai awal; kasir menurunkannya saat deal nego.
                    price: this.priceRangeOf(product).max,
                    notes: ''
                });
            }
            this.notify('success', 'Produk Ditambahkan', `${product.title} (x1) masuk keranjang`);
            this._refreshQrisIfActive();
        },

        // Harga hasil nego, dikunci ulang ke rentang yang ditetapkan pemilik stand.
        // Dipanggil saat kasir selesai mengetik, bukan di tiap ketikan, supaya
        // angka yang sedang dihapus tidak langsung ditimpa.
        updateCartPrice(productId, value) {
            const item = this.cart.find(c => c.product.id === productId);
            if (!item || !item.product.is_negotiable) return;

            const { min, max } = this.priceRangeOf(item.product);
            const digits = String(value ?? '').replace(/\D/g, '');
            let price = digits === '' ? NaN : parseFloat(digits);

            if (isNaN(price)) {
                // Dikosongkan lalu ditinggal: kembali ke harga pasang.
                price = max;
            } else if (price < min) {
                price = min;
                this.notify('warning', 'Di Bawah Batas', `Harga nego ${item.product.title} minimal ${formatRupiah(min)}.`);
            } else if (price > max) {
                price = max;
                this.notify('warning', 'Di Atas Batas', `Harga nego ${item.product.title} maksimal ${formatRupiah(max)}.`);
            }

            item.price = price;
            this._refreshQrisIfActive();
        },

        updateCartQty(productId, delta) {
            const index = this.cart.findIndex(item => item.product.id === productId);
            if (index > -1) {
                this.cart[index].qty += delta;
                if (this.cart[index].qty <= 0) {
                    this.cart.splice(index, 1);
                }
                this._refreshQrisIfActive();
            }
        },

        removeFromCart(productId) {
            this.cart = this.cart.filter(item => item.product.id !== productId);
            this._refreshQrisIfActive();
        },

        clearCart() {
            this.cart = [];
            this.cashAmountPaid = '';
            this.qrisProofPreview = null;
            this.qrisProofFile = null;
            this.qrisUploadFailed = false;
            this.qrisFailureReason = '';
        },

        get cartTotal() {
            return this.cart.reduce((sum, item) => sum + (this.cartItemPrice(item) * item.qty), 0);
        },

        // Selisih dari harga acuan, dipakai sebagai info "hemat" di panel kasir.
        get cartNegotiatedDiscount() {
            return this.cart.reduce((sum, item) => {
                const listPrice = this.priceRangeOf(item.product).max;
                const diff = listPrice - this.cartItemPrice(item);
                return sum + (diff > 0 ? diff * item.qty : 0);
            }, 0);
        },

        get cartItemCount() {
            return this.cart.reduce((sum, item) => sum + item.qty, 0);
        },

        get cashChangeDue() {
            const paid = parseFloat(this.cashAmountPaid) || 0;
            const total = this.cartTotal;
            return Math.max(0, paid - total);
        },

        get isCashValid() {
            const paid = parseFloat(this.cashAmountPaid) || 0;
            return this.cart.length > 0 && paid >= this.cartTotal;
        },

        setCashPreset(amount) {
            this.cashAmountPaid = amount;
        },

        // CHECKOUT SUBMISSION
        async processCashCheckout() {
            if (!this.isCashValid) {
                this.notify('error', 'Validasi Gagal', 'Nominal uang diterima kurang dari total tagihan.');
                return;
            }

            try {
                const payload = {
                    items: this.cart.map(c => ({
                        product_id: c.product.id,
                        qty: c.qty,
                        price: this.cartItemPrice(c)
                    })),
                    amount_paid: parseFloat(this.cashAmountPaid)
                };

                const data = await apiFetch('/user/kasir/checkout-cash', {
                    method: 'POST',
                    body: payload
                });

                if (data.success && data.transaction) {
                    const normTx = this.normalizeTransaction(data.transaction);
                    this.transactions.unshift(normTx);
                    this.activeReceiptTransaction = normTx;
                    this.receiptModalOpen = true;
                    this.isCheckoutOpen = false;
                    this.clearCart();
                    this.notify('success', 'Berhasil!', 'Pesanan berhasil dibuat! Silakan arahkan pembeli ke Kasir untuk pembayaran.');
                }
            } catch (error) {
                this.notify('error', 'Gagal', error.message);
            }
        },

        async generateDynamicQris() {
            const currentStore = this.getCurrentStore();
            if (!currentStore || !currentStore.use_dynamic_qris || !window.__ACTIVE_EVENT__ || !window.__ACTIVE_EVENT__.qris_payload) {
                return;
            }

            if (this.cartTotal <= 0) {
                this.dynamicQrisDataUrl = null;
                return;
            }
            
            this.dynamicQrisLoading = true;
            try {
                const response = await apiFetch('/user/kasir/generate-qris', {
                    method: 'POST',
                    body: { amount: this.cartTotal }
                });
                const payload = response.qris_payload || response.payload;
                if (response.success && payload) {
                    this.dynamicQrisDataUrl = await window.QRCode.toDataURL(payload, {
                        width: 400,
                        margin: 2,
                        color: {
                            dark: '#0f1419',
                            light: '#ffffff'
                        }
                    });
                } else {
                    console.error('Failed to generate dynamic QRIS:', response.message);
                }
            } catch (err) {
                console.error('Error generating dynamic QRIS', err);
            } finally {
                this.dynamicQrisLoading = false;
            }
        },

        /**
         * Kompresi gambar menggunakan HTML5 Canvas.
         * Mengurangi ukuran file foto HP dari 5-15 MB → ~100-300 KB.
         * @param {File} file - File gambar asli dari input
         * @param {Object} opts - Opsi kompresi
         * @param {number} opts.maxWidth - Lebar maksimal (default: 1200px)
         * @param {number} opts.maxHeight - Tinggi maksimal (default: 1200px)
         * @param {number} opts.quality - Kualitas JPEG 0-1 (default: 0.7)
         * @returns {Promise<File>} File hasil kompresi
         */
        compressImage(file, opts = {}) {
            const maxWidth = opts.maxWidth || 1200;
            const maxHeight = opts.maxHeight || 1200;
            const quality = opts.quality || 0.7;

            return new Promise((resolve, reject) => {
                // Kalau bukan image, langsung kembalikan file asli
                if (!file.type.startsWith('image/')) {
                    resolve(file);
                    return;
                }

                // Server hanya menerima jpg/png/gif/bmp/webp. Format lain
                // (HEIC/HEIF bawaan iPhone) HARUS lewat canvas dulu untuk
                // dikonversi, sekecil apa pun ukurannya.
                const formatAman = /^image\/(jpeg|jpg|png|gif|bmp|webp)$/i.test(file.type);

                // Sudah kecil (< 500 KB) dan formatnya diterima server: lewati.
                if (formatAman && file.size <= 500 * 1024) {
                    resolve(file);
                    return;
                }

                const img = new Image();
                const canvas = document.createElement('canvas');
                const ctx = canvas.getContext('2d');

                img.onload = () => {
                    let { width, height } = img;

                    // Hitung rasio resize
                    if (width > maxWidth || height > maxHeight) {
                        const ratio = Math.min(maxWidth / width, maxHeight / height);
                        width = Math.round(width * ratio);
                        height = Math.round(height * ratio);
                    }

                    canvas.width = width;
                    canvas.height = height;
                    ctx.drawImage(img, 0, 0, width, height);

                    canvas.toBlob(
                        (blob) => {
                            if (!blob) {
                                resolve(file); // Fallback ke file asli jika gagal
                                return;
                            }
                            const compressedFile = new File(
                                [blob],
                                file.name.replace(/\.[^.]+$/, '.jpg'),
                                { type: 'image/jpeg', lastModified: Date.now() }
                            );
                            console.log(`[Compress] ${(file.size / 1024 / 1024).toFixed(2)} MB → ${(compressedFile.size / 1024).toFixed(0)} KB`);
                            resolve(compressedFile);
                        },
                        'image/jpeg',
                        quality
                    );
                };

                img.onerror = () => {
                    console.warn('[Compress] Gagal load image, gunakan file asli');
                    resolve(file); // Fallback ke file asli
                };

                img.src = URL.createObjectURL(file);
            });
        },

        async handleQrisProofUpload(event) {
            const file = event.target.files[0];
            if (!file) return;

            try {
                // Tampilkan preview segera dari file asli agar UX tetap responsif
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.qrisProofPreview = e.target.result;
                };
                reader.readAsDataURL(file);

                // Kompresi di background — file yang di-upload ke server adalah hasil compress
                this.qrisProofFile = await this.compressImage(file);

                // Masih di atas 2 MB (batas upload_max_filesize paling umum di
                // server): tekan sekali lagi supaya tidak ditolak 413/419.
                if (this.qrisProofFile.size > 2 * 1024 * 1024) {
                    this.qrisProofFile = await this.compressImage(file, { maxWidth: 900, maxHeight: 900, quality: 0.6 });
                }

                // Beri tahu kasir sedini mungkin kalau ukurannya masih rawan
                // ditolak server, jangan tunggu sampai gagal saat menyimpan.
                if (this.qrisProofFile.size > 2 * 1024 * 1024) {
                    const ukuranMB = (this.qrisProofFile.size / 1024 / 1024).toFixed(1);
                    this.notify('warning', 'Gambar Melebihi Kapasitas', `Setelah dikompres ukurannya masih ${ukuranMB} MB. Coba pakai screenshot bukti transfer, bukan foto layar.`, 6000);
                }
            } catch (err) {
                console.error('[Upload] Error processing image:', err);
                // Fallback: pakai file asli tanpa kompresi
                this.qrisProofFile = file;
                const ukuranMB = (file.size / 1024 / 1024).toFixed(1);
                this.notify('warning', 'Foto Tidak Bisa Dikompres', `Format foto ini tidak dikenali browser, jadi dikirim apa adanya (${ukuranMB} MB) dan bisa ditolak server. Screenshot bukti transfer lebih aman.`, 6000);
            }
        },

        removeQrisProof() {
            this.qrisProofFile = null;
            this.qrisProofPreview = null;
            this.qrisUploadFailed = false;
            this.qrisFailureReason = '';
            const camInput = document.getElementById('qris_proof_camera');
            if (camInput) camInput.value = '';
            const galInput = document.getElementById('qris_proof_gallery');
            if (galInput) galInput.value = '';
        },

        async processQrisCheckout() {
            // Bukti transfer wajib: transaksi QRIS langsung tercatat lunas.
            if (!this.qrisProofFile) {
                this.notify('error', 'Bukti Belum Ada', 'Unggah bukti transfer QRIS terlebih dahulu sebelum menyimpan transaksi.');
                return;
            }

            try {
                const body = new FormData();
                this.cart.forEach((c, idx) => {
                    body.append(`items[${idx}][product_id]`, c.product.id);
                    body.append(`items[${idx}][qty]`, c.qty);
                    body.append(`items[${idx}][price]`, this.cartItemPrice(c));
                });
                body.append('proof_image', this.qrisProofFile);

                const data = await apiFetch('/user/kasir/checkout-qris', {
                    method: 'POST',
                    body: body,
                    // Unggahan foto di jaringan event sering lambat; 30 detik
                    // bawaan terlalu pendek dan bikin gagal padahal masih jalan.
                    timeout: 90000
                });

                if (data.success && data.transaction) {
                    const normTx = this.normalizeTransaction(data.transaction);
                    this.transactions.unshift(normTx);
                    this.activeReceiptTransaction = normTx;
                    this.receiptModalOpen = true;
                    this.isCheckoutOpen = false;
                    this.clearCart();
                    this.notify('success', 'Berhasil', data.message);
                }
            } catch (error) {
                // Pembayaran mungkin sudah masuk rekening walau buktinya gagal
                // terkirim, jadi tawarkan pencatatan darurat.
                this.qrisUploadFailed = true;
                const berkas = this.qrisProofFile;
                const detailBerkas = berkas
                    ? ` (${berkas.type || 'tipe tidak dikenal'}, ${(berkas.size / 1024 / 1024).toFixed(1)} MB)`
                    : '';
                this.qrisFailureReason = (error.message || 'Bukti transfer gagal diunggah.') + detailBerkas;
                this.notify('error', 'Gagal Mengirim Bukti', error.message);
            }
        },

        /**
         * Tombol darurat: uang QRIS sudah masuk rekening tapi buktinya gagal
         * diunggah. Transaksi tetap dicatat lunas supaya masuk laporan, tanpa
         * bukti dan tanpa perlu persetujuan admin.
         */
        async saveQrisWithoutProof() {
            const alasan = this.qrisFailureReason || 'Bukti transfer gagal diunggah.';

            const konfirmasi = await Swal.fire({
                icon: 'warning',
                title: 'Simpan Tanpa Bukti Transfer?',
                html: `Pastikan pembayaran <b>benar-benar sudah masuk</b> ke rekening QRIS.<br><br>
                       Transaksi akan dicatat <b>lunas</b> sebesar <b>${formatRupiah(this.cartTotal + (this.getCurrentStore() ? this.storeUniqueCode(this.getCurrentStore()) : 0))}</b>
                       dan langsung masuk laporan, tanpa arsip bukti transfer.`,
                showCancelButton: true,
                confirmButtonColor: '#f4212e',
                cancelButtonColor: '#eff3f4',
                confirmButtonText: 'Ya, Sudah Dibayar',
                cancelButtonText: '<span class=\'text-[#0f1419]\'>Batal</span>'
            });

            if (!konfirmasi.isConfirmed) return;

            try {
                const data = await apiFetch('/user/kasir/checkout-qris-tanpa-bukti', {
                    method: 'POST',
                    loadingText: 'Mencatat transaksi tanpa bukti...',
                    body: {
                        items: this.cart.map(c => ({
                            product_id: c.product.id,
                            qty: c.qty,
                            price: this.cartItemPrice(c)
                        })),
                        reason: alasan
                    }
                });

                if (data.success && data.transaction) {
                    const normTx = this.normalizeTransaction(data.transaction);
                    this.transactions.unshift(normTx);
                    this.activeReceiptTransaction = normTx;
                    this.receiptModalOpen = true;
                    this.isCheckoutOpen = false;
                    this.clearCart();
                    this.notify('success', 'Tercatat di Laporan', data.message);
                }
            } catch (error) {
                this.notify('error', 'Gagal', error.message);
            }
        },

        // ADMIN QRIS VERIFICATION
        openQrisVerifyModal(tx) {
            this.selectedQrisTransaction = tx;
            this.qrisModalOpen = true;
        },

        async approveQris(txId) {
            try {
                const data = await apiFetch(`/admin/verifikasi-qris/${txId}/approve`, {
                    method: 'POST'
                });
                
                if (data.success) {
                    const idx = this.transactions.findIndex(t => t.id === txId);
                    if (idx !== -1) {
                        this.transactions[idx] = this.normalizeTransaction(data.transaction);
                    }
                    this.qrisModalOpen = false;
                    this.notify('success', 'Berhasil', data.message);
                }
            } catch (error) {
                this.notify('error', 'Gagal', error.message);
            }
        },

        openRejectModal(tx) {
            this.selectedQrisTransaction = tx;
            this.rejectionReason = '';
            this.rejectModalOpen = true;
        },

        async confirmRejectQris() {
            if (!this.rejectionReason.trim()) {
                this.notify('error', 'Alasan Wajib', 'Harap masukkan alasan penolakan.');
                return;
            }
            if (this.selectedQrisTransaction) {
                try {
                    const data = await apiFetch(`/admin/verifikasi-qris/${this.selectedQrisTransaction.id}/reject`, {
                        method: 'POST',
                        body: { reason: this.rejectionReason }
                    });
                    
                    if (data.success) {
                        const idx = this.transactions.findIndex(t => t.id === this.selectedQrisTransaction.id);
                        if (idx !== -1) {
                            this.transactions[idx] = this.normalizeTransaction(data.transaction);
                        }
                        this.rejectModalOpen = false;
                        this.qrisModalOpen = false;
                        this.notify('success', 'Ditolak', data.message);
                    }
                } catch (error) {
                    this.notify('error', 'Gagal', error.message);
                }
            }
        },

        // ADMIN CANCEL PAID TRANSACTION (PRD Section 3.4 & 2.3)
        openCancelTransactionModal(tx) {
            this.transactionToCancel = tx;
            this.cancelReasonCategory = 'Salah input barang/harga';
            this.cancelCustomNote = '';
            this.cancelRefundConfirmed = false;
            this.cancelModalOpen = true;
        },

        async confirmCancelTransaction() {
            if (!this.transactionToCancel) return;
            if (!this.cancelRefundConfirmed) {
                this.notify('error', 'Konfirmasi Diperlukan', 'Harap centang checkbox konfirmasi koordinasi refund.');
                return;
            }
            if (this.cancelReasonCategory === 'Lainnya (isi manual)' && !this.cancelCustomNote.trim()) {
                this.notify('error', 'Catatan Wajib', 'Harap ketikkan detail alasan pembatalan.');
                return;
            }

            const fullReason = this.cancelReasonCategory === 'Lainnya (isi manual)' 
                ? `Lainnya: ${this.cancelCustomNote.trim()}`
                : (this.cancelCustomNote.trim() ? `${this.cancelReasonCategory} (${this.cancelCustomNote.trim()})` : this.cancelReasonCategory);

            try {
                const data = await apiFetch(`/admin/transaksi/${this.transactionToCancel.id}/cancel`, {
                    method: 'POST',
                    body: { 
                        reason_category: this.cancelReasonCategory || 'Lainnya (isi manual)',
                        custom_note: this.cancelCustomNote || '',
                        cancellation_reason: fullReason,
                        refund_ack_confirmed: this.cancelRefundConfirmed
                    }
                });
                
                if (data.success) {
                    const idx = this.transactions.findIndex(t => t.id === this.transactionToCancel.id);
                    if (idx !== -1) {
                        this.transactions[idx] = this.normalizeTransaction ? this.normalizeTransaction(data.transaction) : data.transaction;
                    }
                    this.cancelModalOpen = false;
                    this.notify('warning', 'Transaksi Dibatalkan', data.message);
                }
            } catch (error) {
                this.notify('error', 'Gagal', error.message);
            }
        },

        // PRODUCT MANAGEMENT (User)
        openAddProductModal() {
            this.isEditingProduct = false;
            this.productFormData = {
                id: null,
                title: '',
                price: '',
                is_negotiable: false,
                min_price: '',
                max_price: '',
                category: 'Makanan',
                description: '',
                photo: '',
                photoFile: null,
                photoPreview: '',
                stock_badge: 'Tersedia',
                store_id: ''
            };
            this.productModalOpen = true;
        },

        openEditProductModal(product) {
            this.isEditingProduct = true;
            const cleanPrice = product.price !== null && product.price !== undefined ? Math.round(parseFloat(product.price)) : '';
            this.productFormData = {
                id: product.id,
                title: product.title,
                price: isNaN(cleanPrice) ? '' : cleanPrice,
                is_negotiable: !!product.is_negotiable,
                min_price: product.min_price !== null && product.min_price !== undefined ? Math.round(parseFloat(product.min_price)) : '',
                max_price: product.max_price !== null && product.max_price !== undefined ? Math.round(parseFloat(product.max_price)) : '',
                category: product.category || 'Makanan',
                description: product.description || '',
                photo: product.photo || '',
                photoFile: null,
                photoPreview: product.photo_url || product.photo || '',
                stock_badge: product.stock_badge || 'Tersedia',
                store_id: product.store_id || ''
            };
            this.productModalOpen = true;
        },

        async handleProductPhotoUpload(event) {
            const file = event.target.files[0];
            if (!file) return;

            try {
                this.notify('info', 'Memproses Foto', 'Sedang mengompres gambar...', 2000);
                const compressed = await compressImage(file);
                this.productFormData.photoFile = compressed.file;
                this.productFormData.photoPreview = compressed.previewUrl;
            } catch (error) {
                this.notify('error', 'Gagal', 'Gagal memproses gambar: ' + error.message);
            }
        },

        async saveProduct() {
            if (!this.productFormData.title.trim()) {
                this.notify('error', 'Validasi Form', 'Judul produk wajib diisi.');
                if (typeof window.hideLoading === 'function') window.hideLoading();
                return;
            }
            if (this.productFormData.is_negotiable) {
                const min = parseFloat(this.productFormData.min_price);
                const max = parseFloat(this.productFormData.max_price);
                if (isNaN(min) || isNaN(max)) {
                    this.notify('error', 'Validasi Form', 'Harga terendah dan tertinggi wajib diisi untuk produk yang bisa ditawar.');
                    if (typeof window.hideLoading === 'function') window.hideLoading();
                    return;
                }
                if (max < min) {
                    this.notify('error', 'Validasi Form', 'Harga tertinggi tidak boleh lebih kecil dari harga terendah.');
                    if (typeof window.hideLoading === 'function') window.hideLoading();
                    return;
                }
            } else if (!this.productFormData.price) {
                this.notify('error', 'Validasi Form', 'Harga produk wajib diisi.');
                if (typeof window.hideLoading === 'function') window.hideLoading();
                return;
            }
            if (!this.productFormData.store_id) {
                this.notify('error', 'Validasi Form', 'Pilih warung/tenant terlebih dahulu.');
                if (typeof window.hideLoading === 'function') window.hideLoading();
                return;
            }

            try {
                const basePath = window.location.pathname.startsWith('/admin') ? '/admin' : '/user';
                const url = this.isEditingProduct ? `${basePath}/produk/${this.productFormData.id}` : `${basePath}/produk`;
                
                let payload;
                if (this.productFormData.photoFile) {
                    payload = new FormData();
                    payload.append('title', this.productFormData.title.trim());
                    payload.append('price', this.productFormData.price || '');
                    payload.append('is_negotiable', this.productFormData.is_negotiable ? '1' : '0');
                    payload.append('min_price', this.productFormData.is_negotiable ? this.productFormData.min_price : '');
                    payload.append('max_price', this.productFormData.is_negotiable ? this.productFormData.max_price : '');
                    payload.append('category', this.productFormData.category);
                    payload.append('description', this.productFormData.description || '');
                    payload.append('stock_badge', this.productFormData.stock_badge);
                    payload.append('store_id', this.productFormData.store_id);
                    payload.append('photo', this.productFormData.photoFile);
                    if (this.isEditingProduct) {
                        payload.append('_method', 'PUT'); // Laravel form spoofing
                    }
                } else {
                    payload = {
                        title: this.productFormData.title.trim(),
                        price: this.productFormData.price !== '' ? parseFloat(this.productFormData.price) : null,
                        is_negotiable: !!this.productFormData.is_negotiable,
                        min_price: this.productFormData.is_negotiable ? parseFloat(this.productFormData.min_price) : null,
                        max_price: this.productFormData.is_negotiable ? parseFloat(this.productFormData.max_price) : null,
                        category: this.productFormData.category,
                        description: this.productFormData.description,
                        stock_badge: this.productFormData.stock_badge,
                        store_id: this.productFormData.store_id
                    };
                }

                // If using FormData and PUT method, we must send POST to Laravel and spoof it via _method inside the FormData body.
                // Wait, our apiFetch doesn't automatically convert method to POST if using PUT with FormData.
                // Let's do it here just in case.
                const method = (this.isEditingProduct && this.productFormData.photoFile) ? 'POST' : (this.isEditingProduct ? 'PUT' : 'POST');

                const data = await apiFetch(url, {
                    method: method,
                    body: payload
                });

                if (data.success && data.product) {
                    if (this.isEditingProduct) {
                        const index = this.products.findIndex(p => p.id === this.productFormData.id);
                        if (index > -1) {
                            this.products[index] = data.product;
                        }
                    } else {
                        this.products.unshift(data.product);
                    }
                    this.productModalOpen = false;
                    this.notify('success', 'Berhasil', data.message);
                }
            } catch (error) {
                this.notify('error', 'Gagal', error.message);
            }
        },

        openDeleteProductModal(product) {
            this.productToDelete = product;
            this.deleteProductConfirmOpen = true;
        },

        async confirmDeleteProduct() {
            if (!this.productToDelete) return;
            
            try {
                const basePath = window.location.pathname.startsWith('/admin') ? '/admin' : '/user';
                const data = await apiFetch(`${basePath}/produk/${this.productToDelete.id}`, {
                    method: 'DELETE'
                });
                
                if (data.success) {
                    this.products = this.products.filter(p => p.id !== this.productToDelete.id);
                    this.deleteProductConfirmOpen = false;
                    this.notify('warning', 'Terhapus', data.message);
                    this.productToDelete = null;
                }
            } catch (error) {
                this.notify('error', 'Gagal', error.message);
            }
        },

        // EVENT MANAGEMENT (Super Admin Multi-Event)
        openCreateEventModal() {
            this.isEditingEvent = false;
            this.eventFormData = {
                id: null,
                name: '',
                slug: '',
                start_date: '',
                end_date: '',
                location: '',
                qris_payload: ''
            };
            this.eventModalOpen = true;
        },

        openEditEventModal(ev) {
            this.isEditingEvent = true;
            this.eventFormData = {
                id: ev.id,
                name: ev.name,
                slug: ev.slug,
                start_date: ev.start_date ? String(ev.start_date).substring(0, 10) : '',
                end_date: ev.end_date ? String(ev.end_date).substring(0, 10) : '',
                location: ev.location || '',
                qris_image_url: ev.qris_image_url || null,
                qris_payload: ev.qris_payload || ''
            };
            this.eventModalOpen = true;
        },

        openActivateEventModal(ev) {
            this.eventToActivate = ev;
            this.activateEventConfirmOpen = true;
        },

        async confirmActivateEvent() {
            if (!this.eventToActivate) return;

            const target = this.eventToActivate;
            const rolePrefix = this.currentRole === 'superadmin' ? 'superadmin' : 'admin';
            this.activateEventConfirmOpen = false;

            try {
                const res = await apiFetch(`/${rolePrefix}/events/${target.id}/activate`, {
                    method: 'POST',
                    loadingText: 'Mengaktifkan event...'
                });

                if (res.success) {
                    this.events.forEach(e => {
                        e.is_active = (e.id === target.id);
                    });
                    if (res.event) {
                        this.activeEvent = res.event;
                    }
                    this.notify('success', 'Event Diaktifkan', res.message);
                } else {
                    this.notify('error', 'Gagal', res.message || 'Gagal mengaktifkan event.');
                }
            } catch (err) {
                this.notify('error', 'Gagal', err.message || 'Gagal mengaktifkan event.');
            } finally {
                this.eventToActivate = null;
            }
        },

        // HELPDESK
        openNewTicketModal() {
            this.ticketFormData = {
                category: 'Kasir & Pembayaran',
                subject: '',
                message: ''
            };
            this.ticketModalOpen = true;
        },

        async saveNewTicket() {
            if (!this.ticketFormData.subject.trim() || !this.ticketFormData.message.trim()) {
                this.notify('error', 'Form Tidak Lengkap', 'Subjek dan rincian kendala wajib diisi.');
                return;
            }

            try {
                const data = await apiFetch('/user/helpdesk', {
                    method: 'POST',
                    body: {
                        category: this.ticketFormData.category,
                        subject: this.ticketFormData.subject.trim(),
                        message: this.ticketFormData.message.trim()
                    }
                });

                if (data.success && data.ticket) {
                    this.helpdesk.unshift(data.ticket);
                    this.ticketModalOpen = false;
                    this.notify('success', 'Tiket Terkirim', `Tiket ${data.ticket.ticket_code} berhasil dibuat.`);
                }
            } catch (error) {
                this.notify('error', 'Gagal', error.message);
            }
        },

        async sendTicketReply() {
            if (!this.selectedTicket || !this.ticketReplyText.trim()) return;
            const role = this.currentRole;
            const url = role === 'admin' || role === 'superadmin' 
                ? `/admin/helpdesk/${this.selectedTicket.id}/reply` 
                : `/user/helpdesk/${this.selectedTicket.id}/reply`;

            try {
                const data = await apiFetch(url, {
                    method: 'POST',
                    body: { message: this.ticketReplyText.trim() }
                });

                if (data.success && data.ticket) {
                    const tIndex = this.helpdesk.findIndex(x => x.id === this.selectedTicket.id);
                    if (tIndex !== -1) {
                        this.helpdesk[tIndex] = data.ticket;
                        this.selectedTicket = data.ticket; // update modal view
                    }
                    this.ticketReplyText = '';
                    this.notify('success', 'Balasan Terkirim', data.message || 'Pesan berhasil dikirim.');
                }
            } catch (error) {
                this.notify('error', 'Gagal', error.message);
            }
        },

        async changeTicketStatus(ticketId, newStatus) {
            try {
                const data = await apiFetch(`/admin/helpdesk/${ticketId}/status`, {
                    method: 'POST',
                    body: { status: newStatus }
                });
                
                if (data.success && data.ticket) {
                    const tIndex = this.helpdesk.findIndex(x => x.id === ticketId);
                    if (tIndex !== -1) {
                        this.helpdesk[tIndex] = data.ticket;
                        if (this.selectedTicket && this.selectedTicket.id === ticketId) {
                            this.selectedTicket = data.ticket;
                        }
                    }
                    this.notify('info', 'Status Tiket Diubah', data.message || `Status tiket kini: ${newStatus}`);
                }
            } catch (error) {
                this.notify('error', 'Gagal', error.message);
            }
        },

        // Thermal Receipt Print simulation
        openReceipt(tx) {
            this.activeReceiptTransaction = tx;
            this.receiptModalOpen = true;
        },

        printReceipt() {
            const tx = this.activeReceiptTransaction;
            if (!tx) {
                window.print();
                return;
            }

            const event = this.getActiveEvent();
            const store = this.getCurrentStore();

            const itemsRows = (tx.items || []).map((item, idx) => `
                <tr>
                    <td style="text-align: center; color: #64748b;">${idx + 1}</td>
                    <td style="font-weight: 600; color: #0f172a;">${item.title}</td>
                    <td style="text-align: right; color: #475569;">${item.is_negotiated ? `<s style="color:#94a3b8;">${formatRupiah(item.original_price)}</s> ` : ''}${formatRupiah(item.price)}</td>
                    <td style="text-align: center; font-weight: 700; color: #0f172a;">${item.qty}</td>
                    <td style="text-align: right; font-weight: 700; color: #0f172a;">${formatRupiah(item.subtotal)}</td>
                </tr>
            `).join('');

            const paymentSummary = tx.payment_method === 'cash' ? `
                <tr>
                    <td style="padding: 4px 0; color: #475569;">Metode Pembayaran:</td>
                    <td style="padding: 4px 0; text-align: right; font-weight: 700; color: #0f172a; text-transform: uppercase;">TUNAI / CASH</td>
                </tr>
                <tr>
                    <td style="padding: 4px 0; color: #475569;">Uang Diterima:</td>
                    <td style="padding: 4px 0; text-align: right; font-weight: 700; color: #0f172a;">${formatRupiah(tx.amount_paid)}</td>
                </tr>
                <tr>
                    <td style="padding: 4px 0; color: #047857; font-weight: 700;">Kembalian:</td>
                    <td style="padding: 4px 0; text-align: right; font-weight: 800; color: #047857; font-size: 14px;">${formatRupiah(tx.change_due)}</td>
                </tr>
            ` : `
                <tr>
                    <td style="padding: 4px 0; color: #475569;">Metode Pembayaran:</td>
                    <td style="padding: 4px 0; text-align: right; font-weight: 700; color: #1d4ed8;">QRIS RESMI EO</td>
                </tr>
                <tr>
                    <td style="padding: 4px 0; color: #475569;">Status Pembayaran:</td>
                    <td style="padding: 4px 0; text-align: right; font-weight: 700; color: #047857;">${tx.status === 'paid' ? 'LUNAS / TERVERIFIKASI' : 'MENUNGGU VERIFIKASI'}</td>
                </tr>
            `;

            const receiptHtml = `
            <!DOCTYPE html>
            <html lang="id">
            <head>
                <meta charset="utf-8">
                <title>Struk_${tx.invoice_code.replace(/[^a-zA-Z0-9]/g, '_')}</title>
                <style>
                    @page {
                        size: auto;
                        margin: 12mm 15mm;
                    }
                    * {
                        box-sizing: border-box;
                        margin: 0;
                        padding: 0;
                    }
                    body {
                        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
                        color: #1e293b;
                        background: #ffffff;
                        font-size: 12px;
                        line-height: 1.5;
                        padding: 8px;
                    }
                    .container {
                        max-width: 620px;
                        margin: 0 auto;
                        border: 1px solid #e2e8f0;
                        border-radius: 12px;
                        padding: 24px;
                        background: #ffffff;
                    }
                    .header {
                        display: flex;
                        justify-content: space-between;
                        align-items: flex-start;
                        padding-bottom: 16px;
                        border-bottom: 2px solid #059669;
                    }
                    .store-name {
                        font-size: 18px;
                        font-weight: 800;
                        color: #0f172a;
                        letter-spacing: -0.3px;
                    }
                    .event-name {
                        font-size: 13px;
                        font-weight: 700;
                        color: #059669;
                        margin-top: 2px;
                    }
                    .store-sub {
                        font-size: 11px;
                        color: #64748b;
                        margin-top: 2px;
                    }
                    .invoice-info {
                        text-align: right;
                    }
                    .transaction-code {
                        font-size: 24px;
                        font-weight: 900;
                        color: #1d4ed8;
                        text-align: center;
                        margin: 16px 0;
                        padding: 10px;
                        border: 2px dashed #1d4ed8;
                        border-radius: 12px;
                        letter-spacing: 3px;
                        background: #f0f7ff;
                    }
                    .transaction-code-label {
                        font-size: 10px;
                        font-weight: 800;
                        color: #64748b;
                        letter-spacing: 1px;
                        text-transform: uppercase;
                        display: block;
                        margin-bottom: 2px;
                    }
                    .badge-paid {
                        display: inline-block;
                        background: #ecfdf5;
                        color: #047857;
                        border: 1px solid #a7f3d0;
                        font-weight: 800;
                        font-size: 10px;
                        padding: 3px 8px;
                        border-radius: 6px;
                        text-transform: uppercase;
                        margin-bottom: 4px;
                    }
                    .invoice-code {
                        font-size: 14px;
                        font-weight: 800;
                        color: #0f172a;
                        font-family: monospace;
                    }
                    .invoice-date {
                        font-size: 11px;
                        color: #64748b;
                        margin-top: 2px;
                    }
                    .meta-bar {
                        display: flex;
                        justify-content: space-between;
                        background: #f8fafc;
                        border: 1px solid #e2e8f0;
                        border-radius: 8px;
                        padding: 10px 14px;
                        margin: 16px 0;
                        font-size: 11px;
                    }
                    .meta-item span:first-child {
                        color: #64748b;
                        margin-right: 4px;
                    }
                    .meta-item span:last-child {
                        font-weight: 700;
                        color: #0f172a;
                    }
                    table.items-table {
                        width: 100%;
                        border-collapse: collapse;
                        margin: 16px 0;
                    }
                    table.items-table th {
                        background: #f1f5f9;
                        color: #475569;
                        font-size: 10px;
                        font-weight: 800;
                        text-transform: uppercase;
                        letter-spacing: 0.5px;
                        padding: 8px 10px;
                        border-bottom: 1px solid #cbd5e1;
                    }
                    table.items-table td {
                        padding: 10px 10px;
                        border-bottom: 1px solid #f1f5f9;
                        font-size: 12px;
                    }
                    .summary-container {
                        display: flex;
                        justify-content: flex-end;
                        margin-top: 10px;
                        margin-bottom: 20px;
                    }
                    .summary-box {
                        width: 280px;
                    }
                    .summary-box table {
                        width: 100%;
                        border-collapse: collapse;
                    }
                    .total-row {
                        border-top: 2px solid #e2e8f0;
                        border-bottom: 2px solid #e2e8f0;
                    }
                    .total-row td {
                        padding: 8px 0 !important;
                        font-size: 15px !important;
                        font-weight: 900 !important;
                        color: #0f172a !important;
                    }
                    .footer {
                        border-top: 1px dashed #cbd5e1;
                        padding-top: 14px;
                        margin-top: 32px;
                        text-align: center;
                        font-size: 11px;
                        color: #94a3b8;
                        border-top: 1px solid #e2e8f0;
                        padding-top: 16px;
                    }
                    .footer p {
                        margin-bottom: 2px;
                    }
                    @media print {
                        body {
                            background: none;
                            padding: 0;
                        }
                        .container {
                            border: none;
                            padding: 0;
                            max-width: 100%;
                        }
                    }
                </style>
            </head>
            <body onload="window.print(); window.onafterprint = function(){ window.close(); }">
                <div class="container">
                    <div class="transaction-code">
                        <span class="transaction-code-label">NOMOR ANTREAN / KODE TRANSAKSI</span>
                        #${String(tx.id || 0).padStart(4, '0')}
                    </div>
                    <!-- Header -->
                    <div class="header">
                        <div>
                            <div class="store-name">${tx.store_name || store.name}</div>
                            <div class="event-name">${event ? event.name : 'Bazar UMKM Kuliner Nusantara 2026'}</div>
                            <div class="store-sub">Stand: ${store.booth_number || '-'} • ${event ? event.location : '-'} • Telp/WA: ${store.phone || '-'}</div>
                        </div>
                        <div class="invoice-info">
                            <div class="badge-paid">BUKTI PEMBAYARAN SAH</div>
                            <div class="invoice-code">${tx.invoice_code}</div>
                            <div class="invoice-date">${formatDateTime(tx.paid_at || tx.created_at)}</div>
                        </div>
                    </div>

                    <!-- Meta Bar -->
                    <div class="meta-bar">
                        <div class="meta-item">
                            <span>Kasir:</span>
                            <span>${tx.cashier_name || 'Kasir Stand'}</span>
                        </div>
                        <div class="meta-item">
                            <span>Metode:</span>
                            <span style="text-transform: uppercase;">${tx.payment_method}</span>
                        </div>
                        <div class="meta-item">
                            <span>Status:</span>
                            <span style="color: #047857;">${tx.status.toUpperCase()}</span>
                        </div>
                    </div>

                    <!-- Table of Items -->
                    <table class="items-table">
                        <thead>
                            <tr>
                                <th style="width: 35px; text-align: center;">No</th>
                                <th style="text-align: left;">Nama Menu / Produk</th>
                                <th style="text-align: right;">Harga Satuan</th>
                                <th style="text-align: center; width: 50px;">Qty</th>
                                <th style="text-align: right;">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${itemsRows}
                        </tbody>
                    </table>

                    <!-- Summary Section -->
                    <div class="summary-container">
                        <div class="summary-box">
                            <table>
                                <tr>
                                    <td style="padding: 4px 0; color: #64748b;">Subtotal Item:</td>
                                    <td style="padding: 4px 0; text-align: right; font-weight: 600;">${formatRupiah(tx.total_amount)}</td>
                                </tr>
                                <tr class="total-row">
                                    <td>TOTAL TAGIHAN:</td>
                                    <td style="text-align: right; color: #1d9bf0;">${formatRupiah(tx.total_amount)}</td>
                                </tr>
                                ${tx.status === 'paid' ? '' : `
                                <tr>
                                    <td colspan="2" style="padding: 4px 0; text-align: center; color: #f59e0b; font-size: 11px; font-weight: bold; font-style: italic;">(Menunggu konfirmasi pembayaran)</td>
                                </tr>
                                `}
                                ${paymentSummary}
                            </table>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="footer">
                        <p style="font-weight: 700; color: #1e293b;">Terima kasih atas kunjungan Anda!</p>
                        <p>Struk ini dicetak otomatis oleh sistem POS Kasir UMKM Event dan merupakan bukti transaksi yang sah.</p>
                        <p style="font-size: 10px; color: #94a3b8; margin-top: 4px;">Dukung & Bangga Produk UMKM Indonesia</p>
                    </div>
                </div>

                <script>
                    window.onload = function() {
                        window.print();
                        setTimeout(function() {
                            window.close();
                        }, 500);
                    };
                </script>
            </body>
            </html>
            `;

            this.printDocument(receiptHtml);
        },

        // Universal Iframe / Window Printable Document Generator (Crisp Monochrome B&W)
        printDocument(htmlContent) {
            try {
                const printFrame = document.createElement('iframe');
                printFrame.style.position = 'fixed';
                printFrame.style.right = '0';
                printFrame.style.bottom = '0';
                printFrame.style.width = '0';
                printFrame.style.height = '0';
                printFrame.style.border = '0';
                document.body.appendChild(printFrame);

                const frameDoc = printFrame.contentWindow.document;
                frameDoc.open();
                frameDoc.write(htmlContent);
                frameDoc.close();

                setTimeout(() => {
                    printFrame.contentWindow.focus();
                    printFrame.contentWindow.print();
                    setTimeout(() => {
                        document.body.removeChild(printFrame);
                    }, 1500);
                }, 300);
            } catch (e) {
                console.warn('Iframe print fallback to window.open', e);
                const win = window.open('', '_blank', 'width=900,height=1000');
                if (win) {
                    win.document.open();
                    win.document.write(htmlContent);
                    win.document.close();
                    setTimeout(() => {
                        win.focus();
                        win.print();
                    }, 400);
                } else {
                    window.print();
                }
            }
        },

        // PROPER FORMAL MONOCHROME (B&W) EO REPORT EXPORT
        printAdminReport(customTxList = null) {
            const txList = customTxList || this.transactions;
            const event = this.getActiveEvent() || { name: 'Event Bazaar UMKM', location: '-' };
            const stats = this.getAdminReportStats();
            const dateNow = new Date().toLocaleDateString('id-ID', {
                weekday: 'long',
                day: 'numeric',
                month: 'long',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                timeZone: WIB
            });

            // Per-store breakdown calculations
            const storeSummaries = this.stores.map(st => {
                const stTx = txList.filter(t => t.store_id === st.id && t.status === 'paid');
                const stGross = stTx.reduce((sum, t) => sum + t.total_amount, 0);
                const stOwner = stTx.reduce((sum, t) => sum + (t.revenue_split?.owner_share || t.total_amount * 0.75), 0);
                const stAdmin = stTx.reduce((sum, t) => sum + (t.revenue_split?.admin_gross_share || t.total_amount * 0.25), 0);
                return {
                    name: st.name,
                    booth: st.booth_number || '-',
                    count: stTx.length,
                    gross: stGross,
                    owner: stOwner,
                    admin: stAdmin
                };
            });

            const storeRows = storeSummaries.map((s, idx) => `
                <tr>
                    <td style="text-align: center; border: 1px solid #000; padding: 6px 8px;">${idx + 1}</td>
                    <td style="border: 1px solid #000; padding: 6px 8px; font-weight: bold;">${s.name}</td>
                    <td style="text-align: center; border: 1px solid #000; padding: 6px 8px;">${s.booth}</td>
                    <td style="text-align: center; border: 1px solid #000; padding: 6px 8px;">${s.count}</td>
                    <td style="text-align: right; border: 1px solid #000; padding: 6px 8px; font-weight: bold;">${formatRupiah(s.gross)}</td>
                    <td style="text-align: right; border: 1px solid #000; padding: 6px 8px;">${formatRupiah(s.owner)}</td>
                    <td style="text-align: right; border: 1px solid #000; padding: 6px 8px;">${formatRupiah(s.admin)}</td>
                </tr>
            `).join('');

            const txRows = txList.map((t, idx) => {
                const isNoPay = t.is_without_payment || (t.status === 'rejected' && t.rejection_reason === 'Tanpa Pembayaran');
                return `
                <tr style="${t.status === 'cancelled' ? 'text-decoration: line-through; color: #555;' : ''}">
                    <td style="text-align: center; border: 1px solid #000; padding: 5px 6px;">${idx + 1}</td>
                    <td style="border: 1px solid #000; padding: 5px 6px; font-family: monospace; font-weight: bold;">${t.invoice_code}</td>
                    <td style="border: 1px solid #000; padding: 5px 6px; font-size: 11px;">${formatDateTime(t.paid_at || t.created_at)}</td>
                    <td style="border: 1px solid #000; padding: 5px 6px;">${t.store_name || '-'}</td>
                    <td style="text-align: center; border: 1px solid #000; padding: 5px 6px; text-transform: uppercase; font-size: 11px;">${t.payment_method}</td>
                    <td style="text-align: right; border: 1px solid #000; padding: 5px 6px; font-weight: bold;">${isNoPay ? '-' : formatRupiah(t.total_amount)}</td>
                    <td style="text-align: right; border: 1px solid #000; padding: 5px 6px;">${t.status === 'paid' ? formatRupiah(t.revenue_split?.owner_share || t.total_amount * 0.75) : '-'}</td>
                    <td style="text-align: right; border: 1px solid #000; padding: 5px 6px;">${t.status === 'paid' ? formatRupiah(t.revenue_split?.admin_gross_share || t.total_amount * 0.25) : '-'}</td>
                    <td style="text-align: center; border: 1px solid #000; padding: 5px 6px; font-weight: bold; font-size: 11px;">${isNoPay ? 'TANPA PEMBAYARAN' : t.status.toUpperCase()}</td>
                </tr>
            `}).join('');

            const logoImg = window.__LOGO_BASE64__ || window.__LOGO_URL__ || '';

            const reportHtml = `
            <!DOCTYPE html>
            <html lang="id">
            <head>
                <meta charset="UTF-8">
                <title>Laporan Keuangan & Bagi Hasil EO — ${event.name}</title>
                <style>
                    @page {
                        size: A4 portrait;
                        margin: 15mm 12mm 15mm 12mm;
                    }
                    * {
                        box-sizing: border-box;
                        -webkit-print-color-adjust: exact;
                        print-color-adjust: exact;
                    }
                    body {
                        font-family: 'Arial', 'Helvetica', sans-serif;
                        font-size: 12px;
                        line-height: 1.4;
                        color: #000;
                        background: #fff;
                        margin: 0;
                        padding: 0;
                    }
                    .header {
                        border-bottom: 3px double #000;
                        padding-bottom: 8px;
                        margin-bottom: 14px;
                    }
                    .header h1 {
                        font-size: 15px;
                        font-weight: 900;
                        text-transform: uppercase;
                        margin: 0 0 2px 0;
                        letter-spacing: 0.5px;
                    }
                    .header h2 {
                        font-size: 12px;
                        font-weight: bold;
                        margin: 0 0 3px 0;
                    }
                    .header .meta {
                        font-size: 10px;
                        color: #333;
                    }
                    .section-title {
                        font-size: 12px;
                        font-weight: bold;
                        text-transform: uppercase;
                        margin: 16px 0 6px 0;
                        border-bottom: 1px solid #000;
                        padding-bottom: 3px;
                    }
                    .summary-grid {
                        width: 100%;
                        border-collapse: collapse;
                        margin-bottom: 14px;
                    }
                    .summary-grid td {
                        border: 1px solid #000;
                        padding: 7px 9px;
                        vertical-align: top;
                    }
                    .summary-label {
                        font-size: 9.5px;
                        text-transform: uppercase;
                        font-weight: bold;
                        color: #333;
                        display: block;
                    }
                    .summary-value {
                        font-size: 14px;
                        font-weight: 900;
                        margin-top: 2px;
                        display: block;
                    }
                    .summary-sub {
                        font-size: 9.5px;
                        color: #444;
                        margin-top: 1px;
                        display: block;
                    }
                    table.data-table {
                        width: 100%;
                        border-collapse: collapse;
                        font-size: 10.5px;
                        margin-bottom: 14px;
                    }
                    table.data-table th {
                        border: 1px solid #000;
                        background-color: #eee !important;
                        padding: 5px 7px;
                        font-weight: bold;
                        text-align: left;
                        font-size: 9.5px;
                        text-transform: uppercase;
                    }
                    .signature-table {
                        width: 100%;
                        margin-top: 28px;
                        page-break-inside: avoid;
                    }
                    .signature-table td {
                        width: 50%;
                        text-align: center;
                        vertical-align: top;
                        font-size: 11px;
                    }
                    .signature-space {
                        height: 55px;
                    }
                    .footer-note {
                        margin-top: 20px;
                        padding-top: 6px;
                        border-top: 1px dashed #000;
                        font-size: 9px;
                        text-align: center;
                        color: #444;
                    }
                </style>
            </head>
            <body>
                <div class="header">
                    <table style="width: 100%; border: none;">
                        <tr>
                            ${logoImg ? `
                                <td style="width: 65px; border: none; text-align: left; vertical-align: middle; padding: 0;">
                                    <img src="${logoImg}" style="height: 50px; width: auto; object-fit: contain;">
                                </td>
                            ` : ''}
                            <td style="border: none; text-align: ${logoImg ? 'left' : 'center'}; vertical-align: middle; padding: 0 0 0 10px;">
                                <h1>Laporan Rekapitulasi Keuangan & Bagi Hasil Event</h1>
                                <h2>${event.name} &bull; ${event.location || '-'}</h2>
                                <div class="meta">
                                    <span>Tanggal Cetak: <strong>${dateNow}</strong></span> &bull; 
                                    <span>Penyelenggara: <strong>Panitia Event Organizer (EO)</strong></span> &bull;
                                    <span>Sistem: <strong>JADISATU Event</strong></span>
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>

                <div class="section-title">1. Ringkasan Eksekutif Finansial</div>
                <table class="summary-grid">
                    <tr>
                        <td style="width: 33.3%;">
                            <span class="summary-label">Total Omzet Bruto</span>
                            <span class="summary-value">${formatRupiah(stats.totalGross)}</span>
                            <span class="summary-sub">${stats.paidCount} Transaksi Paid</span>
                        </td>
                        <td style="width: 33.3%;">
                            <span class="summary-label">Porsi Stand/Warung (75%)</span>
                            <span class="summary-value">${formatRupiah(stats.ownerTotal)}</span>
                            <span class="summary-sub">Hak Seluruh Tenant</span>
                        </td>
                        <td style="width: 33.3%; background: #f2f2f2;">
                            <span class="summary-label">Bagian EO (25%)</span>
                            <span class="summary-value">${formatRupiah(stats.adminGross)}</span>
                            <span class="summary-sub">Total Porsi Penyelenggara</span>
                        </td>
                    </tr>
                </table>

                <div class="section-title">2. Rekapitulasi Pendapatan per Stand / Warung</div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="text-align: center; width: 30px;">No</th>
                            <th>Nama Stand / Warung</th>
                            <th style="text-align: center; width: 70px;">Stand</th>
                            <th style="text-align: center; width: 60px;">Tx Paid</th>
                            <th style="text-align: right; width: 95px;">Total Omzet</th>
                            <th style="text-align: right; width: 95px;">Hak Warung (75%)</th>
                            <th style="text-align: right; width: 95px;">Potongan EO (25%)</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${storeRows.length > 0 ? storeRows : '<tr><td colspan="7" style="text-align: center; padding: 8px; border: 1px solid #000;">Belum ada data stand/warung</td></tr>'}
                    </tbody>
                </table>

                <div class="section-title">3. Rincian Data Transaksi (Total ${txList.length} Transaksi)</div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="text-align: center; width: 28px;">No</th>
                            <th style="width: 105px;">Invoice</th>
                            <th style="width: 85px;">Waktu</th>
                            <th>Stand Warung</th>
                            <th style="text-align: center; width: 50px;">Metode</th>
                            <th style="text-align: right; width: 80px;">Nominal</th>
                            <th style="text-align: right; width: 80px;">Warung (75%)</th>
                            <th style="text-align: right; width: 80px;">Bagian EO (25%)</th>
                            <th style="text-align: center; width: 60px;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${txRows.length > 0 ? txRows : '<tr><td colspan="9" style="text-align: center; padding: 8px; border: 1px solid #000;">Belum ada transaksi</td></tr>'}
                    </tbody>
                </table>

                <table class="signature-table">
                    <tr>
                        <td>
                            <div>Dibuat & Divalidasi Oleh:</div>
                            <div style="font-weight: bold; margin-top: 2px;">Admin Event Organizer</div>
                            <div class="signature-space"></div>
                            <div>( __________________________ )</div>
                            <div style="font-size: 10px; color: #555; margin-top: 2px;">Panitia Pelaksana EO</div>
                        </td>
                        <td>
                            <div>Mengetahui & Menyetujui:</div>
                            <div style="font-weight: bold; margin-top: 2px;">Penanggung Jawab / Ketua EO</div>
                            <div class="signature-space"></div>
                            <div>( __________________________ )</div>
                            <div style="font-size: 10px; color: #555; margin-top: 2px;">Event Organizer Lead</div>
                        </td>
                    </tr>
                </table>

                <div class="footer-note">
                    Dokumen ini digenerate otomatis oleh Sistem JADISATU &bull; Dokumen resmi untuk proses rekonsiliasi finansial dan pembagian hasil.
                </div>
            </body>
            </html>
            `;

            this.printDocument(reportHtml);
        },

        // PROPER FORMAL MONOCHROME (B&W) SINGLE TENANT / STAND REPORT EXPORT (SAME DESIGN AS ALL EO REPORT)
        printTenantReport(storeId) {
            const store = this.stores.find(s => s.id == storeId) || this.userStores.find(s => s.id == storeId) || { id: storeId, name: 'Stand Warung', booth_number: '-' };
            const event = this.getActiveEvent() || { name: 'Event Bazaar UMKM', location: '-' };
            const txList = this.transactions.filter(t => t.store_id == storeId);
            
            const paidTx = txList.filter(t => t.status === 'paid');
            const totalGross = paidTx.reduce((sum, t) => sum + t.total_amount, 0);
            const ownerTotal = paidTx.reduce((sum, t) => sum + (t.revenue_split?.owner_share || t.total_amount * 0.75), 0);
            const adminNet = paidTx.reduce((sum, t) => sum + (t.revenue_split?.admin_net_share || t.total_amount * 0.225), 0);
            const platformFee = paidTx.reduce((sum, t) => sum + (t.revenue_split?.superadmin_share || t.total_amount * 0.025), 0);
            const paidCount = paidTx.length;
            const cashCount = paidTx.filter(t => t.payment_method === 'cash').length;
            const qrisCount = paidTx.filter(t => t.payment_method === 'qris').length;

            const dateNow = new Date().toLocaleDateString('id-ID', {
                weekday: 'long',
                day: 'numeric',
                month: 'long',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                timeZone: WIB
            });

            const txRows = txList.map((t, idx) => {
                const isNoPay = t.is_without_payment || (t.status === 'rejected' && t.rejection_reason === 'Tanpa Pembayaran');
                return `
                <tr style="${t.status === 'cancelled' ? 'text-decoration: line-through; color: #555;' : ''}">
                    <td style="text-align: center; border: 1px solid #000; padding: 5px 6px;">${idx + 1}</td>
                    <td style="border: 1px solid #000; padding: 5px 6px; font-family: monospace; font-weight: bold;">${t.invoice_code}</td>
                    <td style="border: 1px solid #000; padding: 5px 6px; font-size: 11px;">${formatDateTime(t.paid_at || t.created_at)}</td>
                    <td style="text-align: center; border: 1px solid #000; padding: 5px 6px; text-transform: uppercase; font-size: 11px;">${t.payment_method}</td>
                    <td style="text-align: right; border: 1px solid #000; padding: 5px 6px; font-weight: bold;">${isNoPay ? '-' : formatRupiah(t.total_amount)}</td>
                    <td style="text-align: right; border: 1px solid #000; padding: 5px 6px; font-weight: bold;">${t.status === 'paid' ? formatRupiah(t.revenue_split?.owner_share || t.total_amount * 0.75) : '-'}</td>
                    <td style="text-align: right; border: 1px solid #000; padding: 5px 6px; font-weight: bold;">${t.status === 'paid' ? formatRupiah(t.revenue_split?.admin_gross_share || t.total_amount * 0.25) : '-'}</td>
                    <td style="text-align: center; border: 1px solid #000; padding: 5px 6px; font-weight: bold; font-size: 11px;">${isNoPay ? 'TANPA PEMBAYARAN' : t.status.toUpperCase()}</td>
                </tr>
            `}).join('');

            const logoImg = window.__LOGO_BASE64__ || window.__LOGO_URL__ || '';

            const reportHtml = `
            <!DOCTYPE html>
            <html lang="id">
            <head>
                <meta charset="UTF-8">
                <title>Laporan Penjualan & Bagi Hasil Stand — ${store.name}</title>
                <style>
                    @page {
                        size: A4 portrait;
                        margin: 15mm 12mm 15mm 12mm;
                    }
                    * {
                        box-sizing: border-box;
                        -webkit-print-color-adjust: exact;
                        print-color-adjust: exact;
                    }
                    body {
                        font-family: 'Arial', 'Helvetica', sans-serif;
                        font-size: 12px;
                        line-height: 1.4;
                        color: #000;
                        background: #fff;
                        margin: 0;
                        padding: 0;
                    }
                    .header {
                        border-bottom: 3px double #000;
                        padding-bottom: 8px;
                        margin-bottom: 14px;
                    }
                    .header h1 {
                        font-size: 15px;
                        font-weight: 900;
                        text-transform: uppercase;
                        margin: 0 0 2px 0;
                        letter-spacing: 0.5px;
                    }
                    .header h2 {
                        font-size: 12px;
                        font-weight: bold;
                        margin: 0 0 3px 0;
                    }
                    .header .meta {
                        font-size: 10px;
                        color: #333;
                    }
                    .section-title {
                        font-size: 12px;
                        font-weight: bold;
                        text-transform: uppercase;
                        margin: 16px 0 6px 0;
                        border-bottom: 1px solid #000;
                        padding-bottom: 3px;
                    }
                    .summary-grid {
                        width: 100%;
                        border-collapse: collapse;
                        margin-bottom: 14px;
                    }
                    .summary-grid td {
                        border: 1px solid #000;
                        padding: 7px 9px;
                        vertical-align: top;
                    }
                    .summary-label {
                        font-size: 9.5px;
                        text-transform: uppercase;
                        font-weight: bold;
                        color: #333;
                        display: block;
                    }
                    .summary-value {
                        font-size: 14px;
                        font-weight: 900;
                        margin-top: 2px;
                        display: block;
                    }
                    .summary-sub {
                        font-size: 9.5px;
                        color: #444;
                        margin-top: 1px;
                        display: block;
                    }
                    table.data-table {
                        width: 100%;
                        border-collapse: collapse;
                        font-size: 10.5px;
                        margin-bottom: 14px;
                    }
                    table.data-table th {
                        border: 1px solid #000;
                        background-color: #eee !important;
                        padding: 5px 7px;
                        font-weight: bold;
                        text-align: left;
                        font-size: 9.5px;
                        text-transform: uppercase;
                    }
                    .signature-table {
                        width: 100%;
                        margin-top: 28px;
                        page-break-inside: avoid;
                    }
                    .signature-table td {
                        width: 50%;
                        text-align: center;
                        vertical-align: top;
                        font-size: 11px;
                    }
                    .signature-space {
                        height: 55px;
                    }
                    .footer-note {
                        margin-top: 20px;
                        padding-top: 6px;
                        border-top: 1px dashed #000;
                        font-size: 9px;
                        text-align: center;
                        color: #444;
                    }
                </style>
            </head>
            <body>
                <div class="header">
                    <table style="width: 100%; border: none;">
                        <tr>
                            ${logoImg ? `
                                <td style="width: 65px; border: none; text-align: left; vertical-align: middle; padding: 0;">
                                    <img src="${logoImg}" style="height: 50px; width: auto; object-fit: contain;">
                                </td>
                            ` : ''}
                            <td style="border: none; text-align: ${logoImg ? 'left' : 'center'}; vertical-align: middle; padding: 0 0 0 10px;">
                                <h1>Laporan Penjualan & Bagi Hasil Stand Tenant</h1>
                                <h2>${store.name} (${store.booth_number ? 'Stand ' + store.booth_number : 'Stand Tenant'}) &bull; ${event.name}</h2>
                                <div class="meta">
                                    <span>Pemilik: <strong>${store.owner_name || '-'}</strong> (${store.phone || '-'})</span> &bull;
                                    <span>Tanggal Cetak: <strong>${dateNow}</strong></span> &bull; 
                                    <span>Sistem: <strong>JADISATU Event</strong></span>
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>

                <div class="section-title">1. Ringkasan Eksekutif Finansial Stand</div>
                <table class="summary-grid">
                    <tr>
                        <td style="width: 33.3%;">
                            <span class="summary-label">Total Omzet Stand</span>
                            <span class="summary-value">${formatRupiah(totalGross)}</span>
                            <span class="summary-sub">${paidCount} Tx Paid (${cashCount} Cash / ${qrisCount} QRIS)</span>
                        </td>
                        <td style="width: 33.3%; background: #f2f2f2;">
                            <span class="summary-label">Hak Bersih Warung (75%)</span>
                            <span class="summary-value">${formatRupiah(ownerTotal)}</span>
                            <span class="summary-sub">Porsi Hak Pemilik Stand</span>
                        </td>
                        <td style="width: 33.3%;">
                            <span class="summary-label">Bagian EO (25%)</span>
                            <span class="summary-value">${formatRupiah(totalGross * 0.25)}</span>
                            <span class="summary-sub">Bagi Hasil Penyelenggara</span>
                        </td>
                    </tr>
                </table>

                <div class="section-title">2. Rincian Data Transaksi Stand (Total ${txList.length} Transaksi)</div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="text-align: center; width: 28px;">No</th>
                            <th style="width: 110px;">Invoice</th>
                            <th style="width: 95px;">Waktu</th>
                            <th style="text-align: center; width: 60px;">Metode</th>
                            <th style="text-align: right; width: 90px;">Nominal</th>
                            <th style="text-align: right; width: 95px;">Warung (75%)</th>
                            <th style="text-align: right; width: 90px;">Bagian EO (25%)</th>
                            <th style="text-align: center; width: 70px;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${txRows.length > 0 ? txRows : '<tr><td colspan="8" style="text-align: center; padding: 8px; border: 1px solid #000;">Belum ada transaksi pada stand ini</td></tr>'}
                    </tbody>
                </table>

                <table class="signature-table">
                    <tr>
                        <td>
                            <div>Dibuat & Divalidasi Oleh:</div>
                            <div style="font-weight: bold; margin-top: 2px;">Admin Event Organizer</div>
                            <div class="signature-space"></div>
                            <div>( __________________________ )</div>
                            <div style="font-size: 10px; color: #555; margin-top: 2px;">Panitia Pelaksana EO</div>
                        </td>
                        <td>
                            <div>Mengetahui & Menyetujui:</div>
                            <div style="font-weight: bold; margin-top: 2px;">Pemilik Stand / Warung</div>
                            <div class="signature-space"></div>
                            <div>( ${store.owner_name || '__________________________'} )</div>
                            <div style="font-size: 10px; color: #555; margin-top: 2px;">${store.name}</div>
                        </td>
                    </tr>
                </table>

                <div class="footer-note">
                    Dokumen ini digenerate otomatis oleh Sistem JADISATU &bull; Dokumen resmi untuk proses rekonsiliasi finansial dan pembagian hasil.
                </div>
            </body>
            </html>
            `;

            this.printDocument(reportHtml);
        },

        // PROPER FORMAL MONOCHROME (B&W) USER/TENANT REPORT EXPORT (STANDARDIZED TEMPLATE, TANPA TTD)
        printUserReport(customTxList = null) {
            const store = this.getCurrentStore() || { id: 1, name: 'Stand Warung', booth_number: '-' };
            const event = this.getActiveEvent() || { name: 'Event Bazaar UMKM', location: '-' };
            const stats = this.getUserReportStats(store.id);
            const txList = customTxList || this.transactions.filter(t => t.store_id === store.id);
            const logoImg = window.__LOGO_BASE64__ || window.__LOGO_URL__ || '';
            const dateNow = new Date().toLocaleDateString('id-ID', {
                weekday: 'long',
                day: 'numeric',
                month: 'long',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                timeZone: WIB
            });

            const txRows = txList.map((t, idx) => {
                const isNoPay = t.is_without_payment || (t.status === 'rejected' && t.rejection_reason === 'Tanpa Pembayaran');
                return `
                <tr style="${t.status === 'cancelled' ? 'text-decoration: line-through; color: #555;' : ''}">
                    <td style="text-align: center; border: 1px solid #000; padding: 5px 6px;">${idx + 1}</td>
                    <td style="border: 1px solid #000; padding: 5px 6px; font-family: monospace; font-weight: bold;">${t.invoice_code}</td>
                    <td style="border: 1px solid #000; padding: 5px 6px; font-size: 11px;">${formatDateTime(t.paid_at || t.created_at)}</td>
                    <td style="text-align: center; border: 1px solid #000; padding: 5px 6px; text-transform: uppercase; font-size: 11px;">${t.payment_method}</td>
                    <td style="text-align: right; border: 1px solid #000; padding: 5px 6px; font-weight: bold;">${isNoPay ? '-' : formatRupiah(t.total_amount)}</td>
                    <td style="text-align: right; border: 1px solid #000; padding: 5px 6px; font-weight: bold;">${t.status === 'paid' ? formatRupiah(t.revenue_split?.owner_share || t.total_amount * 0.75) : '-'}</td>
                    <td style="text-align: right; border: 1px solid #000; padding: 5px 6px; font-weight: bold;">${t.status === 'paid' ? formatRupiah(t.revenue_split?.admin_gross_share || t.total_amount * 0.25) : '-'}</td>
                    <td style="text-align: center; border: 1px solid #000; padding: 5px 6px; font-weight: bold; font-size: 11px;">${isNoPay ? 'TANPA PEMBAYARAN' : t.status.toUpperCase()}</td>
                </tr>
            `}).join('');

            const reportHtml = `
            <!DOCTYPE html>
            <html lang="id">
            <head>
                <meta charset="UTF-8">
                <title>Laporan Penjualan Stand — ${store.name}</title>
                <style>
                    @page {
                        size: A4 portrait;
                        margin: 15mm 12mm 15mm 12mm;
                    }
                    * {
                        box-sizing: border-box;
                        -webkit-print-color-adjust: exact;
                        print-color-adjust: exact;
                    }
                    body {
                        font-family: 'Arial', 'Helvetica', sans-serif;
                        font-size: 12px;
                        line-height: 1.4;
                        color: #000;
                        background: #fff;
                        margin: 0;
                        padding: 0;
                    }
                    .header {
                        border-bottom: 3px double #000;
                        padding-bottom: 8px;
                        margin-bottom: 14px;
                    }
                    .header h1 {
                        font-size: 15px;
                        font-weight: 900;
                        text-transform: uppercase;
                        margin: 0 0 2px 0;
                        letter-spacing: 0.5px;
                    }
                    .header h2 {
                        font-size: 12px;
                        font-weight: bold;
                        margin: 0 0 3px 0;
                    }
                    .header .meta {
                        font-size: 10px;
                        color: #333;
                    }
                    .section-title {
                        font-size: 12px;
                        font-weight: bold;
                        text-transform: uppercase;
                        margin: 16px 0 6px 0;
                        border-bottom: 1px solid #000;
                        padding-bottom: 3px;
                    }
                    .summary-grid {
                        width: 100%;
                        border-collapse: collapse;
                        margin-bottom: 14px;
                    }
                    .summary-grid td {
                        border: 1px solid #000;
                        padding: 7px 9px;
                        vertical-align: top;
                    }
                    .summary-label {
                        font-size: 9.5px;
                        text-transform: uppercase;
                        font-weight: bold;
                        color: #333;
                        display: block;
                    }
                    .summary-value {
                        font-size: 14px;
                        font-weight: 900;
                        margin-top: 2px;
                        display: block;
                    }
                    table.data-table {
                        width: 100%;
                        border-collapse: collapse;
                        font-size: 10.5px;
                        margin-bottom: 14px;
                    }
                    table.data-table th {
                        border: 1px solid #000;
                        background-color: #eee !important;
                        padding: 5px 7px;
                        font-weight: bold;
                        text-align: left;
                        font-size: 9.5px;
                        text-transform: uppercase;
                    }
                    .footer-note {
                        margin-top: 24px;
                        padding-top: 8px;
                        border-top: 1px dashed #000;
                        font-size: 9px;
                        text-align: center;
                        color: #444;
                    }
                </style>
            </head>
            <body>
                <div class="header">
                    <table style="width: 100%; border: none;">
                        <tr>
                            ${logoImg ? `
                                <td style="width: 65px; border: none; text-align: left; vertical-align: middle; padding: 0;">
                                    <img src="${logoImg}" style="height: 50px; width: auto; object-fit: contain;">
                                </td>
                            ` : ''}
                            <td style="border: none; text-align: ${logoImg ? 'left' : 'center'}; vertical-align: middle; padding: 0 0 0 10px;">
                                <h1>Laporan Penjualan & Bagi Hasil Stand</h1>
                                <h2>${store.name} (${store.booth_number || 'Stand Tenant'}) &bull; ${event.name}</h2>
                                <div class="meta">
                                    <span>Tanggal Cetak: <strong>${dateNow}</strong></span> &bull; 
                                    <span>Lokasi: <strong>${event.location || '-'}</strong></span> &bull;
                                    <span>Sistem: <strong>JADISATU Event</strong></span>
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>

                <div class="section-title">1. Ringkasan Omzet Stand</div>
                <table class="summary-grid">
                    <tr>
                        <td style="width: 33.3%;">
                            <span class="summary-label">Total Omzet Bruto</span>
                            <span class="summary-value">${formatRupiah(stats.totalGross)}</span>
                            <span style="font-size: 9.5px; color: #444;">${stats.totalCount} Transaksi Paid</span>
                        </td>
                        <td style="width: 33.3%; background: #f2f2f2;">
                            <span class="summary-label">Hak Pemilik Warung (75%)</span>
                            <span class="summary-value">${formatRupiah(stats.netIncome)}</span>
                            <span style="font-size: 9.5px; color: #444;">Pendapatan Bersih Tenant</span>
                        </td>
                        <td style="width: 33.3%;">
                            <span class="summary-label">Potongan Platform & EO (25%)</span>
                            <span class="summary-value">${formatRupiah(stats.totalGross * 0.25)}</span>
                            <span style="font-size: 9.5px; color: #444;">Penyelenggara & Platform</span>
                        </td>
                    </tr>
                </table>

                <div class="section-title">2. Rincian Riwayat Transaksi</div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="text-align: center; width: 28px;">No</th>
                            <th style="width: 110px;">Invoice</th>
                            <th style="width: 100px;">Waktu</th>
                            <th style="text-align: center; width: 60px;">Metode</th>
                            <th style="text-align: right; width: 95px;">Total Belanja</th>
                            <th style="text-align: right; width: 95px;">Hak Warung (75%)</th>
                            <th style="text-align: right; width: 95px;">Potongan EO (25%)</th>
                            <th style="text-align: center; width: 65px;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${txRows.length > 0 ? txRows : '<tr><td colspan="8" style="text-align: center; padding: 8px; border: 1px solid #000;">Belum ada transaksi</td></tr>'}
                    </tbody>
                </table>

                <div class="footer-note">
                    Dokumen dicetak otomatis dari Sistem JADISATU &bull; Berlaku sah sebagai rekapitulasi penjualan stand.
                </div>
            </body>
            </html>
            `;

            this.printDocument(reportHtml);
        },

        // PROPER FORMAL MONOCHROME (B&W) SUPERADMIN AUDIT REPORT EXPORT
        printSuperAdminReport(customTxList = null) {
            const stats = this.getSuperAdminStats();
            const txList = customTxList || this.transactions.filter(t => t.status === 'paid');
            const event = this.getActiveEvent() || { name: 'Multi-Event UMKM' };
            const dateNow = new Date().toLocaleDateString('id-ID', {
                weekday: 'long',
                day: 'numeric',
                month: 'long',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                timeZone: WIB
            });

            const txRows = txList.map((t, idx) => `
                <tr>
                    <td style="text-align: center; border: 1px solid #000; padding: 5px 6px;">${idx + 1}</td>
                    <td style="border: 1px solid #000; padding: 5px 6px; font-family: monospace; font-weight: bold;">${t.invoice_code}</td>
                    <td style="border: 1px solid #000; padding: 5px 6px; font-size: 11px;">${formatDateTime(t.paid_at || t.created_at)}</td>
                    <td style="border: 1px solid #000; padding: 5px 6px;">${t.store_name || '-'}</td>
                    <td style="text-align: center; border: 1px solid #000; padding: 5px 6px; text-transform: uppercase; font-size: 11px;">${t.payment_method}</td>
                    <td style="text-align: right; border: 1px solid #000; padding: 5px 6px; font-weight: bold;">${formatRupiah(t.total_amount)}</td>
                    <td style="text-align: right; border: 1px solid #000; padding: 5px 6px; font-weight: bold; background: #f2f2f2;">${formatRupiah(t.revenue_split?.superadmin_share || (t.total_amount * 0.025))}</td>
                    <td style="text-align: right; border: 1px solid #000; padding: 5px 6px;">${formatRupiah(t.revenue_split?.admin_net_share || t.total_amount * 0.225)}</td>
                    <td style="text-align: right; border: 1px solid #000; padding: 5px 6px;">${formatRupiah(t.revenue_split?.owner_share || t.total_amount * 0.75)}</td>
                </tr>
            `).join('');

            const logoImg = window.__LOGO_BASE64__ || window.__LOGO_URL__ || '';

            const reportHtml = `
            <!DOCTYPE html>
            <html lang="id">
            <head>
                <meta charset="UTF-8">
                <title>Laporan Platform Fee Developer — ${event.name}</title>
                <style>
                    @page {
                        size: A4 portrait;
                        margin: 15mm 12mm 15mm 12mm;
                    }
                    * {
                        box-sizing: border-box;
                        -webkit-print-color-adjust: exact;
                        print-color-adjust: exact;
                    }
                    body {
                        font-family: 'Arial', 'Helvetica', sans-serif;
                        font-size: 12px;
                        line-height: 1.4;
                        color: #000;
                        background: #fff;
                        margin: 0;
                        padding: 0;
                    }
                    .header {
                        text-align: center;
                        border-bottom: 3px double #000;
                        padding-bottom: 10px;
                        margin-bottom: 14px;
                    }
                    .header h1 {
                        font-size: 16px;
                        font-weight: 900;
                        text-transform: uppercase;
                        margin: 0 0 3px 0;
                    }
                    .header h2 {
                        font-size: 13px;
                        font-weight: bold;
                        margin: 0 0 5px 0;
                    }
                    .header .meta {
                        font-size: 10.5px;
                        color: #222;
                    }
                    .section-title {
                        font-size: 12px;
                        font-weight: bold;
                        text-transform: uppercase;
                        margin: 16px 0 6px 0;
                        border-bottom: 1px solid #000;
                        padding-bottom: 3px;
                    }
                    .summary-grid {
                        width: 100%;
                        border-collapse: collapse;
                        margin-bottom: 14px;
                    }
                    .summary-grid td {
                        border: 1px solid #000;
                        padding: 7px 9px;
                        vertical-align: top;
                    }
                    .summary-label {
                        font-size: 9.5px;
                        text-transform: uppercase;
                        font-weight: bold;
                        color: #333;
                        display: block;
                    }
                    .summary-value {
                        font-size: 14px;
                        font-weight: 900;
                        margin-top: 2px;
                        display: block;
                    }
                    table.data-table {
                        width: 100%;
                        border-collapse: collapse;
                        font-size: 10.5px;
                        margin-bottom: 14px;
                    }
                    table.data-table th {
                        border: 1px solid #000;
                        background-color: #eee !important;
                        padding: 5px 7px;
                        font-weight: bold;
                        text-align: left;
                        font-size: 9.5px;
                        text-transform: uppercase;
                    }
                    .signature-table {
                        width: 100%;
                        margin-top: 28px;
                        page-break-inside: avoid;
                    }
                    .signature-table td {
                        width: 50%;
                        text-align: center;
                        vertical-align: top;
                        font-size: 11px;
                    }
                    .signature-space {
                        height: 55px;
                    }
                    .footer-note {
                        margin-top: 20px;
                        padding-top: 6px;
                        border-top: 1px dashed #000;
                        font-size: 9px;
                        text-align: center;
                        color: #444;
                    }
                </style>
            </head>
            <body>
                <div class="header">
                    <table style="width: 100%; border: none;">
                        <tr>
                            ${logoImg ? `
                                <td style="width: 65px; border: none; text-align: left; vertical-align: middle; padding: 0;">
                                    <img src="${logoImg}" style="height: 50px; width: auto; object-fit: contain;">
                                </td>
                            ` : ''}
                            <td style="border: none; text-align: ${logoImg ? 'left' : 'center'}; vertical-align: middle; padding: 0 0 0 10px;">
                                <h1>Laporan Audit Finansial Platform Developer</h1>
                                <h2>Fee Platform 2.5% dari Omzet &bull; ${event.name}</h2>
                                <div class="meta">
                                    <span>Tanggal Cetak: <strong>${dateNow}</strong></span> &bull; 
                                    <span>Audit Platform POS Multi-Tenant UMKM &bull; <strong>JADISATU Platform</strong></span>
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>

                <div class="section-title">1. Ringkasan Pendapatan Platform</div>
                <table class="summary-grid">
                    <tr>
                        <td style="width: 33.3%; background: #f2f2f2;">
                            <span class="summary-label">Total Fee Developer</span>
                            <span class="summary-value">${formatRupiah(stats.totalSuperAdminRevenue)}</span>
                            <span style="font-size: 9.5px; color: #444;">2.5% dari Omzet Paid</span>
                        </td>
                        <td style="width: 33.3%;">
                            <span class="summary-label">Total Volume Transaksi Paid</span>
                            <span class="summary-value">${formatRupiah(stats.totalVolume)}</span>
                            <span style="font-size: 9.5px; color: #444;">Gross Volume Seluruh Stand</span>
                        </td>
                        <td style="width: 33.3%;">
                            <span class="summary-label">Total Event Terdaftar</span>
                            <span class="summary-value">${stats.totalEvents} Event</span>
                            <span style="font-size: 9.5px; color: #444;">Sistem Aktif Multi-Tenant</span>
                        </td>
                    </tr>
                </table>

                <div class="section-title">2. Rincian Transaksi Paid & Potongan Flat Fee Platform</div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="text-align: center; width: 28px;">No</th>
                            <th style="width: 105px;">Invoice</th>
                            <th style="width: 85px;">Waktu</th>
                            <th>Stand Tenant</th>
                            <th style="text-align: center; width: 50px;">Metode</th>
                            <th style="text-align: right; width: 85px;">Gross</th>
                            <th style="text-align: right; width: 85px;">Developer</th>
                            <th style="text-align: right; width: 85px;">Net EO</th>
                            <th style="text-align: right; width: 85px;">Warung (75%)</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${txRows.length > 0 ? txRows : '<tr><td colspan="9" style="text-align: center; padding: 8px; border: 1px solid #000;">Belum ada transaksi paid</td></tr>'}
                    </tbody>
                </table>

                <table class="signature-table">
                    <tr>
                        <td>
                            <div>Disusun Oleh:</div>
                            <div style="font-weight: bold; margin-top: 2px;">Developer Platform</div>
                            <div class="signature-space"></div>
                            <div>( __________________________ )</div>
                            <div style="font-size: 9px; color: #555; margin-top: 2px;">Administrator Developer</div>
                        </td>
                        <td>
                            <div>Mengetahui & Menyetujui:</div>
                            <div style="font-weight: bold; margin-top: 2px;">Auditor Finansial / Manajemen</div>
                            <div class="signature-space"></div>
                            <div>( __________________________ )</div>
                            <div style="font-size: 9px; color: #555; margin-top: 2px;">Auditor Platform</div>
                        </td>
                    </tr>
                </table>

                <div class="footer-note">
                    Dokumen resmi audit lisensi platform POS UMKM &bull; Dihasilkan langsung dari database transaksi terenkripsi.
                </div>
            </body>
            </html>
            `;

            this.printDocument(reportHtml);
        },

        // EXPORT SUPERADMIN REPORT TO WORD (.DOC) WITH SIGNATURES
        exportSuperAdminReportWord(customTxList = null) {
            const stats = this.getSuperAdminStats();
            const txList = customTxList || this.transactions.filter(t => t.status === 'paid');
            const event = this.getActiveEvent() || { name: 'Multi-Event UMKM' };
            const dateStr = new Date().toISOString().slice(0, 10);
            const dateNow = new Date().toLocaleDateString('id-ID', {
                weekday: 'long',
                day: 'numeric',
                month: 'long',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                timeZone: WIB
            });

            const txRows = txList.map((t, idx) => `
                <tr>
                    <td style="text-align: center; border: 1pt solid #000; padding: 4pt 5pt;">${idx + 1}</td>
                    <td style="border: 1pt solid #000; padding: 4pt 5pt; font-family: monospace; font-weight: bold;">${t.invoice_code}</td>
                    <td style="border: 1pt solid #000; padding: 4pt 5pt; font-size: 8.5pt;">${formatDateTime(t.paid_at || t.created_at)}</td>
                    <td style="border: 1pt solid #000; padding: 4pt 5pt;">${t.store_name || '-'}</td>
                    <td style="text-align: center; border: 1pt solid #000; padding: 4pt 5pt; text-transform: uppercase; font-size: 8.5pt;">${t.payment_method}</td>
                    <td style="text-align: right; border: 1pt solid #000; padding: 4pt 5pt; font-weight: bold;">${formatRupiah(t.total_amount)}</td>
                    <td style="text-align: right; border: 1pt solid #000; padding: 4pt 5pt;">${formatRupiah(t.revenue_split?.owner_share || t.total_amount * 0.75)}</td>
                    <td style="text-align: right; border: 1pt solid #000; padding: 4pt 5pt;">${formatRupiah(t.revenue_split?.admin_gross_share || t.total_amount * 0.25)}</td>
                    <td style="text-align: right; border: 1pt solid #000; padding: 4pt 5pt; font-weight: bold; background: #f2f2f2;">${formatRupiah(t.revenue_split?.superadmin_share || (t.total_amount * 0.025))}</td>
                    <td style="text-align: right; border: 1pt solid #000; padding: 4pt 5pt;">${formatRupiah(t.revenue_split?.admin_net_share || t.total_amount * 0.225)}</td>
                </tr>
            `).join('');

            const logoImg = window.__LOGO_BASE64__ || window.__LOGO_URL__ || '';

            const wordContent = `
            <html xmlns:o='urn:schemas-microsoft-com:office:office' xmlns:w='urn:schemas-microsoft-com:office:word' xmlns='http://www.w3.org/TR/REC-html40'>
            <head>
                <meta charset="utf-8">
                <title>Laporan_Platform_Fee_Developer_${dateStr}</title>
                <!--[if gte mso 9]>
                <xml>
                <w:WordDocument>
                    <w:View>Print</w:View>
                    <w:Zoom>100</w:Zoom>
                    <w:DoNotOptimizeForBrowser/>
                </w:WordDocument>
                </xml>
                <![endif]-->
                <style>
                    @page Section1 {
                        size: 595.3pt 841.9pt; /* A4 */
                        margin: 42.5pt 42.5pt 42.5pt 42.5pt;
                        mso-header-margin: 35.4pt;
                        mso-footer-margin: 35.4pt;
                        mso-paper-source: 0;
                    }
                    div.Section1 { page: Section1; }
                    body { font-family: 'Arial', sans-serif; font-size: 9.5pt; color: #000; line-height: 1.3; }
                    h1 { font-size: 14pt; font-weight: bold; text-transform: uppercase; margin: 0 0 2pt 0; }
                    h2 { font-size: 11pt; font-weight: bold; color: #111; margin: 0 0 4pt 0; }
                    .meta { font-size: 8.5pt; color: #333; }
                    .section-title { font-size: 10pt; font-weight: bold; text-transform: uppercase; margin: 12pt 0 4pt 0; border-bottom: 1pt solid #000; padding-bottom: 2pt; }
                    table { width: 100%; border-collapse: collapse; margin-bottom: 10pt; font-size: 9pt; }
                    th { border: 1pt solid #000; background-color: #f2f2f2; padding: 4pt 5pt; font-weight: bold; text-align: left; text-transform: uppercase; font-size: 8.5pt; }
                    td { border: 1pt solid #000; padding: 4pt 5pt; vertical-align: top; }
                    .sig-table { width: 100%; border: none; margin-top: 24pt; }
                    .sig-table td { border: none; width: 50%; text-align: center; vertical-align: top; }
                    .sig-space { height: 48pt; }
                    .footer-note { font-size: 8.5pt; text-align: center; color: #555; border-top: 1pt dashed #000; padding-top: 6pt; margin-top: 16pt; }
                </style>
            </head>
            <body>
                <div class="Section1">
                    <div style="border-bottom: 2pt double #000; padding-bottom: 6pt; margin-bottom: 10pt;">
                        <table style="width: 100%; border: none;">
                            <tr>
                                ${logoImg ? `
                                    <td style="width: 60pt; border: none; text-align: left; vertical-align: middle; padding: 0;">
                                        <img src="${logoImg}" style="height: 45pt; width: auto;" width="60" height="45">
                                    </td>
                                ` : ''}
                                <td style="border: none; text-align: ${logoImg ? 'left' : 'center'}; vertical-align: middle; padding-left: 8pt;">
                                    <h1 style="text-align: ${logoImg ? 'left' : 'center'};">Laporan Audit Finansial Platform Developer</h1>
                                    <h2 style="text-align: ${logoImg ? 'left' : 'center'};">Fee Platform 2.5% dari Omzet &bull; ${event.name}</h2>
                                    <div class="meta" style="text-align: ${logoImg ? 'left' : 'center'};">
                                        Tanggal Cetak: <strong>${dateNow}</strong> &bull; Sistem: <strong>JADISATU Platform Multi-Event</strong>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <div class="section-title">1. Ringkasan Pendapatan Platform</div>
                    <table>
                        <tr>
                            <td style="width: 33.3%; background-color: #f2f2f2;">
                                <div style="font-size: 8.5pt; font-weight: bold; text-transform: uppercase; color: #444;">Total Fee Developer</div>
                                <div style="font-size: 12pt; font-weight: bold; margin-top: 2pt;">${formatRupiah(stats.totalSuperAdminRevenue)}</div>
                                <div style="font-size: 8.5pt; color: #555;">2.5% dari Omzet Paid</div>
                            </td>
                            <td style="width: 33.3%;">
                                <div style="font-size: 8.5pt; font-weight: bold; text-transform: uppercase; color: #444;">Total Volume Transaksi</div>
                                <div style="font-size: 12pt; font-weight: bold; margin-top: 2pt;">${formatRupiah(stats.totalVolume)}</div>
                                <div style="font-size: 8.5pt; color: #555;">Gross Volume Seluruh Stand</div>
                            </td>
                            <td style="width: 33.3%;">
                                <div style="font-size: 8.5pt; font-weight: bold; text-transform: uppercase; color: #444;">Total Event Terdaftar</div>
                                <div style="font-size: 12pt; font-weight: bold; margin-top: 2pt;">${stats.totalEvents} Event</div>
                                <div style="font-size: 8.5pt; color: #555;">Sistem Aktif Multi-Tenant</div>
                            </td>
                        </tr>
                    </table>

                    <div class="section-title">2. Rincian Transaksi Paid & Breakdown Bagi Hasil</div>
                    <table>
                        <thead>
                            <tr>
                                <th style="text-align: center; width: 20pt;">No</th>
                                <th style="width: 70pt;">Invoice</th>
                                <th style="width: 65pt;">Waktu</th>
                                <th>Stand Tenant</th>
                                <th style="text-align: center; width: 40pt;">Metode</th>
                                <th style="text-align: right; width: 60pt;">Gross</th>
                                <th style="text-align: right; width: 60pt;">Warung (75%)</th>
                                <th style="text-align: right; width: 60pt;">Potongan EO (25%)</th>
                                <th style="text-align: right; width: 55pt;">Fee Dev (10%)</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${txRows.length > 0 ? txRows : '<tr><td colspan="9" style="text-align: center; padding: 6pt;">Belum ada transaksi paid</td></tr>'}
                        </tbody>
                    </table>

                    <table class="sig-table">
                        <tr>
                            <td>
                                <div>Disusun Oleh:</div>
                                <div style="font-weight: bold; margin-top: 2pt;">Developer Platform</div>
                                <div class="sig-space"></div>
                                <div>( __________________________ )</div>
                                <div style="font-size: 8.5pt; color: #555; margin-top: 2pt;">Administrator Developer</div>
                            </td>
                            <td>
                                <div>Mengetahui & Menyetujui:</div>
                                <div style="font-weight: bold; margin-top: 2pt;">Auditor Finansial / Manajemen</div>
                                <div class="sig-space"></div>
                                <div>( __________________________ )</div>
                                <div style="font-size: 8.5pt; color: #555; margin-top: 2pt;">Auditor Platform</div>
                            </td>
                        </tr>
                    </table>

                    <div class="footer-note">
                        Dokumen resmi audit lisensi platform POS UMKM &bull; Dihasilkan langsung dari database transaksi terenkripsi.
                    </div>
                </div>
            </body>
            </html>
            `;

            this.downloadReportFile(wordContent, `Laporan_Developer_${dateStr}.doc`, 'application/msword');
        },

        // EXPORT SUPERADMIN REPORT TO EXCEL (.XLS)
        exportSuperAdminReportExcel(customTxList = null) {
            const stats = this.getSuperAdminStats();
            const txList = customTxList || this.transactions.filter(t => t.status === 'paid');
            const event = this.getActiveEvent() || { name: 'Multi-Event UMKM' };
            const dateStr = new Date().toISOString().slice(0, 10);
            const dateNow = new Date().toLocaleDateString('id-ID', {
                weekday: 'long',
                day: 'numeric',
                month: 'long',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                timeZone: WIB
            });

            const txRows = txList.map((t, idx) => `
                <tr>
                    <td style="text-align: center; border: 1px solid #000;">${idx + 1}</td>
                    <td style="border: 1px solid #000; font-family: monospace;">${t.invoice_code}</td>
                    <td style="border: 1px solid #000;">${formatDateTime(t.paid_at || t.created_at)}</td>
                    <td style="border: 1px solid #000;">${t.store_name || '-'}</td>
                    <td style="text-align: center; border: 1px solid #000; text-transform: uppercase;">${t.payment_method}</td>
                    <td style="text-align: right; border: 1px solid #000;">${t.total_amount}</td>
                    <td style="text-align: right; border: 1px solid #000;">${t.revenue_split?.owner_share || t.total_amount * 0.75}</td>
                    <td style="text-align: right; border: 1px solid #000;">${t.revenue_split?.admin_gross_share || t.total_amount * 0.25}</td>
                    <td style="text-align: right; border: 1px solid #000; font-weight: bold; background: #e6f2ff;">${t.revenue_split?.superadmin_share || (t.total_amount * 0.025)}</td>
                </tr>
            `).join('');

            const excelContent = `
            <html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
            <head>
                <meta charset="utf-8">
                <!--[if gte mso 9]>
                <xml>
                <x:ExcelWorkbook>
                <x:ExcelWorksheets>
                    <x:ExcelWorksheet>
                    <x:Name>Laporan Developer</x:Name>
                    <x:WorksheetOptions>
                        <x:DisplayGridlines/>
                    </x:WorksheetOptions>
                    </x:ExcelWorksheet>
                </x:ExcelWorksheets>
                </x:ExcelWorkbook>
                </xml>
                <![endif]-->
            </head>
            <body>
                <table>
                    <tr><td colspan="10" style="font-size: 14pt; font-weight: bold;">LAPORAN AUDIT FINANSIAL DEVELOPER PLATFORM</td></tr>
                    <tr><td colspan="10" style="font-size: 11pt; font-weight: bold; color: #1d9bf0;">BREAKDOWN: 75% WARUNG • 25% EO • FEE DEV 10% DARI 25% (= 2.5%)</td></tr>
                    <tr><td colspan="10" style="font-size: 9pt; color: #555;">Tanggal Ekspor: ${dateNow} | Sistem: JADISATU Platform</td></tr>
                    <tr><td colspan="10"></td></tr>
                    <tr style="background-color: #f2f2f2; font-weight: bold;">
                        <td colspan="2" style="border: 1px solid #000;">FEE DEVELOPER (10% DARI 25%)</td>
                        <td colspan="3" style="border: 1px solid #000;">TOTAL VOLUME GROSS</td>
                        <td colspan="3" style="border: 1px solid #000;">HAK WARUNG (75%)</td>
                        <td colspan="2" style="border: 1px solid #000;">POTONGAN EO (25%)</td>
                    </tr>
                    <tr style="font-weight: bold; font-size: 12pt;">
                        <td colspan="2" style="border: 1px solid #000; color: #1d9bf0;">${formatRupiah(stats.totalSuperAdminRevenue)}</td>
                        <td colspan="3" style="border: 1px solid #000;">${formatRupiah(stats.totalVolume)}</td>
                        <td colspan="3" style="border: 1px solid #000; color: #1d9bf0;">${formatRupiah(stats.totalVolume * 0.75)}</td>
                        <td colspan="2" style="border: 1px solid #000;">${formatRupiah(stats.totalVolume * 0.25)}</td>
                    </tr>
                    <tr><td colspan="10"></td></tr>
                    <tr style="background-color: #0f1419; color: #ffffff; font-weight: bold; text-align: center;">
                        <th style="border: 1px solid #000;">No</th>
                        <th style="border: 1px solid #000;">Invoice</th>
                        <th style="border: 1px solid #000;">Waktu</th>
                        <th style="border: 1px solid #000;">Stand Tenant</th>
                        <th style="border: 1px solid #000;">Metode</th>
                        <th style="border: 1px solid #000;">Gross (100%)</th>
                        <th style="border: 1px solid #000;">Hak Warung (75%)</th>
                        <th style="border: 1px solid #000;">Potongan EO (25%)</th>
                        <th style="border: 1px solid #000;">Fee Dev (10%)</th>
                    </tr>
                    ${txRows}
                </table>
            </body>
            </html>
            `;

            this.downloadReportFile(excelContent, `Laporan_Developer_${dateStr}.xls`, 'application/vnd.ms-excel');
        },

        // Universal File Downloader
        downloadReportFile(content, filename, mimeType) {
            const blob = new Blob(['\ufeff' + content], { type: mimeType });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = filename;
            document.body.appendChild(a);
            a.click();
            setTimeout(() => {
                document.body.removeChild(a);
                URL.revokeObjectURL(url);
            }, 500);
            this.notify('success', 'Ekspor Berhasil', `File ${filename} berhasil diunduh.`);
        },

        // EXPORT EO REPORT TO WORD (.DOC) WITH SIGNATURES
        exportAdminReportWord(customTxList = null) {
            const txList = customTxList || this.transactions;
            const event = this.getActiveEvent() || { name: 'Event Bazaar UMKM', location: '-' };
            const stats = this.getAdminReportStats();
            const dateStr = new Date().toISOString().slice(0, 10);
            const dateNow = new Date().toLocaleDateString('id-ID', {
                weekday: 'long',
                day: 'numeric',
                month: 'long',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                timeZone: WIB
            });

            const storeSummaries = this.stores.map(st => {
                const stTx = txList.filter(t => t.store_id === st.id && t.status === 'paid');
                const stGross = stTx.reduce((sum, t) => sum + t.total_amount, 0);
                const stOwner = stTx.reduce((sum, t) => sum + (t.revenue_split?.owner_share || t.total_amount * 0.75), 0);
                const stAdmin = stTx.reduce((sum, t) => sum + (t.revenue_split?.admin_gross_share || t.total_amount * 0.25), 0);
                return {
                    name: st.name,
                    booth: st.booth_number || '-',
                    count: stTx.length,
                    gross: stGross,
                    owner: stOwner,
                    admin: stAdmin
                };
            });

            const storeRows = storeSummaries.map((s, idx) => `
                <tr>
                    <td style="text-align: center; border: 1pt solid #000; padding: 4pt 6pt;">${idx + 1}</td>
                    <td style="border: 1pt solid #000; padding: 4pt 6pt; font-weight: bold;">${s.name}</td>
                    <td style="text-align: center; border: 1pt solid #000; padding: 4pt 6pt;">${s.booth}</td>
                    <td style="text-align: center; border: 1pt solid #000; padding: 4pt 6pt;">${s.count}</td>
                    <td style="text-align: right; border: 1pt solid #000; padding: 4pt 6pt; font-weight: bold;">${formatRupiah(s.gross)}</td>
                    <td style="text-align: right; border: 1pt solid #000; padding: 4pt 6pt;">${formatRupiah(s.owner)}</td>
                    <td style="text-align: right; border: 1pt solid #000; padding: 4pt 6pt;">${formatRupiah(s.admin)}</td>
                </tr>
            `).join('');

            const txRows = txList.map((t, idx) => {
                const isNoPay = t.is_without_payment || (t.status === 'rejected' && t.rejection_reason === 'Tanpa Pembayaran');
                return `
                <tr style="${t.status === 'cancelled' ? 'text-decoration: line-through; color: #777;' : ''}">
                    <td style="text-align: center; border: 1pt solid #000; padding: 4pt 5pt;">${idx + 1}</td>
                    <td style="border: 1pt solid #000; padding: 4pt 5pt; font-family: monospace; font-weight: bold;">${t.invoice_code}</td>
                    <td style="border: 1pt solid #000; padding: 4pt 5pt; font-size: 9.5pt;">${formatDateTime(t.paid_at || t.created_at)}</td>
                    <td style="border: 1pt solid #000; padding: 4pt 5pt;">${t.store_name || '-'}</td>
                    <td style="text-align: center; border: 1pt solid #000; padding: 4pt 5pt; text-transform: uppercase;">${t.payment_method}</td>
                    <td style="text-align: right; border: 1pt solid #000; padding: 4pt 5pt; font-weight: bold;">${isNoPay ? '-' : formatRupiah(t.total_amount)}</td>
                    <td style="text-align: right; border: 1pt solid #000; padding: 4pt 5pt;">${t.status === 'paid' ? formatRupiah(t.revenue_split?.owner_share || t.total_amount * 0.75) : '-'}</td>
                    <td style="text-align: right; border: 1pt solid #000; padding: 4pt 5pt;">${t.status === 'paid' ? formatRupiah(t.revenue_split?.admin_gross_share || t.total_amount * 0.25) : '-'}</td>
                    <td style="text-align: center; border: 1pt solid #000; padding: 4pt 5pt; font-weight: bold;">${isNoPay ? 'TANPA PEMBAYARAN' : t.status.toUpperCase()}</td>
                </tr>
            `}).join('');

            const logoImg = window.__LOGO_BASE64__ || window.__LOGO_URL__ || '';

            const wordContent = `
            <html xmlns:o='urn:schemas-microsoft-com:office:office' xmlns:w='urn:schemas-microsoft-com:office:word' xmlns='http://www.w3.org/TR/REC-html40'>
            <head>
                <meta charset='utf-8'>
                <title>Laporan Keuangan EO — ${event.name}</title>
                <!--[if gte mso 9]>
                <xml>
                    <w:WordDocument>
                        <w:View>Print</w:View>
                        <w:Zoom>100</w:Zoom>
                        <w:DoNotOptimizeForBrowser/>
                    </w:WordDocument>
                </xml>
                <![endif]-->
                <style>
                    @page Section1 {
                        size: 21cm 29.7cm;
                        margin: 2cm 1.8cm 2cm 1.8cm;
                        mso-header-margin: 1cm;
                        mso-footer-margin: 1cm;
                        mso-paper-source: 0;
                    }
                    div.Section1 { page: Section1; }
                    body { font-family: 'Arial', 'Calibri', sans-serif; font-size: 10pt; line-height: 1.3; color: #000; }
                    h1 { font-size: 14pt; font-weight: bold; text-align: center; text-transform: uppercase; margin: 0 0 3pt 0; }
                    h2 { font-size: 11.5pt; font-weight: bold; text-align: center; margin: 0 0 4pt 0; }
                    .meta { font-size: 9pt; text-align: center; color: #333; margin-bottom: 10pt; }
                    .section-title { font-size: 11pt; font-weight: bold; text-transform: uppercase; margin-top: 14pt; margin-bottom: 4pt; border-bottom: 1pt solid #000; padding-bottom: 2pt; }
                    table { width: 100%; border-collapse: collapse; margin-bottom: 12pt; font-size: 9.5pt; }
                    th { border: 1pt solid #000; background-color: #eee; padding: 4pt 5pt; font-weight: bold; text-align: left; text-transform: uppercase; font-size: 9pt; }
                    td { border: 1pt solid #000; padding: 4pt 5pt; vertical-align: top; }
                    .sig-table { width: 100%; border: none; margin-top: 24pt; }
                    .sig-table td { border: none; width: 50%; text-align: center; vertical-align: top; }
                    .sig-space { height: 48pt; }
                    .footer-note { font-size: 8.5pt; text-align: center; color: #555; border-top: 1pt dashed #000; padding-top: 6pt; margin-top: 16pt; }
                </style>
            </head>
            <body>
                <div class="Section1">
                    <div style="border-bottom: 2pt double #000; padding-bottom: 6pt; margin-bottom: 10pt;">
                        <table style="width: 100%; border: none;">
                            <tr>
                                ${logoImg ? `
                                    <td style="width: 60pt; border: none; text-align: left; vertical-align: middle; padding: 0;">
                                        <img src="${logoImg}" style="height: 45pt; width: auto;" width="60" height="45">
                                    </td>
                                ` : ''}
                                <td style="border: none; text-align: ${logoImg ? 'left' : 'center'}; vertical-align: middle; padding-left: 8pt;">
                                    <h1 style="text-align: ${logoImg ? 'left' : 'center'};">Laporan Rekapitulasi Keuangan & Bagi Hasil Event</h1>
                                    <h2 style="text-align: ${logoImg ? 'left' : 'center'};">${event.name} &bull; ${event.location || '-'}</h2>
                                    <div class="meta" style="text-align: ${logoImg ? 'left' : 'center'};">
                                        Tanggal Cetak: <strong>${dateNow}</strong> &bull; Penyelenggara: <strong>Panitia EO</strong> &bull; Sistem: <strong>JADISATU Event</strong>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <div class="section-title">1. Ringkasan Eksekutif Finansial</div>
                    <table>
                        <tr>
                            <td style="width: 33.3%;">
                                <div style="font-size: 8.5pt; font-weight: bold; text-transform: uppercase; color: #444;">Total Omzet Bruto</div>
                                <div style="font-size: 12pt; font-weight: bold; margin-top: 2pt;">${formatRupiah(stats.totalGross)}</div>
                                <div style="font-size: 8.5pt; color: #555;">${stats.paidCount} Transaksi Paid</div>
                            </td>
                            <td style="width: 33.3%;">
                                <div style="font-size: 8.5pt; font-weight: bold; text-transform: uppercase; color: #444;">Porsi Stand/Warung (75%)</div>
                                <div style="font-size: 12pt; font-weight: bold; margin-top: 2pt;">${formatRupiah(stats.ownerTotal)}</div>
                                <div style="font-size: 8.5pt; color: #555;">Hak Seluruh Tenant</div>
                            </td>
                            <td style="width: 33.3%; background-color: #f2f2f2;">
                                <div style="font-size: 8.5pt; font-weight: bold; text-transform: uppercase; color: #444;">Bagian EO (25%)</div>
                                <div style="font-size: 12pt; font-weight: bold; margin-top: 2pt;">${formatRupiah(stats.adminGross)}</div>
                                <div style="font-size: 8.5pt; color: #555;">Total Porsi Penyelenggara</div>
                            </td>
                        </tr>
                    </table>

                    <div class="section-title">2. Rekapitulasi Pendapatan per Stand / Warung</div>
                    <table>
                        <thead>
                            <tr>
                                <th style="text-align: center; width: 25pt;">No</th>
                                <th>Nama Stand / Warung</th>
                                <th style="text-align: center; width: 55pt;">Stand</th>
                                <th style="text-align: center; width: 45pt;">Tx Paid</th>
                                <th style="text-align: right; width: 75pt;">Total Omzet</th>
                                <th style="text-align: right; width: 75pt;">Hak Warung (75%)</th>
                                <th style="text-align: right; width: 75pt;">Potongan EO (25%)</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${storeRows.length > 0 ? storeRows : '<tr><td colspan="7" style="text-align: center; padding: 6pt;">Belum ada data stand</td></tr>'}
                        </tbody>
                    </table>

                    <div class="section-title">3. Rincian Seluruh Transaksi (${txList.length} Transaksi)</div>
                    <table>
                        <thead>
                            <tr>
                                <th style="text-align: center; width: 20pt;">No</th>
                                <th style="width: 80pt;">Invoice</th>
                                <th style="width: 70pt;">Waktu</th>
                                <th>Stand Warung</th>
                                <th style="text-align: center; width: 40pt;">Metode</th>
                                <th style="text-align: right; width: 65pt;">Nominal</th>
                                <th style="text-align: right; width: 65pt;">Warung (75%)</th>
                                <th style="text-align: right; width: 65pt;">Bagian EO (25%)</th>
                                <th style="text-align: center; width: 45pt;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${txRows.length > 0 ? txRows : '<tr><td colspan="9" style="text-align: center; padding: 6pt;">Belum ada transaksi</td></tr>'}
                        </tbody>
                    </table>

                    <table class="sig-table">
                        <tr>
                            <td>
                                <div>Dibuat & Divalidasi Oleh:</div>
                                <div style="font-weight: bold; margin-top: 2pt;">Admin Event Organizer</div>
                                <div class="sig-space"></div>
                                <div>( __________________________ )</div>
                                <div style="font-size: 8.5pt; color: #555; margin-top: 2pt;">Panitia Pelaksana EO</div>
                            </td>
                            <td>
                                <div>Mengetahui & Menyetujui:</div>
                                <div style="font-weight: bold; margin-top: 2pt;">Penanggung Jawab / Ketua EO</div>
                                <div class="sig-space"></div>
                                <div>( __________________________ )</div>
                                <div style="font-size: 8.5pt; color: #555; margin-top: 2pt;">Event Organizer Lead</div>
                            </td>
                        </tr>
                    </table>

                    <div class="footer-note">
                        Dokumen ini digenerate secara otomatis oleh Sistem JADISATU &bull; Berlaku sah untuk pertanggungjawaban panitia EO dan rekonsiliasi bagi hasil.
                    </div>
                </div>
            </body>
            </html>
            `;

            this.downloadReportFile(wordContent, `Laporan_EO_${event.name.replace(/[^a-zA-Z0-9]/g, '_')}_${dateStr}.doc`, 'application/msword');
        },

        // EXPORT EO REPORT TO EXCEL (.XLS SPREADSHEET)
        exportAdminReportExcel(customTxList = null) {
            const txList = customTxList || this.transactions;
            const event = this.getActiveEvent() || { name: 'Event Bazaar UMKM', location: '-' };
            const stats = this.getAdminReportStats();
            const dateStr = new Date().toISOString().slice(0, 10);
            const dateNow = new Date().toLocaleDateString('id-ID', {
                day: 'numeric',
                month: 'long',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });

            const storeSummaries = this.stores.map(st => {
                const stTx = txList.filter(t => t.store_id === st.id && t.status === 'paid');
                const stGross = stTx.reduce((sum, t) => sum + t.total_amount, 0);
                const stOwner = stTx.reduce((sum, t) => sum + (t.revenue_split?.owner_share || t.total_amount * 0.75), 0);
                const stAdmin = stTx.reduce((sum, t) => sum + (t.revenue_split?.admin_gross_share || t.total_amount * 0.25), 0);
                return {
                    name: st.name,
                    booth: st.booth_number || '-',
                    count: stTx.length,
                    gross: stGross,
                    owner: stOwner,
                    admin: stAdmin
                };
            });

            let storeRowsXml = storeSummaries.map((s, idx) => `
                <tr>
                    <td style="text-align: center; border: 1px solid #cbd5e1;">${idx + 1}</td>
                    <td style="border: 1px solid #cbd5e1; font-weight: bold;">${s.name}</td>
                    <td style="text-align: center; border: 1px solid #cbd5e1;">${s.booth}</td>
                    <td style="text-align: center; border: 1px solid #cbd5e1;">${s.count}</td>
                    <td style="text-align: right; border: 1px solid #cbd5e1; font-weight: bold;">${s.gross}</td>
                    <td style="text-align: right; border: 1px solid #cbd5e1;">${s.owner}</td>
                    <td style="text-align: right; border: 1px solid #cbd5e1;">${s.admin}</td>
                </tr>
            `).join('');

            let txRowsXml = txList.map((t, idx) => {
                const isNoPay = t.is_without_payment || (t.status === 'rejected' && t.rejection_reason === 'Tanpa Pembayaran');
                return `
                <tr>
                    <td style="text-align: center; border: 1px solid #cbd5e1;">${idx + 1}</td>
                    <td style="border: 1px solid #cbd5e1; font-family: monospace; font-weight: bold;">${t.invoice_code}</td>
                    <td style="border: 1px solid #cbd5e1;">${formatDateTime(t.paid_at || t.created_at)}</td>
                    <td style="border: 1px solid #cbd5e1;">${t.store_name || '-'}</td>
                    <td style="text-align: center; border: 1px solid #cbd5e1; text-transform: uppercase;">${t.payment_method}</td>
                    <td style="text-align: right; border: 1px solid #cbd5e1;">${isNoPay ? 0 : t.total_amount}</td>
                    <td style="text-align: right; border: 1px solid #cbd5e1;">${t.status === 'paid' ? (t.revenue_split?.owner_share || t.total_amount * 0.75) : 0}</td>
                    <td style="text-align: right; border: 1px solid #cbd5e1;">${t.status === 'paid' ? (t.revenue_split?.admin_gross_share || t.total_amount * 0.25) : 0}</td>
                    <td style="text-align: center; border: 1px solid #cbd5e1; font-weight: bold;">${isNoPay ? 'TANPA PEMBAYARAN' : t.status.toUpperCase()}</td>
                </tr>
            `}).join('');

            const excelContent = `
            <html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
            <head>
                <meta charset="utf-8">
                <!--[if gte mso 9]>
                <xml>
                    <x:ExcelWorkbook>
                        <x:ExcelWorksheets>
                            <x:ExcelWorksheet>
                                <x:Name>Laporan EO</x:Name>
                                <x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions>
                            </x:ExcelWorksheet>
                        </x:ExcelWorksheets>
                    </x:ExcelWorkbook>
                </xml>
                <![endif]-->
                <style>
                    body { font-family: Calibri, Arial, sans-serif; font-size: 11pt; }
                    th { background-color: #e2e8f0; font-weight: bold; border: 1px solid #94a3b8; padding: 6px 8px; text-align: left; }
                    td { border: 1px solid #cbd5e1; padding: 5px 8px; vertical-align: middle; }
                </style>
            </head>
            <body>
                <table>
                    <tr>
                        <td colspan="7" style="font-size: 14pt; font-weight: bold;">LAPORAN REKAPITULASI KEUANGAN & BAGI HASIL EVENT</td>
                    </tr>
                    <tr>
                        <td colspan="7" style="font-size: 12pt; font-weight: bold; color: #1e293b;">${event.name}</td>
                    </tr>
                    <tr>
                        <td colspan="7" style="font-size: 10pt; color: #475569;">Lokasi: ${event.location || '-'} | Tanggal Ekspor: ${dateNow} | Sistem: JADISATU Event</td>
                    </tr>
                    <tr><td colspan="7"></td></tr>
                    <tr style="background-color: #f1f5f9;">
                        <th colspan="7" style="font-size: 11pt;">1. RINGKASAN EKSEKUTIF FINANSIAL</th>
                    </tr>
                    <tr>
                        <td colspan="2" style="font-weight: bold;">Total Omzet Bruto:</td>
                        <td colspan="5" style="font-weight: bold;">${stats.totalGross} (${stats.paidCount} Transaksi Paid)</td>
                    </tr>
                    <tr>
                        <td colspan="2" style="font-weight: bold;">Hak Stand / Warung (75%):</td>
                        <td colspan="5">${stats.ownerTotal}</td>
                    </tr>
                    <tr style="background-color: #f8fafc;">
                        <td colspan="2" style="font-weight: bold; color: #0284c7;">Bagian EO (25%):</td>
                        <td colspan="5" style="font-weight: bold; color: #0284c7;">${stats.adminGross}</td>
                    </tr>
                    <tr><td colspan="7"></td></tr>
                    <tr style="background-color: #f1f5f9;">
                        <th colspan="7" style="font-size: 11pt;">2. REKAPITULASI PER STAND / WARUNG</th>
                    </tr>
                    <tr>
                        <th style="text-align: center; width: 40px;">No</th>
                        <th>Nama Stand / Warung</th>
                        <th style="text-align: center;">No Booth</th>
                        <th style="text-align: center;">Tx Paid</th>
                        <th style="text-align: right;">Total Omzet (Rp)</th>
                        <th style="text-align: right;">Hak Warung 75% (Rp)</th>
                        <th style="text-align: right;">Potongan EO 25% (Rp)</th>
                    </tr>
                    ${storeRowsXml}
                    <tr><td colspan="7"></td></tr>
                    <tr style="background-color: #f1f5f9;">
                        <th colspan="9" style="font-size: 11pt;">3. RINCIAN DATA TRANSAKSI</th>
                    </tr>
                    <tr>
                        <th style="text-align: center; width: 40px;">No</th>
                        <th>Invoice</th>
                        <th>Waktu</th>
                        <th>Stand Warung</th>
                        <th style="text-align: center;">Metode</th>
                        <th style="text-align: right;">Total Belanja (Rp)</th>
                        <th style="text-align: right;">Warung 75% (Rp)</th>
                        <th style="text-align: right;">Bagian EO 25% (Rp)</th>
                        <th style="text-align: center;">Status</th>
                    </tr>
                    ${txRowsXml}
                </table>
            </body>
            </html>
            `;

            this.downloadReportFile(excelContent, `Laporan_EO_${event.name.replace(/[^a-zA-Z0-9]/g, '_')}_${dateStr}.xls`, 'application/vnd.ms-excel');
        },

        // EXPORT USER REPORT TO WORD (.DOC) (STANDARDIZED TEMPLATE, TANPA TTD)
        exportUserReportWord(customTxList = null) {
            const store = this.getCurrentStore() || { id: 1, name: 'Stand Warung', booth_number: '-' };
            const event = this.getActiveEvent() || { name: 'Event Bazaar UMKM', location: '-' };
            const stats = this.getUserReportStats(store.id);
            const txList = customTxList || this.transactions.filter(t => t.store_id === store.id);
            const logoImg = window.__LOGO_BASE64__ || window.__LOGO_URL__ || '';
            const dateStr = new Date().toISOString().slice(0, 10);
            const dateNow = new Date().toLocaleDateString('id-ID', {
                weekday: 'long',
                day: 'numeric',
                month: 'long',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                timeZone: WIB
            });

            const txRows = txList.map((t, idx) => {
                const isNoPay = t.is_without_payment || (t.status === 'rejected' && t.rejection_reason === 'Tanpa Pembayaran');
                return `
                <tr style="${t.status === 'cancelled' ? 'text-decoration: line-through; color: #777;' : ''}">
                    <td style="text-align: center; border: 1pt solid #000; padding: 4pt 5pt;">${idx + 1}</td>
                    <td style="border: 1pt solid #000; padding: 4pt 5pt; font-family: monospace; font-weight: bold;">${t.invoice_code}</td>
                    <td style="border: 1pt solid #000; padding: 4pt 5pt; font-size: 9.5pt;">${formatDateTime(t.paid_at || t.created_at)}</td>
                    <td style="text-align: center; border: 1pt solid #000; padding: 4pt 5pt; text-transform: uppercase;">${t.payment_method}</td>
                    <td style="text-align: right; border: 1pt solid #000; padding: 4pt 5pt; font-weight: bold;">${isNoPay ? '-' : formatRupiah(t.total_amount)}</td>
                    <td style="text-align: right; border: 1pt solid #000; padding: 4pt 5pt; font-weight: bold;">${t.status === 'paid' ? formatRupiah(t.revenue_split?.owner_share || t.total_amount * 0.75) : '-'}</td>
                    <td style="text-align: right; border: 1pt solid #000; padding: 4pt 5pt; font-weight: bold;">${t.status === 'paid' ? formatRupiah(t.revenue_split?.admin_gross_share || t.total_amount * 0.25) : '-'}</td>
                    <td style="text-align: center; border: 1pt solid #000; padding: 4pt 5pt; font-weight: bold;">${isNoPay ? 'TANPA PEMBAYARAN' : t.status.toUpperCase()}</td>
                </tr>
            `}).join('');

            const wordContent = `
            <html xmlns:o='urn:schemas-microsoft-com:office:office' xmlns:w='urn:schemas-microsoft-com:office:word' xmlns='http://www.w3.org/TR/REC-html40'>
            <head>
                <meta charset='utf-8'>
                <title>Laporan Penjualan Stand — ${store.name}</title>
                <!--[if gte mso 9]>
                <xml>
                    <w:WordDocument>
                        <w:View>Print</w:View>
                        <w:Zoom>100</w:Zoom>
                        <w:DoNotOptimizeForBrowser/>
                    </w:WordDocument>
                </xml>
                <![endif]-->
                <style>
                    @page Section1 {
                        size: 21cm 29.7cm;
                        margin: 2cm 1.8cm 2cm 1.8cm;
                    }
                    div.Section1 { page: Section1; }
                    body { font-family: 'Arial', 'Calibri', sans-serif; font-size: 10pt; line-height: 1.3; color: #000; }
                    h1 { font-size: 14pt; font-weight: bold; text-align: center; text-transform: uppercase; margin: 0 0 3pt 0; }
                    h2 { font-size: 11.5pt; font-weight: bold; text-align: center; margin: 0 0 4pt 0; }
                    .meta { font-size: 9pt; text-align: center; color: #333; margin-bottom: 10pt; }
                    .section-title { font-size: 11pt; font-weight: bold; text-transform: uppercase; margin-top: 14pt; margin-bottom: 4pt; border-bottom: 1pt solid #000; padding-bottom: 2pt; }
                    table { width: 100%; border-collapse: collapse; margin-bottom: 12pt; font-size: 9.5pt; }
                    th { border: 1pt solid #000; background-color: #eee; padding: 4pt 5pt; font-weight: bold; text-align: left; text-transform: uppercase; font-size: 9pt; }
                    td { border: 1pt solid #000; padding: 4pt 5pt; vertical-align: top; }
                    .footer-note { font-size: 8.5pt; text-align: center; color: #555; border-top: 1pt dashed #000; padding-top: 6pt; margin-top: 20pt; }
                </style>
            </head>
            <body>
                <div class="Section1">
                    <div style="border-bottom: 2pt double #000; padding-bottom: 6pt; margin-bottom: 10pt;">
                        <table style="width: 100%; border: none;">
                            <tr>
                                ${logoImg ? `
                                    <td style="width: 60pt; border: none; text-align: left; vertical-align: middle; padding: 0;">
                                        <img src="${logoImg}" style="height: 45pt; width: auto;" width="60" height="45">
                                    </td>
                                ` : ''}
                                <td style="border: none; text-align: ${logoImg ? 'left' : 'center'}; vertical-align: middle; padding-left: 8pt;">
                                    <h1 style="text-align: ${logoImg ? 'left' : 'center'};">Laporan Penjualan & Bagi Hasil Stand</h1>
                                    <h2 style="text-align: ${logoImg ? 'left' : 'center'};">${store.name} (${store.booth_number || 'Stand Tenant'}) &bull; ${event.name}</h2>
                                    <div class="meta" style="text-align: ${logoImg ? 'left' : 'center'};">
                                        Tanggal Cetak: <strong>${dateNow}</strong> &bull; Lokasi: <strong>${event.location || '-'}</strong> &bull; Sistem: <strong>JADISATU Event</strong>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <div class="section-title">1. Ringkasan Omzet Stand</div>
                    <table>
                        <tr>
                            <td style="width: 33.3%;">
                                <div style="font-size: 8.5pt; font-weight: bold; text-transform: uppercase; color: #444;">Total Omzet Bruto</div>
                                <div style="font-size: 12pt; font-weight: bold; margin-top: 2pt;">${formatRupiah(stats.totalGross)}</div>
                                <div style="font-size: 8.5pt; color: #555;">${stats.totalCount} Transaksi Paid</div>
                            </td>
                            <td style="width: 33.3%; background-color: #f2f2f2;">
                                <div style="font-size: 8.5pt; font-weight: bold; text-transform: uppercase; color: #444;">Hak Pemilik Stand (75%)</div>
                                <div style="font-size: 12pt; font-weight: bold; margin-top: 2pt;">${formatRupiah(stats.netIncome)}</div>
                                <div style="font-size: 8.5pt; color: #555;">Pendapatan Bersih Warung</div>
                            </td>
                            <td style="width: 33.3%;">
                                <div style="font-size: 8.5pt; font-weight: bold; text-transform: uppercase; color: #444;">Potongan Platform & EO (25%)</div>
                                <div style="font-size: 12pt; font-weight: bold; margin-top: 2pt;">${formatRupiah(stats.totalGross * 0.25)}</div>
                                <div style="font-size: 8.5pt; color: #555;">Penyelenggara & Platform</div>
                            </td>
                        </tr>
                    </table>

                    <div class="section-title">2. Rincian Riwayat Transaksi Stand</div>
                    <table>
                        <thead>
                            <tr>
                                <th style="text-align: center; width: 20pt;">No</th>
                                <th style="width: 80pt;">Invoice</th>
                                <th style="width: 70pt;">Waktu</th>
                                <th style="text-align: center; width: 45pt;">Metode</th>
                                <th style="text-align: right; width: 70pt;">Total Belanja</th>
                                <th style="text-align: right; width: 70pt;">Hak Warung (75%)</th>
                                <th style="text-align: right; width: 70pt;">Potongan EO (25%)</th>
                                <th style="text-align: center; width: 50pt;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${txRows.length > 0 ? txRows : '<tr><td colspan="8" style="text-align: center; padding: 6pt;">Belum ada transaksi</td></tr>'}
                        </tbody>
                    </table>

                    <div class="footer-note">
                        Dokumen dicetak otomatis dari Sistem JADISATU &bull; Berlaku sah sebagai rekapitulasi penjualan stand.
                    </div>
                </div>
            </body>
            </html>
            `;

            this.downloadReportFile(wordContent, `Laporan_Stand_${store.name.replace(/[^a-zA-Z0-9]/g, '_')}_${dateStr}.doc`, 'application/msword');
        },

        // EXPORT USER REPORT TO EXCEL (.XLS)
        exportUserReportExcel(customTxList = null) {
            const store = this.getCurrentStore() || { id: 1, name: 'Stand Warung', booth_number: '-' };
            const event = this.getActiveEvent() || { name: 'Event Bazaar UMKM', location: '-' };
            const stats = this.getUserReportStats(store.id);
            const txList = customTxList || this.transactions.filter(t => t.store_id === store.id);
            const dateStr = new Date().toISOString().slice(0, 10);
            const dateNow = new Date().toLocaleDateString('id-ID', {
                day: 'numeric',
                month: 'long',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });

            let txRowsXml = txList.map((t, idx) => {
                const isNoPay = t.is_without_payment || (t.status === 'rejected' && t.rejection_reason === 'Tanpa Pembayaran');
                return `
                <tr>
                    <td style="text-align: center; border: 1px solid #cbd5e1;">${idx + 1}</td>
                    <td style="border: 1px solid #cbd5e1; font-family: monospace; font-weight: bold;">${t.invoice_code}</td>
                    <td style="border: 1px solid #cbd5e1;">${formatDateTime(t.paid_at || t.created_at)}</td>
                    <td style="text-align: center; border: 1px solid #cbd5e1; text-transform: uppercase;">${t.payment_method}</td>
                    <td style="text-align: right; border: 1px solid #cbd5e1;">${isNoPay ? 0 : t.total_amount}</td>
                    <td style="text-align: right; border: 1px solid #cbd5e1;">${t.status === 'paid' ? (t.revenue_split?.owner_share || t.total_amount * 0.75) : 0}</td>
                    <td style="text-align: right; border: 1px solid #cbd5e1;">${t.status === 'paid' ? (t.revenue_split?.admin_gross_share || t.total_amount * 0.25) : 0}</td>
                    <td style="text-align: center; border: 1px solid #cbd5e1; font-weight: bold;">${isNoPay ? 'TANPA PEMBAYARAN' : t.status.toUpperCase()}</td>
                </tr>
            `}).join('');

            const excelContent = `
            <html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
            <head>
                <meta charset="utf-8">
                <!--[if gte mso 9]>
                <xml>
                    <x:ExcelWorkbook>
                        <x:ExcelWorksheets>
                            <x:ExcelWorksheet>
                                <x:Name>Laporan Stand</x:Name>
                                <x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions>
                            </x:ExcelWorksheet>
                        </x:ExcelWorksheets>
                    </x:ExcelWorkbook>
                </xml>
                <![endif]-->
                <style>
                    body { font-family: Calibri, Arial, sans-serif; font-size: 11pt; }
                    th { background-color: #e2e8f0; font-weight: bold; border: 1px solid #94a3b8; padding: 6px 8px; text-align: left; }
                    td { border: 1px solid #cbd5e1; padding: 5px 8px; vertical-align: middle; }
                </style>
            </head>
            <body>
                <table>
                    <tr>
                        <td colspan="7" style="font-size: 14pt; font-weight: bold;">LAPORAN PENJUALAN STAND UMKM</td>
                    </tr>
                    <tr>
                        <td colspan="7" style="font-size: 12pt; font-weight: bold;">${store.name} (${store.booth_number}) — ${event.name}</td>
                    </tr>
                    <tr>
                        <td colspan="7" style="font-size: 10pt; color: #475569;">Lokasi: ${event.location || '-'} | Tanggal Ekspor: ${dateNow} | Sistem: JADISATU Event</td>
                    </tr>
                    <tr><td colspan="7"></td></tr>
                    <tr style="background-color: #f1f5f9;">
                        <th colspan="7" style="font-size: 11pt;">1. RINGKASAN OMZET STAND</th>
                    </tr>
                    <tr>
                        <td colspan="2" style="font-weight: bold;">Total Omzet Bruto:</td>
                        <td colspan="5" style="font-weight: bold;">${stats.totalGross} (${stats.totalCount} Tx Paid)</td>
                    </tr>
                    <tr style="background-color: #f8fafc;">
                        <td colspan="2" style="font-weight: bold; color: #0284c7;">Hak Pemilik Stand (75% Net):</td>
                        <td colspan="5" style="font-weight: bold; color: #0284c7;">${stats.netIncome}</td>
                    </tr>
                    <tr>
                        <td colspan="2" style="font-weight: bold;">Potongan Platform & EO (25%):</td>
                        <td colspan="5">${stats.totalGross * 0.25}</td>
                    </tr>
                    <tr><td colspan="7"></td></tr>
                    <tr style="background-color: #f1f5f9;">
                        <th colspan="8" style="font-size: 11pt;">2. RINCIAN DATA TRANSAKSI</th>
                    </tr>
                    <tr>
                        <th style="text-align: center; width: 40px;">No</th>
                        <th>Invoice</th>
                        <th>Waktu</th>
                        <th style="text-align: center;">Metode</th>
                        <th style="text-align: right;">Total Belanja (Rp)</th>
                        <th style="text-align: right;">Hak Warung 75% (Rp)</th>
                        <th style="text-align: right;">Potongan EO 25% (Rp)</th>
                        <th style="text-align: center;">Status</th>
                    </tr>
                    ${txRowsXml}
                </table>
            </body>
            </html>
            `;

            this.downloadReportFile(excelContent, `Laporan_Stand_${store.name.replace(/[^a-zA-Z0-9]/g, '_')}_${dateStr}.xls`, 'application/vnd.ms-excel');
        },

        // Helper calculations for reports
        getUserReportStats(storeId = null) {
            const txs = this.transactions || [];
            const validTx = txs.filter(t => (storeId ? t.store_id == storeId : true) && t.status === 'paid');
            const totalGross = validTx.reduce((sum, t) => sum + (t.total_amount || 0), 0);
            const netIncome = totalGross * 0.75;
            const totalCount = validTx.length;
            const cancelledCount = txs.filter(t => (storeId ? t.store_id == storeId : true) && ['cancelled', 'rejected'].includes(t.status)).length;
            const pendingCount = txs.filter(t => (storeId ? t.store_id == storeId : true) && ['pending', 'pending_verification'].includes(t.status)).length;

            return {
                totalGross: totalGross || 0,
                netIncome: netIncome || 0,
                totalPotongan: (totalGross * 0.25) || 0,
                totalCount: totalCount || 0,
                cancelledCount: cancelledCount || 0,
                pendingCount: pendingCount || 0
            };
        },

        getAdminReportStats() {
            const paidTx = this.transactions.filter(t => t.status === 'paid');
            const totalGross = paidTx.reduce((sum, t) => sum + t.total_amount, 0);
            const ownerTotal = paidTx.reduce((sum, t) => sum + (t.revenue_split?.owner_share || t.total_amount * 0.75), 0);
            const adminGross = paidTx.reduce((sum, t) => sum + (t.revenue_split?.admin_gross_share || t.total_amount * 0.25), 0);
            const superadminTotal = paidTx.reduce((sum, t) => sum + (t.revenue_split?.superadmin_share || (t.total_amount * 0.025)), 0);
            const adminNet = paidTx.reduce((sum, t) => sum + (t.revenue_split?.admin_net_share || t.total_amount * 0.225), 0);
            
            // Cash vs QRIS breakdown
            const cashTx = paidTx.filter(t => t.payment_method === 'cash');
            const qrisTx = paidTx.filter(t => t.payment_method === 'qris');
            const totalCash = cashTx.reduce((sum, t) => sum + t.total_amount, 0);
            const totalQris = qrisTx.reduce((sum, t) => sum + t.total_amount, 0);

            // Settlement: seluruh uang dipegang admin.
            // Cash disetor ke kasir admin saat verifikasi, QRIS masuk rekening admin.
            // Jadi admin membayar hak warung SECARA PENUH (75% dari seluruh omzet),
            // bukan sisa hasil offset cash vs QRIS seperti alur lama.
            const qrisHakWarung = qrisTx.reduce((sum, t) => sum + (t.revenue_split?.owner_share || t.total_amount * 0.75), 0);
            const cashHakWarung = cashTx.reduce((sum, t) => sum + (t.revenue_split?.owner_share || t.total_amount * 0.75), 0);
            const netSettlement = ownerTotal;

            const pendingCashCount = this.transactions.filter(t => t.status === 'pending' && t.payment_method === 'cash').length;
            const pendingCount = pendingCashCount;
            const cancelledCount = this.transactions.filter(t => t.status === 'cancelled').length;

            return {
                totalGross,
                ownerTotal,
                adminGross,
                superadminTotal,
                adminNet,
                totalCash,
                totalQris,
                cashCount: cashTx.length,
                qrisCount: qrisTx.length,
                qrisHakWarung,
                cashHakWarung,
                netSettlement,
                paidCount: paidTx.length,
                pendingCount,
                pendingCashCount,
                cancelledCount,
                storesCount: this.stores.length
            };
        },

        // Settlement breakdown per individual store
        getSettlementPerStore() {
            const paidTx = this.transactions.filter(t => t.status === 'paid');
            const storeMap = {};

            paidTx.forEach(t => {
                const sid = t.store_id;
                if (!storeMap[sid]) {
                    storeMap[sid] = {
                        store_id: sid,
                        store_name: t.store_name || 'Unknown',
                        totalGross: 0,
                        totalCash: 0,
                        totalQris: 0,
                        hakWarung: 0,
                        hakAdmin: 0,
                        qrisHakWarung: 0,
                        cashHakWarung: 0,
                        txCount: 0
                    };
                }

                const s = storeMap[sid];
                const ownerShare = t.revenue_split?.owner_share || t.total_amount * 0.75;
                const adminShare = t.revenue_split?.admin_gross_share || t.total_amount * 0.25;

                s.totalGross += t.total_amount;
                s.hakWarung += ownerShare;
                s.hakAdmin += adminShare;
                s.txCount++;

                if (t.payment_method === 'cash') {
                    s.totalCash += t.total_amount;
                    s.cashHakWarung += ownerShare;
                } else if (t.payment_method === 'qris') {
                    s.totalQris += t.total_amount;
                    s.qrisHakWarung += ownerShare;
                }
            });

            // Seluruh uang (cash maupun QRIS) ada di admin, jadi yang harus
            // ditransfer ke warung adalah hak warung penuh — bukan sisa offset.
            return Object.values(storeMap).map(s => ({
                ...s,
                netSettlement: s.hakWarung,
                cashDipegang: s.totalCash,   // cash yang sudah disetor ke kasir admin
                qrisDipegang: s.totalQris     // uang di rekening admin
            })).sort((a, b) => b.totalGross - a.totalGross);
        },

        getSuperAdminStats() {
            const paidTx = this.transactions.filter(t => t.status === 'paid');
            const totalVolume = paidTx.reduce((sum, t) => sum + t.total_amount, 0);
            const totalSuperAdminRevenue = paidTx.reduce((sum, t) => sum + (t.revenue_split?.superadmin_share || (t.total_amount * 0.025)), 0);
            const ownerTotal = paidTx.reduce((sum, t) => sum + (t.revenue_split?.owner_share || t.total_amount * 0.75), 0);
            const potonganTotal = paidTx.reduce((sum, t) => sum + (t.revenue_split?.admin_gross_share || t.total_amount * 0.25), 0);
            const totalEvents = this.events.length;
            const activeEvent = this.getActiveEvent();

            return {
                totalVolume,
                totalSuperAdminRevenue,
                ownerTotal,
                potonganTotal,
                totalEvents,
                paidCount: paidTx.length,
                activeEventName: activeEvent ? activeEvent.name : '-'
            };
        },

        // Testing Mode State & Actions
        resetTestingModalOpen: false,
        resetTestingEventTarget: null,

        openResetTestingModal(event = null) {
            this.resetTestingEventTarget = event || this.getActiveEvent();
            this.resetTestingModalOpen = true;
        },

        async toggleEventTesting(eventId = null) {
            const targetEvent = eventId ? (this.events.find(e => e.id == eventId) || this.getActiveEvent()) : this.getActiveEvent();
            if (!targetEvent) {
                showSwal('warn', 'Event Tidak Ditemukan', 'Harap pilih event terlebih dahulu.');
                return;
            }

            const rolePrefix = this.currentRole === 'superadmin' ? 'superadmin' : 'admin';
            try {
                this.showLoading('Mengubah status Masa Testing...');
                const res = await apiFetch(`/${rolePrefix}/events/${targetEvent.id}/toggle-testing`, {
                    method: 'POST',
                    body: { is_testing_mode: !targetEvent.is_testing_mode }
                });

                if (res.success) {
                    targetEvent.is_testing_mode = res.is_testing_mode;
                    if (this.activeEvent && this.activeEvent.id == targetEvent.id) {
                        this.activeEvent.is_testing_mode = res.is_testing_mode;
                    }
                    showSwal('success', 'Status Berubah', res.message);
                } else {
                    showSwal('danger', 'Gagal', res.message || 'Gagal mengubah mode testing.');
                }
            } catch (err) {
                showSwal('danger', 'Kesalahan', err.message || 'Terjadi kesalahan saat memproses permintaan.');
            } finally {
                this.hideLoading();
            }
        },

        async confirmResetTesting() {
            const targetEvent = this.resetTestingEventTarget || this.getActiveEvent();
            if (!targetEvent) return;

            const rolePrefix = this.currentRole === 'superadmin' ? 'superadmin' : 'admin';
            this.resetTestingModalOpen = false;

            try {
                this.showLoading('Membersihkan seluruh data transaksi testing...');
                const res = await apiFetch(`/${rolePrefix}/events/${targetEvent.id}/reset-testing`, {
                    method: 'POST'
                });

                if (res.success) {
                    // Filter out testing transactions from frontend store state
                    this.transactions = this.transactions.filter(t => !t.is_testing);
                    showSwal('success', 'Berhasil Direset', res.message);
                    setTimeout(() => {
                        window.location.reload();
                    }, 1200);
                } else {
                    showSwal('danger', 'Gagal Reset', res.message || 'Terjadi kesalahan saat reset transaksi.');
                }
            } catch (err) {
                showSwal('danger', 'Kesalahan', err.message || 'Terjadi kesalahan pada server.');
            } finally {
                this.hideLoading();
            }
        }
    });

Alpine.start();
