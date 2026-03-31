<?php

namespace App\Events;

use App\Models\Transaction;
use App\Support\Broadcasting\ChannelNames;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class InventoryTransactionChanged implements ShouldBroadcast, ShouldDispatchAfterCommit
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public Transaction $transaction,
        public string $action,
    ) {
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel(ChannelNames::inventoryTransactions()),
            new PrivateChannel(ChannelNames::inventoryCheckout()),
            new PrivateChannel(ChannelNames::staffDashboard()),
            new Channel(ChannelNames::publicInventoryStock()),
        ];
    }

    public function broadcastAs(): string
    {
        return 'inventory.transaction.changed';
    }

    public function broadcastWith(): array
    {
        $transaction = $this->transaction->loadMissing(['item', 'personnel']);

        return [
            'type' => $this->broadcastAs(),
            'action' => $this->action,
            'transaction' => [
                'id' => $transaction->id,
                'item_id' => $transaction->item_id,
                'item_name' => $transaction->item?->name,
                'transac_type' => $transaction->transac_type,
                'quantity' => $transaction->quantity,
                'unit' => $transaction->unit,
                'barcode' => $transaction->barcode,
                'status_hint' => $transaction->transac_type,
                'occurred_at' => optional($transaction->updated_at ?? $transaction->created_at)?->toIso8601String(),
            ],
            'invalidate' => [
                'inventory.transactions',
                'inventory.checkout',
                'inventory.dashboard',
            ],
        ];
    }
}
