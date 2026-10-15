<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$logs = App\Models\NotificationLog::latest()->take(5)->get();
foreach ($logs as $l) {
    echo "ID: {$l->id} | Domain: {$l->domain} | Recipient: {$l->recipient_email} | Status: {$l->status} | Created: {$l->created_at} | Meta: " . json_encode($l->payload_meta) . "\n";
}




