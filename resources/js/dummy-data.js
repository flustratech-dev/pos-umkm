/**
 * Mock / Dummy Data for POS Kasir UMKM Event
 * Conforms to PRD-Frontend-POS-Kasir-UMKM-Event.md & PRD-Full-POS-Kasir-UMKM-Event.md
 */

export const initialEvents = [
    {
        id: 1,
        name: 'Bazar UMKM Kuliner Nusantara 2026',
        slug: 'bazar-umkm-kuliner-2026',
        start_date: '2026-08-15',
        end_date: '2026-08-20',
        is_active: true,
        location: 'Parkir Timur Senayan, Jakarta',
        created_by: 1,
        created_at: '2026-08-10 10:00:00'
    },
    {
        id: 2,
        name: 'Festival Ramadhan UMKM Berkah 2026',
        slug: 'festival-ramadhan-2026',
        start_date: '2026-03-20',
        end_date: '2026-04-05',
        is_active: false,
        location: 'Lapangan Banteng, Jakarta Pusat',
        created_by: 1,
        created_at: '2026-03-10 09:00:00'
    },
    {
        id: 3,
        name: 'Pameran Kreatif & Jajanan Pemuda 2026',
        slug: 'pameran-kreatif-pemuda-2026',
        start_date: '2026-06-01',
        end_date: '2026-06-05',
        is_active: false,
        location: 'Grand City Convention, Surabaya',
        created_by: 1,
        created_at: '2026-05-25 14:00:00'
    }
];

export const initialUsers = [
    {
        id: 1,
        name: 'Super Admin Platform',
        username: 'superadmin',
        email: 'superadmin@pos-umkm.id',
        role: 'superadmin',
        store_id: null,
        phone: '081122334455'
    },
    {
        id: 2,
        name: 'Admin EO Nusantara',
        username: 'admin.eo',
        email: 'admin@pos-umkm.id',
        role: 'admin',
        store_id: null,
        phone: '081299887766'
    },
    {
        id: 3,
        name: 'Siti Rahmawati',
        username: 'sitirahma',
        email: 'warung.busiti@gmail.com',
        role: 'user',
        store_id: 1,
        store_name: 'Warung Bu Siti - Nasi & Kopi',
        phone: '081234567890'
    },
    {
        id: 4,
        name: 'Budi Santoso',
        username: 'budidimsum',
        email: 'dimsum.pojok@gmail.com',
        role: 'user',
        store_id: 2,
        store_name: 'Dimsum Pojok Rasa',
        phone: '081298765432'
    },
    {
        id: 5,
        name: 'Agus Pratama',
        username: 'aguses',
        email: 'kedai.es@gmail.com',
        role: 'user',
        store_id: 3,
        store_name: 'Kedai Es Nusantara',
        phone: '085712349876'
    },
    {
        id: 6,
        name: 'Rian Taichan',
        username: 'riantaichan',
        email: 'taichan.bro@gmail.com',
        role: 'user',
        store_id: 4,
        store_name: 'Sate Taichan Mas Bro',
        phone: '081345671122'
    }
];

export const initialStores = [
    {
        id: 1,
        event_id: 1,
        owner_id: 3,
        name: 'Warung Bu Siti - Nasi & Kopi',
        owner_name: 'Siti Rahmawati',
        phone: '081234567890',
        booth_number: 'Stand A-01',
        category: 'Makanan & Minuman',
        is_active: true,
        created_at: '2026-08-14 08:30:00'
    },
    {
        id: 2,
        event_id: 1,
        owner_id: 4,
        name: 'Dimsum Pojok Rasa',
        owner_name: 'Budi Santoso',
        phone: '081298765432',
        booth_number: 'Stand A-02',
        category: 'Aneka Dimsum & Snack',
        is_active: true,
        created_at: '2026-08-14 09:00:00'
    },
    {
        id: 3,
        event_id: 1,
        owner_id: 5,
        name: 'Kedai Es Nusantara',
        owner_name: 'Agus Pratama',
        phone: '085712349876',
        booth_number: 'Stand B-05',
        category: 'Minuman Segar & Dessert',
        is_active: true,
        created_at: '2026-08-14 09:30:00'
    },
    {
        id: 4,
        event_id: 1,
        owner_id: 6,
        name: 'Sate Taichan Mas Bro',
        owner_name: 'Rian Taichan',
        phone: '081345671122',
        booth_number: 'Stand B-06',
        category: 'Kuliner Bakaran & Pedas',
        is_active: true,
        created_at: '2026-08-14 10:00:00'
    }
];

export const initialProducts = [
    // Warung Bu Siti (Store ID 1)
    {
        id: 1,
        store_id: 1,
        title: 'Es Kopi Susu Aren Spesial',
        price: 18000,
        category: 'Minuman',
        description: 'Espresso robusta mantap dengan susu segar dan gula aren murni',
        photo: 'https://images.unsplash.com/photo-1517701550927-30cf4ba1dba5?w=500&auto=format&fit=crop&q=80',
        is_active: true,
        stock_badge: 'Best Seller'
    },
    {
        id: 2,
        store_id: 1,
        title: 'Nasi Ayam Geprek Sambal Korek',
        price: 25000,
        category: 'Makanan',
        description: 'Ayam krispi renyah digeprek dengan sambal korek bawang pedas nampol + nasi hangat',
        photo: 'https://images.unsplash.com/photo-1562967914-608f82629710?w=500&auto=format&fit=crop&q=80',
        is_active: true,
        stock_badge: 'Favorit'
    },
    {
        id: 3,
        store_id: 1,
        title: 'Tahu Bakso Crispy Pedas (5 Pcs)',
        price: 15000,
        category: 'Snack',
        description: 'Tahu bakso sapi renyah disajikan dengan bubuk cabai pedas & saus cocolan',
        photo: 'https://images.unsplash.com/photo-1541592106381-b31e9677c0e5?w=500&auto=format&fit=crop&q=80',
        is_active: true,
        stock_badge: 'Tersedia'
    },
    {
        id: 4,
        store_id: 1,
        title: 'Es Teh Manis Solo Jumbo',
        price: 6000,
        category: 'Minuman',
        description: 'Teh melati wangi khas Solo gelas jumbo segar manis pas',
        photo: 'https://images.unsplash.com/photo-1556679343-c7306c1976bc?w=500&auto=format&fit=crop&q=80',
        is_active: true,
        stock_badge: 'Best Seller'
    },
    {
        id: 5,
        store_id: 1,
        title: 'Pisang Goreng Keju Cokelat',
        price: 14000,
        category: 'Snack',
        description: 'Pisang kepok manis renyah dengan limpahan keju parut & saus cokelat lumer',
        photo: 'https://images.unsplash.com/photo-1587314168485-3236d6710814?w=500&auto=format&fit=crop&q=80',
        is_active: true,
        stock_badge: 'Tersedia'
    },
    {
        id: 6,
        store_id: 1,
        title: 'Nasi Goreng Kampung Telur',
        price: 22000,
        category: 'Makanan',
        description: 'Nasi goreng racikan bumbu khas kampung dengan telur ceplok dan kerupuk',
        photo: 'https://images.unsplash.com/photo-1603133872878-684f208fb84b?w=500&auto=format&fit=crop&q=80',
        is_active: true,
        stock_badge: 'Tersedia'
    },
    // Store 2: Dimsum Pojok Rasa
    {
        id: 7,
        store_id: 2,
        title: 'Dimsum Mentai Mozarella (4 pcs)',
        price: 26000,
        category: 'Makanan',
        description: 'Dimsum ayam udang lembut dengan topping saus mentai gurih dan keju torch',
        photo: 'https://images.unsplash.com/photo-1496116218417-1a781b1c416c?w=500&auto=format&fit=crop&q=80',
        is_active: true,
        stock_badge: 'Favorit'
    },
    {
        id: 8,
        store_id: 2,
        title: 'Siomay Ayam Udang Kukus (4 pcs)',
        price: 20000,
        category: 'Makanan',
        description: 'Siomay kukus hangat kaya daging disajikan dengan chili oil racikan spesial',
        photo: 'https://images.unsplash.com/photo-1563245372-f21724e3856d?w=500&auto=format&fit=crop&q=80',
        is_active: true,
        stock_badge: 'Tersedia'
    },
    // Store 3: Kedai Es Nusantara
    {
        id: 9,
        store_id: 3,
        title: 'Es Campur Durian Kelapa Muda',
        price: 22000,
        category: 'Minuman',
        description: 'Es serut dengan daging durian asli, kelapa muda, alpukat, dan sirup cocopandan',
        photo: 'https://images.unsplash.com/photo-1551024709-8f23befc6f87?w=500&auto=format&fit=crop&q=80',
        is_active: true,
        stock_badge: 'Best Seller'
    },
    // Store 4: Sate Taichan Mas Bro
    {
        id: 10,
        store_id: 4,
        title: 'Sate Taichan Paha Gurih (10 Tusuk)',
        price: 28000,
        category: 'Makanan',
        description: 'Sate ayam bagian paha juicy dibakar gurih dengan sambal taichan peras jeruk limau',
        photo: 'https://images.unsplash.com/photo-1529692236671-f1f6cf9683ba?w=500&auto=format&fit=crop&q=80',
        is_active: true,
        stock_badge: 'Favorit'
    }
];

export const initialTransactions = [
    {
        id: 101,
        invoice_code: 'INV/20260815/001',
        store_id: 1,
        store_name: 'Warung Bu Siti - Nasi & Kopi',
        cashier_id: 3,
        cashier_name: 'Siti Rahmawati',
        total_amount: 43000,
        payment_method: 'cash',
        amount_paid: 50000,
        change_due: 7000,
        status: 'paid',
        paid_at: '2026-08-15 10:15:22',
        created_at: '2026-08-15 10:15:00',
        items: [
            { product_id: 2, title: 'Nasi Ayam Geprek Sambal Korek', price: 25000, qty: 1, subtotal: 25000 },
            { product_id: 1, title: 'Es Kopi Susu Aren Spesial', price: 18000, qty: 1, subtotal: 18000 }
        ],
        revenue_split: {
            owner_share: 32250, // 75% of 43,000
            admin_gross_share: 10750, // 25% of 43,000
            superadmin_share: 1000, // Rp 1,000 flat
            admin_net_share: 9750 // 10,750 - 1,000
        }
    },
    {
        id: 102,
        invoice_code: 'INV/20260815/002',
        store_id: 1,
        store_name: 'Warung Bu Siti - Nasi & Kopi',
        cashier_id: 3,
        cashier_name: 'Siti Rahmawati',
        total_amount: 32000,
        payment_method: 'qris',
        amount_paid: null,
        change_due: null,
        status: 'paid',
        paid_at: '2026-08-15 10:30:10',
        verified_by: 2,
        verified_at: '2026-08-15 10:31:05',
        created_at: '2026-08-15 10:28:45',
        proof_image: 'https://images.unsplash.com/photo-1554224155-8d04cb21cd6c?w=600&auto=format&fit=crop&q=80',
        items: [
            { product_id: 1, title: 'Es Kopi Susu Aren Spesial', price: 18000, qty: 1, subtotal: 18000 },
            { product_id: 5, title: 'Pisang Goreng Keju Cokelat', price: 14000, qty: 1, subtotal: 14000 }
        ],
        revenue_split: {
            owner_share: 24000, // 75% of 32,000
            admin_gross_share: 8000, // 25% of 32,000
            superadmin_share: 1000,
            admin_net_share: 7000
        }
    },
    {
        id: 103,
        invoice_code: 'INV/20260815/003',
        store_id: 1,
        store_name: 'Warung Bu Siti - Nasi & Kopi',
        cashier_id: 3,
        cashier_name: 'Siti Rahmawati',
        total_amount: 36000,
        payment_method: 'qris',
        amount_paid: null,
        change_due: null,
        status: 'pending_verification',
        paid_at: null,
        verified_by: null,
        verified_at: null,
        created_at: '2026-08-15 11:05:12',
        proof_image: 'https://images.unsplash.com/photo-1554224155-8d04cb21cd6c?w=600&auto=format&fit=crop&q=80',
        items: [
            { product_id: 1, title: 'Es Kopi Susu Aren Spesial', price: 18000, qty: 2, subtotal: 36000 }
        ],
        revenue_split: null
    },
    {
        id: 104,
        invoice_code: 'INV/20260815/004',
        store_id: 1,
        store_name: 'Warung Bu Siti - Nasi & Kopi',
        cashier_id: 3,
        cashier_name: 'Siti Rahmawati',
        total_amount: 21000,
        payment_method: 'cash',
        amount_paid: 50000,
        change_due: 29000,
        status: 'paid',
        paid_at: '2026-08-15 11:20:00',
        created_at: '2026-08-15 11:19:40',
        items: [
            { product_id: 3, title: 'Tahu Bakso Crispy Pedas', price: 15000, qty: 1, subtotal: 15000 },
            { product_id: 4, title: 'Es Teh Manis Solo Jumbo', price: 6000, qty: 1, subtotal: 6000 }
        ],
        revenue_split: {
            owner_share: 15750,
            admin_gross_share: 5250,
            superadmin_share: 1000,
            admin_net_share: 4250
        }
    },
    {
        id: 105,
        invoice_code: 'INV/20260815/005',
        store_id: 1,
        store_name: 'Warung Bu Siti - Nasi & Kopi',
        cashier_id: 3,
        cashier_name: 'Siti Rahmawati',
        total_amount: 25000,
        payment_method: 'qris',
        amount_paid: null,
        change_due: null,
        status: 'rejected',
        rejection_reason: 'Bukti transfer buram dan nominal tidak terbaca dengan jelas',
        paid_at: null,
        verified_by: 2,
        verified_at: '2026-08-15 11:45:00',
        created_at: '2026-08-15 11:40:00',
        proof_image: 'https://images.unsplash.com/photo-1554224155-8d04cb21cd6c?w=600&auto=format&fit=crop&q=80',
        items: [
            { product_id: 2, title: 'Nasi Ayam Geprek Sambal Korek', price: 25000, qty: 1, subtotal: 25000 }
        ],
        revenue_split: null
    },
    {
        id: 106,
        invoice_code: 'INV/20260815/006',
        store_id: 1,
        store_name: 'Warung Bu Siti - Nasi & Kopi',
        cashier_id: 3,
        cashier_name: 'Siti Rahmawati',
        total_amount: 40000,
        payment_method: 'cash',
        amount_paid: 40000,
        change_due: 0,
        status: 'cancelled',
        cancelled_by: 2,
        cancelled_by_name: 'Admin EO Nusantara',
        cancelled_at: '2026-08-15 12:15:00',
        cancellation_reason: 'Salah input barang/harga (Customer batal memesan menu ayam geprek dobel)',
        refund_ack_confirmed: true,
        paid_at: '2026-08-15 12:00:00',
        created_at: '2026-08-15 11:58:30',
        items: [
            { product_id: 2, title: 'Nasi Ayam Geprek Sambal Korek', price: 25000, qty: 1, subtotal: 25000 },
            { product_id: 3, title: 'Tahu Bakso Crispy Pedas', price: 15000, qty: 1, subtotal: 15000 }
        ],
        revenue_split: {
            owner_share: 30000,
            admin_gross_share: 10000,
            superadmin_share: 1000,
            admin_net_share: 9000
        }
    },
    // Transactions from other stores for Admin/Superadmin overview
    {
        id: 107,
        invoice_code: 'INV/20260815/007',
        store_id: 2,
        store_name: 'Dimsum Pojok Rasa',
        cashier_id: 4,
        cashier_name: 'Budi Santoso',
        total_amount: 46000,
        payment_method: 'qris',
        amount_paid: null,
        change_due: null,
        status: 'pending_verification',
        paid_at: null,
        created_at: '2026-08-15 12:30:00',
        proof_image: 'https://images.unsplash.com/photo-1554224155-8d04cb21cd6c?w=600&auto=format&fit=crop&q=80',
        items: [
            { product_id: 7, title: 'Dimsum Mentai Mozarella (4 pcs)', price: 26000, qty: 1, subtotal: 26000 },
            { product_id: 8, title: 'Siomay Ayam Udang Kukus', price: 20000, qty: 1, subtotal: 20000 }
        ],
        revenue_split: null
    },
    {
        id: 108,
        invoice_code: 'INV/20260815/008',
        store_id: 3,
        store_name: 'Kedai Es Nusantara',
        cashier_id: 5,
        cashier_name: 'Agus Pratama',
        total_amount: 44000,
        payment_method: 'cash',
        amount_paid: 50000,
        change_due: 6000,
        status: 'paid',
        paid_at: '2026-08-15 12:45:00',
        created_at: '2026-08-15 12:44:00',
        items: [
            { product_id: 9, title: 'Es Campur Durian Kelapa Muda', price: 22000, qty: 2, subtotal: 44000 }
        ],
        revenue_split: {
            owner_share: 33000,
            admin_gross_share: 11000,
            superadmin_share: 1000,
            admin_net_share: 10000
        }
    },
    {
        id: 109,
        invoice_code: 'INV/20260815/009',
        store_id: 4,
        store_name: 'Sate Taichan Mas Bro',
        cashier_id: 6,
        cashier_name: 'Rian Taichan',
        total_amount: 56000,
        payment_method: 'qris',
        amount_paid: null,
        change_due: null,
        status: 'paid',
        paid_at: '2026-08-15 13:00:00',
        verified_by: 2,
        verified_at: '2026-08-15 13:02:00',
        created_at: '2026-08-15 12:59:00',
        proof_image: 'https://images.unsplash.com/photo-1554224155-8d04cb21cd6c?w=600&auto=format&fit=crop&q=80',
        items: [
            { product_id: 10, title: 'Sate Taichan Paha Gurih (10 Tusuk)', price: 28000, qty: 2, subtotal: 56000 }
        ],
        revenue_split: {
            owner_share: 42000,
            admin_gross_share: 14000,
            superadmin_share: 1000,
            admin_net_share: 13000
        }
    }
];

export const initialHelpdeskTickets = [
    {
        id: 1,
        ticket_code: 'TCK-20260815-01',
        user_id: 3,
        user_name: 'Siti Rahmawati (Warung Bu Siti)',
        store_name: 'Warung Bu Siti - Nasi & Kopi',
        category: 'Kasir & Pembayaran',
        subject: 'Verifikasi bukti QRIS transaksi INV/003 belum masuk',
        status: 'in_progress',
        created_at: '2026-08-15 11:10:00',
        messages: [
            {
                sender_role: 'user',
                sender_name: 'Siti Rahmawati',
                message: 'Halo admin EO, pembeli di stand saya sudah transfer QRIS Rp36.000 dengan invoice INV/003, mohon bantu dicek dan disetujui ya.',
                time: '11:10'
            },
            {
                sender_role: 'admin',
                sender_name: 'Admin EO Nusantara',
                message: 'Baik Bu Siti, bukti sedang dicek mutasi banknya oleh tim finance di tenda panitia. Segera kami verifikasi.',
                time: '11:15'
            }
        ]
    },
    {
        id: 2,
        ticket_code: 'TCK-20260815-02',
        user_id: 3,
        user_name: 'Siti Rahmawati (Warung Bu Siti)',
        store_name: 'Warung Bu Siti - Nasi & Kopi',
        category: 'Operasional Event',
        subject: 'Permintaan tambahan stopkontak listrik di Stand A-01',
        status: 'resolved',
        created_at: '2026-08-15 08:45:00',
        messages: [
            {
                sender_role: 'user',
                sender_name: 'Siti Rahmawati',
                message: 'Pagi tim panitia, apakah bisa minta tambahan 1 colokan kabel untuk blender es kopi?',
                time: '08:45'
            },
            {
                sender_role: 'admin',
                sender_name: 'Admin EO Nusantara',
                message: 'Sudah diantarkan oleh tim logistik ya bu. Terima kasih.',
                time: '09:00'
            }
        ]
    },
    {
        id: 3,
        ticket_code: 'TCK-20260815-03',
        user_id: 4,
        user_name: 'Budi Santoso (Dimsum Pojok)',
        store_name: 'Dimsum Pojok Rasa',
        category: 'Produk & Menu',
        subject: 'Cara edit foto menu yang sudah terlanjur diupload',
        status: 'open',
        created_at: '2026-08-15 12:40:00',
        messages: [
            {
                sender_role: 'user',
                sender_name: 'Budi Santoso',
                message: 'Siang min, mau tanya cara ganti foto menu dimsum mentai apakah bisa langsung dari menu produk?',
                time: '12:40'
            }
        ]
    }
];

export const staticQrisData = {
    merchant_name: 'EO BAZAR UMKM NUSANTARA 2026',
    nmid: 'ID1020260815998',
    acquirer: 'PT BANK CENTRAL ASIA TBK',
    // High quality mock QR code image URL
    qris_image_url: 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=00020101021126580016ID.CO.QRIS.WWW01189360000201123456780208123456785204581253033605802ID5924EO+BAZAR+UMKM+NUSANTARA6007JAKARTA61051234062070703A016304ABCD'
};
