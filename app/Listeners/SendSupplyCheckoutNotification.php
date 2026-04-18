<?php

namespace App\Listeners;

use App\Events\InventoryTransactionChanged;
use App\Notifications\SupplyCheckoutTriggeredNotification;
use App\Services\Notifications\NotificationDispatchService;

class SendSupplyCheckoutNotification
{
    public function __construct(private readonly NotificationDispatchService $dispatch)
    {
    }

    public function handle(InventoryTransactionChanged $event): void
    {
        $transaction = $event->transaction;

        if ($event->action !== 'created' || ($transaction['transac_type'] ?? null) !== 'outgoing') {
            return;
        }

        $requesterName = trim(implode(' ', array_filter([
            $transaction['personnel']['fname'] ?? null,
            $transaction['personnel']['mname'] ?? null,
            $transaction['personnel']['lname'] ?? null,
            $transaction['personnel']['suffix'] ?? null,
        ])));

        $this->dispatch->dispatchNotification(
            domain: 'inventory.checkout',
            eventKey: 'inventory.checkout.triggered',
            notificationClass: SupplyCheckoutTriggeredNotification::class,
            payload: [
                'item_name' => $transaction['item']['name'] ?? 'Inventory item',
                'quantity' => $transaction['quantity'] ?? null,
                'unit' => $transaction['unit'] ?? null,
                'requester_name' => $requesterName !== '' ? $requesterName : 'Unknown requester',
            ],
            meta: [
                'transaction_id' => $transaction['id'],
                'item_id' => $transaction['item_id'] ?? null,
                'transac_type' => $transaction['transac_type'] ?? null,
            ],
            notifiableType: \App\Models\Transaction::class,
            notifiableId: (string) $transaction['id'],
        );
    }
}
