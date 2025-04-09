<?php

use App\Enums\Inventory;

return [
    'transaction_type' => [
        Inventory::INCOMING->value,
        Inventory::OUTGOING->value,
    ]
];
