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

Route::get('/auth/handoff', [\App\Http\Controllers\AuthHandoffController::class, 'handle'])->name('auth.handoff');

require __DIR__.'/web/dev.php';

Route::get('/apps/sproutai-sso', function () {
    $user = \Illuminate\Support\Facades\Auth::user();

    if (!$user) {
        return redirect()->route('login');
    }

    $token = (string) config('services.sproutai.internal_sync_token', env('SPROUTAI_INTERNAL_SYNC_TOKEN'));
    if (strlen($token) < 32) {
        abort(500, 'SPROUTAI_INTERNAL_SYNC_TOKEN is not configured securely.');
    }

    $payload = base64_encode(json_encode([
        'jti' => (string) \Illuminate\Support\Str::uuid(),
        'id' => $user->id,
        'name' => $user->name,
        'email' => $user->email,
        'timestamp' => now()->timestamp,
    ]));

    $signature = hash_hmac('sha256', $payload, $token);

    $sproutAiHost = config('services.sproutai.host', env('SPROUTAI_HOST', 'https://onecbc.philrice.gov.ph/ai'));
    $redirectUrl = rtrim($sproutAiHost, '/') . '/sso-login?' . http_build_query([
        'payload' => $payload,
        'signature' => $signature,
    ]);

    return redirect()->away($redirectUrl)->header('Referrer-Policy', 'no-referrer');
})->middleware(['auth', 'throttle:30,1'])->name('sproutai.microservice');

