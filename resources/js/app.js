import './bootstrap';
import Alpine from 'alpinejs';
import Chart from 'chart.js/auto';
import { initialEvents, initialUsers, initialStores, initialProducts, initialTransactions, initialHelpdeskTickets, staticQrisData } from './dummy-data';
import { evaluatePasswordStrength } from './password-meter';

window.Alpine = Alpine;
window.Chart = Chart;
window.evaluatePasswordStrength = evaluatePasswordStrength;

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

export const formatNumber = (number) => {
    if (!number || isNaN(number)) return '0';
    return new Intl.NumberFormat('id-ID').format(number);
};

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
            minute: '2-digit'
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
        activeEvent: window.__ACTIVE_EVENT__ || null,
        staticQris: staticQrisData,

        // Real database data from Laravel backend
        events: window.__INITIAL_EVENTS__ || [],
        stores: window.__INITIAL_STORES__ || [],
        products: window.__INITIAL_PRODUCTS__ || [],
        transactions: window.__INITIAL_TRANSACTIONS__ || [],
        helpdesk: window.__INITIAL_HELPDESK__ || [],

        // Cart state for User POS
        cart: [],
        
        // Active UI & Modals State
        isCartOpen: false,
        isCheckoutOpen: false,
        activePaymentTab: 'cash', // 'cash' or 'qris'
        cashAmountPaid: '',
        qrisProofPreview: null,
        qrisProofFile: null,
        
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
            category: 'Makanan',
            description: '',
            photo: '',
            stock_badge: 'Tersedia'
        },
        deleteProductConfirmOpen: false,
        productToDelete: null,

        // Event Management Modal for Super Admin
        eventModalOpen: false,
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
            if (window.__INITIAL_PRODUCTS__) this.products = window.__INITIAL_PRODUCTS__;
            if (window.__INITIAL_TRANSACTIONS__) this.transactions = window.__INITIAL_TRANSACTIONS__;
            if (window.__INITIAL_HELPDESK__) this.helpdesk = window.__INITIAL_HELPDESK__;
        },

        getRoleLabel(role) {
            if (role === 'user') return 'Pemilik Warung (User)';
            if (role === 'admin') return 'Admin EO';
            if (role === 'superadmin') return 'Super Admin Platform';
            return role;
        },

        getCurrentUser() {
            return this.currentUser || window.__AUTH_USER__ || { name: 'User', email: '', role: this.currentRole };
        },

        getActiveEvent() {
            return this.activeEvent || window.__ACTIVE_EVENT__ || { name: 'Event Belum Aktif' };
        },

        getCurrentStore() {
            const user = this.getCurrentUser();
            if (user && user.store_name) {
                return {
                    id: user.store_id,
                    name: user.store_name,
                    booth_number: user.booth_number || 'Stand A-01'
                };
            }
            return this.stores[0] || null;
        },

        // Toast notifications
        notify(type = 'success', title = 'Pemberitahuan', message = '') {
            const id = Date.now() + Math.random().toString(36).substring(2, 6);
            this.toasts.push({ id, type, title, message });
            setTimeout(() => {
                this.removeToast(id);
            }, 4500);
        },

        removeToast(id) {
            this.toasts = this.toasts.filter(t => t.id !== id);
        },

        // CART MANAGEMENT (for User POS)
        addToCart(product) {
            const existing = this.cart.find(item => item.product.id === product.id);
            if (existing) {
                existing.qty++;
            } else {
                this.cart.push({
                    product,
                    qty: 1,
                    notes: ''
                });
            }
            this.notify('success', 'Produk Ditambahkan', `${product.title} (x1) masuk keranjang`);
        },

        updateCartQty(productId, delta) {
            const index = this.cart.findIndex(item => item.product.id === productId);
            if (index > -1) {
                this.cart[index].qty += delta;
                if (this.cart[index].qty <= 0) {
                    this.cart.splice(index, 1);
                }
            }
        },

        removeFromCart(productId) {
            this.cart = this.cart.filter(item => item.product.id !== productId);
        },

        clearCart() {
            this.cart = [];
            this.cashAmountPaid = '';
            this.qrisProofPreview = null;
            this.qrisProofFile = null;
        },

        get cartTotal() {
            return this.cart.reduce((sum, item) => sum + (item.product.price * item.qty), 0);
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
        processCashCheckout() {
            if (!this.isCashValid) {
                this.notify('error', 'Validasi Gagal', 'Nominal uang diterima kurang dari total tagihan.');
                return;
            }

            const total = this.cartTotal;
            const paid = parseFloat(this.cashAmountPaid);
            const change = paid - total;
            const store = this.getCurrentStore();
            const user = this.getCurrentUser();

            // Revenue calculation according to PRD section 3.1
            const ownerShare = total * 0.75;
            const adminGross = total * 0.25;
            const superadminShare = 1000;
            const adminNet = adminGross - superadminShare;

            const newTx = {
                id: Date.now(),
                invoice_code: `INV/${new Date().getFullYear()}${String(new Date().getMonth() + 1).padStart(2, '0')}${String(new Date().getDate()).padStart(2, '0')}/${String(Math.floor(Math.random() * 900) + 100)}`,
                store_id: store.id,
                store_name: store.name,
                cashier_id: user.id,
                cashier_name: user.name,
                total_amount: total,
                payment_method: 'cash',
                amount_paid: paid,
                change_due: change,
                status: 'paid',
                paid_at: new Date().toISOString().replace('T', ' ').substring(0, 19),
                created_at: new Date().toISOString().replace('T', ' ').substring(0, 19),
                items: this.cart.map(c => ({
                    product_id: c.product.id,
                    title: c.product.title,
                    price: c.product.price,
                    qty: c.qty,
                    subtotal: c.product.price * c.qty
                })),
                revenue_split: {
                    owner_share: ownerShare,
                    admin_gross_share: adminGross,
                    superadmin_share: superadminShare,
                    admin_net_share: adminNet
                }
            };

            this.transactions.unshift(newTx);
            setStoredData('transactions', this.transactions);

            this.activeReceiptTransaction = newTx;
            this.receiptModalOpen = true;
            this.isCheckoutOpen = false;
            this.clearCart();

            this.notify('success', 'Transaksi Berhasil!', `Pembayaran Cash sukses. Kembalian: ${formatRupiah(change)}`);
        },

        processQrisCheckout() {
            if (!this.qrisProofPreview) {
                this.notify('error', 'Bukti Diperlukan', 'Harap unggah screenshot bukti transfer QRIS terlebih dahulu.');
                return;
            }

            const total = this.cartTotal;
            const store = this.getCurrentStore();
            const user = this.getCurrentUser();

            const newTx = {
                id: Date.now(),
                invoice_code: `INV/${new Date().getFullYear()}${String(new Date().getMonth() + 1).padStart(2, '0')}${String(new Date().getDate()).padStart(2, '0')}/${String(Math.floor(Math.random() * 900) + 100)}`,
                store_id: store.id,
                store_name: store.name,
                cashier_id: user.id,
                cashier_name: user.name,
                total_amount: total,
                payment_method: 'qris',
                amount_paid: null,
                change_due: null,
                status: 'pending_verification',
                paid_at: null,
                verified_by: null,
                verified_at: null,
                created_at: new Date().toISOString().replace('T', ' ').substring(0, 19),
                proof_image: this.qrisProofPreview,
                items: this.cart.map(c => ({
                    product_id: c.product.id,
                    title: c.product.title,
                    price: c.product.price,
                    qty: c.qty,
                    subtotal: c.product.price * c.qty
                })),
                revenue_split: null
            };

            this.transactions.unshift(newTx);
            setStoredData('transactions', this.transactions);

            this.isCheckoutOpen = false;
            this.clearCart();

            // Compliance with PRD section 2.2 / 3.3
            this.notify('warning', 'Bukti Terkirim', 'Menunggu verifikasi admin EO — transaksi belum berhasil.');
        },

        // ADMIN QRIS VERIFICATION
        openQrisVerifyModal(tx) {
            this.selectedQrisTransaction = tx;
            this.qrisModalOpen = true;
        },

        approveQris(txId) {
            const tx = this.transactions.find(t => t.id === txId);
            if (!tx) return;

            const total = tx.total_amount;
            const ownerShare = total * 0.75;
            const adminGross = total * 0.25;
            const superadminShare = 1000;
            const adminNet = adminGross - superadminShare;

            tx.status = 'paid';
            tx.paid_at = new Date().toISOString().replace('T', ' ').substring(0, 19);
            tx.verified_by = 2;
            tx.verified_at = tx.paid_at;
            tx.revenue_split = {
                owner_share: ownerShare,
                admin_gross_share: adminGross,
                superadmin_share: superadminShare,
                admin_net_share: adminNet
            };

            setStoredData('transactions', this.transactions);
            this.qrisModalOpen = false;
            this.notify('success', 'Verifikasi Disetujui', `Transaksi ${tx.invoice_code} berhasil disetujui & revenue split dihitung.`);
        },

        openRejectModal(tx) {
            this.selectedQrisTransaction = tx;
            this.rejectionReason = '';
            this.rejectModalOpen = true;
        },

        confirmRejectQris() {
            if (!this.selectedQrisTransaction) return;
            if (!this.rejectionReason.trim()) {
                this.notify('error', 'Alasan Wajib', 'Harap isi alasan penolakan bukti verifikasi.');
                return;
            }

            const tx = this.transactions.find(t => t.id === this.selectedQrisTransaction.id);
            if (tx) {
                tx.status = 'rejected';
                tx.rejection_reason = this.rejectionReason.trim();
                tx.verified_by = 2;
                tx.verified_at = new Date().toISOString().replace('T', ' ').substring(0, 19);
                tx.revenue_split = null;
                setStoredData('transactions', this.transactions);
            }

            this.rejectModalOpen = false;
            this.qrisModalOpen = false;
            this.notify('info', 'QRIS Ditolak', `Transaksi ${tx.invoice_code} ditolak dengan alasan: ${tx.rejection_reason}`);
        },

        // ADMIN CANCEL PAID TRANSACTION (PRD Section 3.4 & 2.3)
        openCancelTransactionModal(tx) {
            this.transactionToCancel = tx;
            this.cancelReasonCategory = 'Salah input barang/harga';
            this.cancelCustomNote = '';
            this.cancelRefundConfirmed = false;
            this.cancelModalOpen = true;
        },

        confirmCancelTransaction() {
            if (!this.transactionToCancel) return;
            if (!this.cancelRefundConfirmed) {
                this.notify('error', 'Konfirmasi Diperlukan', 'Harap centang checkbox konfirmasi koordinasi refund.');
                return;
            }
            if (this.cancelReasonCategory === 'Lainnya (isi manual)' && !this.cancelCustomNote.trim()) {
                this.notify('error', 'Catatan Wajib', 'Harap ketikkan detail alasan pembatalan.');
                return;
            }

            const tx = this.transactions.find(t => t.id === this.transactionToCancel.id);
            if (tx) {
                const fullReason = this.cancelReasonCategory === 'Lainnya (isi manual)' 
                    ? `Lainnya: ${this.cancelCustomNote.trim()}`
                    : (this.cancelCustomNote.trim() ? `${this.cancelReasonCategory} (${this.cancelCustomNote.trim()})` : this.cancelReasonCategory);

                tx.status = 'cancelled';
                tx.cancelled_by = 2;
                tx.cancelled_by_name = 'Admin EO Nusantara';
                tx.cancelled_at = new Date().toISOString().replace('T', ' ').substring(0, 19);
                tx.cancellation_reason = fullReason;
                tx.refund_ack_confirmed = true;

                setStoredData('transactions', this.transactions);
            }

            this.cancelModalOpen = false;
            this.notify('warning', 'Transaksi Dibatalkan', `Transaksi ${tx.invoice_code} telah berstatus Cancelled dan dikeluarkan dari kalkulasi pendapatan.`);
        },

        // PRODUCT MANAGEMENT (User)
        openAddProductModal() {
            this.isEditingProduct = false;
            this.productFormData = {
                id: null,
                title: '',
                price: '',
                category: 'Makanan',
                description: '',
                photo: '',
                stock_badge: 'Tersedia'
            };
            this.productModalOpen = true;
        },

        openEditProductModal(product) {
            this.isEditingProduct = true;
            this.productFormData = {
                id: product.id,
                title: product.title,
                price: product.price,
                category: product.category || 'Makanan',
                description: product.description || '',
                photo: product.photo || '',
                stock_badge: product.stock_badge || 'Tersedia'
            };
            this.productModalOpen = true;
        },

        saveProduct() {
            if (!this.productFormData.title.trim() || !this.productFormData.price) {
                this.notify('error', 'Validasi Form', 'Judul produk dan harga wajib diisi.');
                return;
            }

            const store = this.getCurrentStore();

            if (this.isEditingProduct) {
                const index = this.products.findIndex(p => p.id === this.productFormData.id);
                if (index > -1) {
                    this.products[index] = {
                        ...this.products[index],
                        title: this.productFormData.title.trim(),
                        price: parseFloat(this.productFormData.price),
                        category: this.productFormData.category,
                        description: this.productFormData.description,
                        photo: this.productFormData.photo || '',
                        stock_badge: this.productFormData.stock_badge
                    };
                    this.notify('success', 'Produk Diperbarui', `Menu ${this.productFormData.title} berhasil diupdate.`);
                }
            } else {
                const newProd = {
                    id: Date.now(),
                    store_id: store ? store.id : null,
                    title: this.productFormData.title.trim(),
                    price: parseFloat(this.productFormData.price),
                    category: this.productFormData.category,
                    description: this.productFormData.description,
                    photo: this.productFormData.photo || '',
                    is_active: true,
                    stock_badge: this.productFormData.stock_badge
                };
                this.products.unshift(newProd);
                this.notify('success', 'Produk Ditambahkan', `Menu ${newProd.title} siap dijual di kasir.`);
            }

            setStoredData('products', this.products);
            this.productModalOpen = false;
        },

        openDeleteProductModal(product) {
            this.productToDelete = product;
            this.deleteProductConfirmOpen = true;
        },

        confirmDeleteProduct() {
            if (!this.productToDelete) return;
            this.products = this.products.filter(p => p.id !== this.productToDelete.id);
            setStoredData('products', this.products);
            this.deleteProductConfirmOpen = false;
            this.notify('info', 'Produk Dihapus', `Menu ${this.productToDelete.title} telah dinonaktifkan.`);
            this.productToDelete = null;
        },

        // EVENT MANAGEMENT (Super Admin Multi-Event)
        openCreateEventModal() {
            this.eventFormData = {
                name: '',
                slug: '',
                start_date: '',
                end_date: '',
                location: ''
            };
            this.eventModalOpen = true;
        },

        saveNewEvent() {
            if (!this.eventFormData.name.trim()) {
                this.notify('error', 'Nama Event Wajib', 'Harap isi nama event.');
                return;
            }

            const slug = this.eventFormData.slug || this.eventFormData.name.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '');

            const newEvent = {
                id: Date.now(),
                name: this.eventFormData.name.trim(),
                slug,
                start_date: this.eventFormData.start_date || new Date().toISOString().substring(0, 10),
                end_date: this.eventFormData.end_date || new Date().toISOString().substring(0, 10),
                location: this.eventFormData.location || 'Lokasi Event',
                is_active: false,
                created_by: 1,
                created_at: new Date().toISOString().replace('T', ' ').substring(0, 19)
            };

            this.events.unshift(newEvent);
            setStoredData('events', this.events);
            this.eventModalOpen = false;
            this.notify('success', 'Event Dibuat', `Event "${newEvent.name}" berhasil dibuat sebagai arsip.`);
        },

        openActivateEventModal(ev) {
            this.eventToActivate = ev;
            this.activateEventConfirmOpen = true;
        },

        confirmActivateEvent() {
            if (!this.eventToActivate) return;
            
            // Set all others to false, activate chosen event
            this.events.forEach(e => {
                e.is_active = (e.id === this.eventToActivate.id);
            });

            setStoredData('events', this.events);
            this.activateEventConfirmOpen = false;
            this.notify('success', 'Event Diaktifkan', `Event "${this.eventToActivate.name}" kini aktif.`);
            this.eventToActivate = null;
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

        saveNewTicket() {
            if (!this.ticketFormData.subject.trim() || !this.ticketFormData.message.trim()) {
                this.notify('error', 'Form Tidak Lengkap', 'Subjek dan rincian kendala wajib diisi.');
                return;
            }

            const user = this.getCurrentUser();
            const store = this.getCurrentStore();

            const newTicket = {
                id: Date.now(),
                ticket_code: `TCK-${new Date().getFullYear()}${String(new Date().getMonth() + 1).padStart(2, '0')}${String(new Date().getDate()).padStart(2, '0')}-${String(Math.floor(Math.random() * 90) + 10)}`,
                user_id: user.id,
                user_name: user.name,
                store_name: store.name,
                category: this.ticketFormData.category,
                subject: this.ticketFormData.subject.trim(),
                status: 'open',
                created_at: new Date().toISOString().replace('T', ' ').substring(0, 19),
                messages: [
                    {
                        sender_role: 'user',
                        sender_name: user.name,
                        message: this.ticketFormData.message.trim(),
                        time: new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })
                    }
                ]
            };

            this.helpdesk.unshift(newTicket);
            setStoredData('helpdesk', this.helpdesk);
            this.ticketModalOpen = false;
            this.notify('success', 'Tiket Terkirim', `Tiket ${newTicket.ticket_code} berhasil dibuat.`);
        },

        sendTicketReply() {
            if (!this.selectedTicket || !this.ticketReplyText.trim()) return;
            const role = this.currentRole;
            const user = this.getCurrentUser();

            const t = this.helpdesk.find(x => x.id === this.selectedTicket.id);
            if (t) {
                t.messages.push({
                    sender_role: role === 'admin' || role === 'superadmin' ? 'admin' : 'user',
                    sender_name: user.name,
                    message: this.ticketReplyText.trim(),
                    time: new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })
                });

                if (role === 'admin' || role === 'superadmin') {
                    if (t.status === 'open') t.status = 'in_progress';
                }

                setStoredData('helpdesk', this.helpdesk);
                this.ticketReplyText = '';
                this.notify('success', 'Balasan Terkirim', 'Pesan berhasil dikirim.');
            }
        },

        changeTicketStatus(ticketId, newStatus) {
            const t = this.helpdesk.find(x => x.id === ticketId);
            if (t) {
                t.status = newStatus;
                setStoredData('helpdesk', this.helpdesk);
                this.notify('info', 'Status Tiket Diubah', `Status tiket kini: ${newStatus}`);
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
                    <td style="text-align: right; color: #475569;">${formatRupiah(item.price)}</td>
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
                        text-align: center;
                        font-size: 11px;
                        color: #64748b;
                    }
                    .footer p {
                        margin-bottom: 2px;
                    }
                </style>
            </head>
            <body>
                <div class="container">
                    <!-- Header -->
                    <div class="header">
                        <div>
                            <div class="store-name">${tx.store_name || store.name}</div>
                            <div class="event-name">${event ? event.name : 'Bazar UMKM Kuliner Nusantara 2026'}</div>
                            <div class="store-sub">Stand: ${store.booth_number || 'Stand A-01'} • ${event ? event.location : 'Lokasi Event'} • Telp/WA: ${store.phone || '0812-3456-7890'}</div>
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
                                <tr>
                                    <td style="padding: 4px 0; color: #64748b;">Pajak & Layanan:</td>
                                    <td style="padding: 4px 0; text-align: right; color: #64748b;">Rp 0</td>
                                </tr>
                                <tr class="total-row">
                                    <td>TOTAL TAGIHAN:</td>
                                    <td style="text-align: right; color: #059669;">${formatRupiah(tx.total_amount)}</td>
                                </tr>
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
                frameDoc.write(receiptHtml);
                frameDoc.close();

                setTimeout(() => {
                    printFrame.contentWindow.focus();
                    printFrame.contentWindow.print();
                    setTimeout(() => {
                        document.body.removeChild(printFrame);
                    }, 1000);
                }, 250);
            } catch (e) {
                console.warn('Iframe print fallback to window.open', e);
                const win = window.open('', '_blank', 'width=700,height=800');
                if (win) {
                    win.document.open();
                    win.document.write(receiptHtml);
                    win.document.close();
                } else {
                    window.print();
                }
            }
        },

        // Helper calculations for reports
        getUserReportStats(storeId = 1) {
            const validTx = this.transactions.filter(t => t.store_id === storeId && t.status === 'paid');
            const totalGross = validTx.reduce((sum, t) => sum + t.total_amount, 0);
            const netIncome = totalGross * 0.75;
            const totalCount = validTx.length;
            const cancelledCount = this.transactions.filter(t => t.store_id === storeId && t.status === 'cancelled').length;
            const pendingCount = this.transactions.filter(t => t.store_id === storeId && t.status === 'pending_verification').length;

            return {
                totalGross,
                netIncome,
                totalCount,
                cancelledCount,
                pendingCount
            };
        },

        getAdminReportStats() {
            const paidTx = this.transactions.filter(t => t.status === 'paid');
            const totalGross = paidTx.reduce((sum, t) => sum + t.total_amount, 0);
            const ownerTotal = paidTx.reduce((sum, t) => sum + (t.revenue_split?.owner_share || t.total_amount * 0.75), 0);
            const adminGross = paidTx.reduce((sum, t) => sum + (t.revenue_split?.admin_gross_share || t.total_amount * 0.25), 0);
            const superadminTotal = paidTx.reduce((sum, t) => sum + (t.revenue_split?.superadmin_share || 1000), 0);
            const adminNet = paidTx.reduce((sum, t) => sum + (t.revenue_split?.admin_net_share || (t.total_amount * 0.25) - 1000), 0);
            
            const pendingCount = this.transactions.filter(t => t.status === 'pending_verification').length;
            const cancelledCount = this.transactions.filter(t => t.status === 'cancelled').length;

            return {
                totalGross,
                ownerTotal,
                adminGross,
                superadminTotal,
                adminNet,
                paidCount: paidTx.length,
                pendingCount,
                cancelledCount,
                storesCount: this.stores.length
            };
        },

        getSuperAdminStats() {
            const paidTx = this.transactions.filter(t => t.status === 'paid');
            const totalVolume = paidTx.reduce((sum, t) => sum + t.total_amount, 0);
            const totalSuperAdminRevenue = paidTx.length * 1000; // Flat Rp 1,000 per paid transaction
            const totalEvents = this.events.length;
            const activeEvent = this.getActiveEvent();

            return {
                totalVolume,
                totalSuperAdminRevenue,
                totalEvents,
                paidCount: paidTx.length,
                activeEventName: activeEvent ? activeEvent.name : '-'
            };
        }
    });

Alpine.start();
