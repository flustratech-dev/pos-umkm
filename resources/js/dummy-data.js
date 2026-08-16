/**
 * Clean Initial State for Production - POS Kasir UMKM Event
 */

export const initialEvents = [
    {
        id: 1,
        name: 'Bazar Kuliner & UMKM Nusantara 2026',
        slug: 'event-bazar-umkm-2026',
        start_date: new Date().toISOString().substring(0, 10),
        end_date: new Date(Date.now() + 30 * 86400000).toISOString().substring(0, 10),
        is_active: true,
        location: 'Area Bazar Utama',
        created_by: 1,
        created_at: new Date().toISOString().replace('T', ' ').substring(0, 19)
    }
];

export const initialUsers = [
    {
        id: 1,
        name: 'Super Admin Platform',
        username: 'superadmin',
        email: 'superadmin@gmail.com',
        role: 'superadmin',
        store_id: null,
        phone: '081122334455'
    },
    {
        id: 2,
        name: 'Admin EO',
        username: 'admin',
        email: 'admin@gmail.com',
        role: 'admin',
        store_id: null,
        phone: '081299887766'
    }
];

export const initialStores = [];

export const initialProducts = [];

export const initialTransactions = [];

export const initialHelpdeskTickets = [];

export const staticQrisData = {
    merchant_name: 'PANITIA EVENT BAZAR UMKM',
    nmid: 'ID1020039485712',
    bank: 'BCA / QRIS NASIONAL',
    qris_image_url: 'https://images.unsplash.com/photo-1607604276583-eef5d076aa5f?w=600&auto=format&fit=crop&q=80',
    account_number: '123-456-7890',
    account_holder: 'PANITIA BAZAR NUSANTARA'
};
