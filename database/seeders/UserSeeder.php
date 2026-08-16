<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\HelpdeskReply;
use App\Models\HelpdeskTicket;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use App\Services\CheckoutService;
use App\Services\RevenueSplitService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Super Admin Platform
        $superadmin = User::firstOrCreate(
            ['email' => env('SUPERADMIN_EMAIL', 'superadmin@pos-umkm.id')],
            [
                'name' => env('SUPERADMIN_NAME', 'Super Admin Platform'),
                'username' => env('SUPERADMIN_USERNAME', 'superadmin'),
                'phone' => env('SUPERADMIN_PHONE', '081122334455'),
                'role' => 'superadmin',
                'password' => Hash::make(env('SUPERADMIN_PASSWORD', 'password123')),
            ]
        );

        // 2. Admin EO Nusantara
        $admin = User::firstOrCreate(
            ['email' => env('ADMIN_EMAIL', 'admin@pos-umkm.id')],
            [
                'name' => env('ADMIN_NAME', 'Admin EO Nusantara'),
                'username' => env('ADMIN_USERNAME', 'admin.eo'),
                'phone' => env('ADMIN_PHONE', '081299887766'),
                'role' => 'admin',
                'password' => Hash::make(env('ADMIN_PASSWORD', 'password123')),
            ]
        );

        // 3. Default Active Event
        $event = Event::firstOrCreate(
            ['slug' => 'bazar-kuliner-umkm-2026'],
            [
                'name' => 'Bazar Kuliner & UMKM Nusantara 2026',
                'start_date' => now()->toDateString(),
                'end_date' => now()->addDays(7)->toDateString(),
                'location' => 'Parkir Timur Senayan, Jakarta',
                'is_active' => true,
                'created_by' => $superadmin->id,
            ]
        );

        // 4. Demo Tenant User 1: Warung Bu Siti
        $userSiti = User::firstOrCreate(
            ['email' => 'warung.busiti@gmail.com'],
            [
                'name' => 'Ibu Siti Aminah',
                'username' => 'warung.busiti',
                'phone' => '081234567890',
                'role' => 'user',
                'password' => Hash::make('password123'),
            ]
        );

        $storeSiti = Store::firstOrCreate(
            ['owner_id' => $userSiti->id, 'event_id' => $event->id],
            [
                'name' => 'Warung Nasi Uduk & Gorengan Bu Siti',
                'booth_number' => 'Stand A-01',
                'category' => 'Makanan & Minuman',
                'is_active' => true,
            ]
        );
        $userSiti->update(['store_id' => $storeSiti->id]);

        // Demo Products for Warung Bu Siti
        $productsData = [
            [
                'title' => 'Nasi Uduk Komplit Ayam Suwir',
                'price' => 25000,
                'category' => 'Makanan',
                'description' => 'Nasi uduk harum santan dengan suwiran ayam gurih, telur iris, bihun, dan sambal terasi.',
                'photo' => 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=400&q=80',
                'stock_badge' => 'Best Seller',
            ],
            [
                'title' => 'Es Teh Manis Jumbo Melati',
                'price' => 6000,
                'category' => 'Minuman',
                'description' => 'Es teh racikan daun teh asli melati dengan gula cair murni, segar dingin.',
                'photo' => 'https://images.unsplash.com/photo-1556679343-c7306c1976bc?auto=format&fit=crop&w=400&q=80',
                'stock_badge' => 'Favorit',
            ],
            [
                'title' => 'Tahu & Tempe Mendoan Crispy',
                'price' => 12000,
                'category' => 'Snack',
                'description' => 'Mendoan gurih hangat dengan cocolan kecap rawit pedas nikmat.',
                'photo' => 'https://images.unsplash.com/photo-1565299585323-38d6b0865b47?auto=format&fit=crop&w=400&q=80',
                'stock_badge' => 'Tersedia',
            ],
            [
                'title' => 'Ayam Geprek Sambal Korek',
                'price' => 22000,
                'category' => 'Makanan',
                'description' => 'Ayam krispi digeprek dengan sambal cabai rawit pedas mantap.',
                'photo' => 'https://images.unsplash.com/photo-1626082927389-6cd097cdc6ec?auto=format&fit=crop&w=400&q=80',
                'stock_badge' => 'Tersedia',
            ],
            [
                'title' => 'Es Jeruk Peras Murni',
                'price' => 8000,
                'category' => 'Minuman',
                'description' => 'Jeruk peras asli tanpa pemanis buatan, kaya vitamin C.',
                'photo' => 'https://images.unsplash.com/photo-1613478223719-2ab802602423?auto=format&fit=crop&w=400&q=80',
                'stock_badge' => 'Tersedia',
            ],
        ];

        $createdProducts = [];
        foreach ($productsData as $pData) {
            $createdProducts[] = Product::firstOrCreate(
                ['store_id' => $storeSiti->id, 'title' => $pData['title']],
                array_merge($pData, ['is_active' => true])
            );
        }

        // 5. Demo Tenant User 2: Kopi Kenangan Senayan
        $userKopi = User::firstOrCreate(
            ['email' => 'kopi.senayan@gmail.com'],
            [
                'name' => 'Budi Santoso',
                'username' => 'kopi.senayan',
                'phone' => '087788990011',
                'role' => 'user',
                'password' => Hash::make('password123'),
            ]
        );

        $storeKopi = Store::firstOrCreate(
            ['owner_id' => $userKopi->id, 'event_id' => $event->id],
            [
                'name' => 'Kedai Kopi & Toast Nusantara',
                'booth_number' => 'Stand B-04',
                'category' => 'Minuman & Snack',
                'is_active' => true,
            ]
        );
        $userKopi->update(['store_id' => $storeKopi->id]);

        Product::firstOrCreate(
            ['store_id' => $storeKopi->id, 'title' => 'Kopi Susu Gula Aren Spesial'],
            [
                'price' => 18000,
                'category' => 'Minuman',
                'description' => 'Espresso arabika dengan susu segar dan sirup gula aren organik.',
                'photo' => 'https://images.unsplash.com/photo-1541167760496-1628856ab772?auto=format&fit=crop&w=400&q=80',
                'stock_badge' => 'Best Seller',
                'is_active' => true,
            ]
        );

        // 6. Seed Sample Transactions for Warung Bu Siti
        $checkoutService = new CheckoutService(new RevenueSplitService());

        // Cash Transaction (Paid)
        if ($storeSiti->transactions()->count() === 0 && count($createdProducts) >= 2) {
            $checkoutService->processCashCheckout(
                $storeSiti,
                $userSiti,
                [
                    ['product_id' => $createdProducts[0]->id, 'qty' => 2], // 2 x 25k = 50k
                    ['product_id' => $createdProducts[1]->id, 'qty' => 2], // 2 x 6k = 12k
                ],
                70000.00 // total 62k, change 8k
            );

            // QRIS Transaction (Pending)
            $checkoutService->processQrisCheckout(
                $storeSiti,
                $userSiti,
                [
                    ['product_id' => $createdProducts[2]->id, 'qty' => 1], // 1 x 12k = 12k
                    ['product_id' => $createdProducts[1]->id, 'qty' => 1], // 1 x 6k = 6k
                ],
                'https://images.unsplash.com/photo-1556742049-0a67e55722c0?auto=format&fit=crop&w=600&q=80'
            );
        }

        // 7. Seed Sample Helpdesk Ticket
        if (HelpdeskTicket::count() === 0) {
            $ticket = HelpdeskTicket::create([
                'ticket_code' => 'TCK-' . now()->format('Ymd') . '-01',
                'user_id' => $userSiti->id,
                'store_id' => $storeSiti->id,
                'category' => 'Kasir & Pembayaran',
                'subject' => 'Verifikasi transaksi QRIS Rp18.000 atas nama Doni',
                'status' => 'open',
            ]);

            HelpdeskReply::create([
                'ticket_id' => $ticket->id,
                'user_id' => $userSiti->id,
                'message' => 'Halo panitia, mohon verifikasi transaksi QRIS di stand A-01 nominal Rp 18.000 sudah saya upload struknya. Terima kasih!',
            ]);
        }
    }
}
