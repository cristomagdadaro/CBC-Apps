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

class InventoryTransactionChanged implements ShouldBroadcast, ShouldDispatchAfterCommit
{
    use Dispatchable;
    use InteractsWithSockets;

    public function __construct(
        Transaction $transaction,
        public string $action,
    ) {
        $loadedTransaction = $transaction->loadMissing(['item', 'personnel']);

        $this->transaction = [
            'id' => $loadedTransaction->id,
            'item_id' => $loadedTransaction->item_id,
            'transac_type' => $loadedTransaction->transac_type,
            'quantity' => $loadedTransaction->quantity,
            'unit' => $loadedTransaction->unit,
            'barcode' => $loadedTransaction->barcode,
            'occurred_at' => optional($loadedTransaction->updated_at ?? $loadedTransaction->created_at)?->toIso8601String(),
            'item' => [
                'name' => $loadedTransaction->item?->name,
            ],
            'personnel' => [
                'fname' => $loadedTransaction->personnel?->fname,
                'mname' => $loadedTransaction->personnel?->mname,
                'lname' => $loadedTransaction->personnel?->lname,
                'suffix' => $loadedTransaction->personnel?->suffix,
            ],
        ];
    }

    public array $transaction;

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
        return [
            'type' => $this->broadcastAs(),
            'action' => $this->action,
            'transaction' => [
                'id' => $this->transaction['id'],
                'item_id' => $this->transaction['item_id'],
                'item_name' => $this->transaction['item']['name'] ?? null,
                'transac_type' => $this->transaction['transac_type'],
                'quantity' => $this->transaction['quantity'],
                'unit' => $this->transaction['unit'],
                'barcode' => $this->transaction['barcode'],
                'status_hint' => $this->transaction['transac_type'],
                'occurred_at' => $this->transaction['occurred_at'],
            ],
            'invalidate' => [
                'inventory.transactions',
                'inventory.checkout',
                'inventory.dashboard',
            ],
        ];
    }
}
