<?php

namespace App\Observers;

use App\Enums\Inventory;
use App\Mail\OutgoingTransactionNotification;
use App\Models\Option;
use App\Models\Transaction;
use Illuminate\Support\Facades\Mail;

class TransactionObserver
{
    public function created(Transaction $transaction): void
    {
        if ($transaction->transac_type !== Inventory::OUTGOING->value) {
            return;
        }

        $recipient = Option::getEventResponseNotificationEmail();
        if (empty($recipient)) {
            return;
        }

        Mail::to($recipient)->send(new OutgoingTransactionNotification($transaction));
    }
}
