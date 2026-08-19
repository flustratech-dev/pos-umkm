<?php

namespace Tests\Feature;

use Tests\TestCase;

class CheckoutLayoutTest extends TestCase
{
    /**
     * Panel checkout pernah lebih tinggi dari layar HP tanpa bisa di-scroll,
     * sehingga tombol bayar dan blok QRIS tidak bisa dijangkau kasir.
     */
    public function test_checkout_drawer_can_scroll_on_small_screens(): void
    {
        $source = file_get_contents(resource_path('views/user/kasir.blade.php'));

        // Panel drawer menahan isinya, tiap bagian mengurus scroll-nya sendiri.
        $this->assertStringContainsString(
            'bg-white shadow-2xl flex flex-col justify-between overflow-hidden',
            $source
        );

        // Daftar item harus boleh menyusut di dalam flex column.
        $this->assertStringContainsString(
            'flex-1 min-h-0 overflow-y-auto p-5 space-y-3',
            $source
        );

        // Panel pembayaran (tab QRIS paling panjang) bisa di-scroll sendiri.
        $this->assertStringContainsString(
            'shrink-0 max-h-[75vh] overflow-y-auto custom-scrollbar p-5 border-t',
            $source
        );
    }
}
