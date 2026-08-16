<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Super Admin Platform Master
        $superadmin = User::firstOrCreate(
            ['email' => env('SUPERADMIN_EMAIL', 'superadmin@gmail.com')],
            [
                'name' => env('SUPERADMIN_NAME', 'Super Admin Platform'),
                'username' => env('SUPERADMIN_USERNAME', 'superadmin'),
                'phone' => env('SUPERADMIN_PHONE', '081122334455'),
                'role' => 'superadmin',
                'password' => Hash::make(env('SUPERADMIN_PASSWORD', '12345678')),
            ]
        );

        // 2. Admin EO (Event Organizer)
        User::firstOrCreate(
            ['email' => env('ADMIN_EMAIL', 'admin@gmail.com')],
            [
                'name' => env('ADMIN_NAME', 'Admin EO'),
                'username' => env('ADMIN_USERNAME', 'admin'),
                'phone' => env('ADMIN_PHONE', '081299887766'),
                'role' => 'admin',
                'password' => Hash::make(env('ADMIN_PASSWORD', '12345678')),
            ]
        );

        // 3. Default Initial Active Event (Siap Produksi)
        Event::firstOrCreate(
            ['slug' => 'event-bazar-umkm-2026'],
            [
                'name' => 'Bazar Kuliner & UMKM Nusantara 2026',
                'start_date' => now()->toDateString(),
                'end_date' => now()->addDays(30)->toDateString(),
                'location' => 'Area Bazar Utama',
                'is_active' => true,
                'created_by' => $superadmin->id,
            ]
        );
    }
}
