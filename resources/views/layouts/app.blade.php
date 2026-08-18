<!DOCTYPE html>
<html lang="id" class="h-full bg-[#f7f9f9] text-[#0f1419] antialiased selection:bg-[#1d9bf0] selection:text-white">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Kasir JADISATU') — JADISATU</title>

    <!-- PWA & Mobile Standalone Settings -->
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#1d9bf0">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="Kasir POS">

    <!-- Favicon (High Curvature Squircle) -->
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/favicon-32x32.png') }}?v=3">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('images/favicon-16x16.png') }}?v=3">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('images/favicon.png') }}?v=3">
    <link rel="apple-touch-icon" href="{{ asset('images/apple-touch-icon.png') }}?v=3">

    <!-- Google Fonts: Plus Jakarta Sans & Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @php
        $authUser = auth()->user();
        $userStore = $authUser ? ($authUser->store ?: $authUser->ownedStore) : null;
        $userStoreId = $userStore ? $userStore->id : null;

        $jsAuthUser = $authUser ? [
            'id' => $authUser->id,
            'name' => $authUser->name,
            'username' => $authUser->username,
            'email' => $authUser->email,
            'role' => $authUser->role,
            'store_id' => $userStoreId,
            'store_name' => $userStore ? $userStore->name : null,
            'booth_number' => $userStore ? $userStore->booth_number : null,
        ] : null;

        $activeEv = \App\Models\Event::getActive();
        $jsActiveEvent = $activeEv ? [
            'id' => $activeEv->id,
            'name' => $activeEv->name,
            'slug' => $activeEv->slug,
            'location' => $activeEv->location,
            'is_active' => $activeEv->is_active,
            'qris_image_url' => $activeEv->qris_image_url,
        ] : null;

        $dbEvents = \App\Models\Event::all();
        $activeEventId = $activeEv ? $activeEv->id : null;
        
        // Stores Mapping (Only if passed from controller)
        $dbStores = isset($stores) ? $stores->map(function($s) {
            return [
                'id' => $s->id,
                'event_id' => $s->event_id,
                'owner_id' => $s->owner_id,
                'name' => $s->name,
                'owner_name' => $s->owner ? $s->owner->name : '',
                'phone' => $s->owner ? $s->owner->phone : '',
                'booth_number' => $s->booth_number,
                'category' => $s->category,
                'is_active' => $s->is_active,
            ];
        }) : collect();

        // Products Mapping (Only if passed from controller)
        $dbProducts = isset($products) ? $products->map(function($p) {
            return [
                'id' => $p->id,
                'store_id' => $p->store_id,
                'title' => $p->title,
                'price' => (float)$p->price,
                'category' => $p->category,
                'description' => $p->description,
                'photo' => $p->photo_url,
                'stock_badge' => $p->stock_badge,
                'is_active' => $p->is_active,
                'store' => $p->relationLoaded('store') ? $p->store : null,
            ];
        }) : collect();

        // Transactions Mapping (Only if passed from controller, handling various names)
        $rawTransactions = $transactions ?? $recentTransactions ?? $pendingTransactions ?? $historyTransactions ?? collect();
        $dbTransactions = $rawTransactions->map(function($t) {
            return [
                'id' => $t->id,
                'invoice_code' => $t->invoice_code,
                'store_id' => $t->store_id,
                'store_name' => $t->store ? $t->store->name : '',
                'cashier_id' => $t->cashier_id,
                'cashier_name' => $t->cashier ? $t->cashier->name : '',
                'total_amount' => (float)$t->total_amount,
                'payment_method' => $t->payment_method,
                'amount_paid' => $t->amount_paid ? (float)$t->amount_paid : null,
                'change_due' => $t->change_due ? (float)$t->change_due : null,
                'status' => $t->status,
                'paid_at' => $t->paid_at ? $t->paid_at->toIso8601String() : null,
                'created_at' => $t->created_at ? $t->created_at->toIso8601String() : null,
                'payment_proof' => $t->paymentProof ? $t->paymentProof->proof_url : null,
                'proof_image' => $t->paymentProof ? $t->paymentProof->proof_url : null,
                'items' => $t->items ? $t->items->map(function($item) {
                    return [
                        'product_id' => $item->product_id,
                        'title' => $item->title,
                        'price' => (float)$item->price,
                        'qty' => $item->qty,
                        'subtotal' => (float)$item->subtotal,
                    ];
                }) : collect(),
                'revenue_split' => $t->revenueSplit ? [
                    'owner_share' => (float)$t->revenueSplit->owner_share,
                    'admin_gross_share' => (float)$t->revenueSplit->admin_gross_share,
                    'superadmin_share' => (float)$t->revenueSplit->superadmin_share,
                    'admin_net_share' => (float)$t->revenueSplit->admin_net_share,
                ] : null,
            ];
        });

        // Helpdesk Tickets Mapping (Only if passed from controller)
        $dbTickets = isset($tickets) ? $tickets->map(function($tk) {
            return [
                'id' => $tk->id,
                'ticket_code' => $tk->ticket_code,
                'user_id' => $tk->user_id,
                'user_name' => $tk->user ? $tk->user->name : '',
                'store_id' => $tk->store_id,
                'store_name' => $tk->store ? $tk->store->name : '',
                'category' => $tk->category,
                'subject' => $tk->subject,
                'status' => $tk->status,
                'created_at' => $tk->created_at ? $tk->created_at->toIso8601String() : null,
                'replies' => $tk->replies ? $tk->replies->map(function($r) {
                    return [
                        'id' => $r->id,
                        'user_id' => $r->user_id,
                        'user_name' => $r->user ? $r->user->name : '',
                        'message' => $r->message,
                        'created_at' => $r->created_at ? $r->created_at->toIso8601String() : null,
                    ];
                }) : collect(),
            ];
        }) : collect();

        // User's owned stores (for switching events)
        $dbUserStores = collect();
        if ($authUser && $authUser->isUser()) {
            $dbUserStores = \App\Models\Store::with('event')
                ->where('owner_id', $authUser->id)
                ->orWhere('id', $userStoreId ?: 0)
                ->latest()
                ->get()
                ->unique('id')
                ->values()
                ->map(function($s) {
                    return [
                        'id' => $s->id,
                        'name' => $s->name,
                        'event_name' => $s->event ? $s->event->name : 'Unknown Event',
                        'event_is_active' => $s->event ? (bool)$s->event->is_active : false,
                    ];
                });
        }
    @endphp

    <script>
        window.__AUTH_USER__ = @json($jsAuthUser);
        window.__USER_STORES__ = @json($dbUserStores);
        window.__ACTIVE_EVENT__ = @json($jsActiveEvent);
        window.__INITIAL_EVENTS__ = @json($dbEvents);
        window.__INITIAL_STORES__ = @json($dbStores);
        window.__INITIAL_PRODUCTS__ = @json($dbProducts);
        window.__INITIAL_TRANSACTIONS__ = @json($dbTransactions);
        window.__INITIAL_HELPDESK__ = @json($dbTickets);
        window.__LOGO_URL__ = @json(asset('images/logo_jadisatu.png'));
        @php
            $logoPath = public_path('images/logo_jadisatu.png');
            $logoData = file_exists($logoPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath)) : '';
        @endphp
        window.__LOGO_BASE64__ = @json($logoData);
        @if(session('success'))
            window.__FLASH_SUCCESS__ = @json(session('success'));
        @endif
        @if(session('error'))
            window.__FLASH_ERROR__ = @json(session('error'));
        @endif
        @if($errors->any())
            window.__FLASH_ERROR__ = @json($errors->first());
        @endif
    </script>

    <!-- Vite Styles & Scripts (Loaded AFTER window data is ready) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        [x-cloak] { display: none !important; }
        body { font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; }
    </style>
</head>
<body class="h-full flex overflow-hidden bg-[#f7f9f9]" x-data>
    <!-- Desktop Sidebar (Twitter UI) -->
    @include('components.sidebar')

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col min-w-0 h-screen overflow-y-auto overflow-x-hidden">
        <!-- Impersonation Inspection Floating Banner -->
        @include('components.impersonate-banner')

        <!-- Header / Topbar (Twitter UI) -->
        @include('components.topbar')

        <!-- Scrollable Body Content -->
        <main class="flex-1 pb-24 lg:pb-10 px-4 sm:px-6 lg:px-8 py-6 max-w-7xl w-full mx-auto">
            @yield('content')
        </main>
    </div>

    <!-- Mobile Bottom Navigation (Twitter UI) -->
    @include('components.bottom-nav')

    <!-- Global Thermal Receipt Modal -->
    @include('components.receipt-modal')

    <!-- Global Circular Logo Loading Spinner Overlay -->
    @include('components.loading-overlay')

    <script>
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.getRegistrations().then(function(registrations) {
                for (let registration of registrations) {
                    registration.unregister();
                }
            });
        }
    </script>
</body>
</html>
