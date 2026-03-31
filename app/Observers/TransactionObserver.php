<?php

namespace App\Observers;

use App\Events\InventoryTransactionChanged;
use App\Models\Transaction;

class TransactionObserver
{
    public function created(Transaction $transaction): void
    {
        event(new InventoryTransactionChanged($transaction, 'created'));
    }

    public function updated(Transaction $transaction): void
    {
        event(new InventoryTransactionChanged($transaction, 'updated'));
    }

    public function deleted(Transaction $transaction): void
    {
        event(new InventoryTransactionChanged($transaction, 'deleted'));
    }
}
