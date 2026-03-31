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
        $transaction = $event->transaction->loadMissing(['item', 'personnel']);

        if ($event->action !== 'created' || $transaction->transac_type !== 'outgoing') {
            return;
        }

        $requesterName = trim(implode(' ', array_filter([
            $transaction->personnel?->fname,
            $transaction->personnel?->mname,
            $transaction->personnel?->lname,
            $transaction->personnel?->suffix,
        ])));

        $this->dispatch->dispatchNotification(
            domain: 'inventory.checkout',
            eventKey: 'inventory.checkout.triggered',
            notificationClass: SupplyCheckoutTriggeredNotification::class,
            payload: [
                'item_name' => $transaction->item?->name ?? 'Inventory item',
                'quantity' => $transaction->quantity,
                'unit' => $transaction->unit,
                'requester_name' => $requesterName !== '' ? $requesterName : 'Unknown requester',
            ],
            meta: [
                'transaction_id' => $transaction->id,
                'item_id' => $transaction->item_id,
                'transac_type' => $transaction->transac_type,
            ],
            notifiableType: $transaction::class,
            notifiableId: (string) $transaction->id,
        );
    }
}
