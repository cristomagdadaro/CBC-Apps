<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Import web routes grouped by owning module.
|
*/

require __DIR__.'/web/shared.php';
require __DIR__.'/web/main-dashboard.php';
require __DIR__.'/web/form-builder.php';
require __DIR__.'/web/bookings-and-rentals.php';
require __DIR__.'/web/fes-request.php';
require __DIR__.'/web/equipment-logger.php';
require __DIR__.'/web/research.php';
require __DIR__.'/web/inventory.php';
require __DIR__.'/web/file-reports.php';
require __DIR__.'/web/options.php';
require __DIR__.'/web/user-management.php';
require __DIR__.'/web/golinks.php';

if (app()->environment('local')) {
    require __DIR__.'/web/dev.php';
}

Route::get('/ai', function() {
    $user = auth()->user();
    
    $payload = base64_encode(json_encode([
        'id' => $user->id,
        'name' => $user->name,
        'email' => $user->email,
        'timestamp' => now()->timestamp,
    ]));

    $signature = hash_hmac('sha256', $payload, env('SPROUTAI_INTERNAL_SYNC_TOKEN'));

    $redirectUrl = rtrim(env('SPROUTAI_HOST', 'https://onecbc.philrice.gov.ph/ai'), '/') . '/sso-login?' . http_build_query([
        'payload' => $payload,
        'signature' => $signature,
    ]);

    return redirect()->away($redirectUrl);
})->middleware('auth')->name('sproutai.microservice');
