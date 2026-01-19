<?php

namespace App\Mail;

use App\Models\Transaction;
use App\Enums\Inventory;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OutgoingTransactionNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Transaction $transaction)
    {
    }

    public function build(): self
    {
        $this->transaction->loadMissing(['item', 'personnel', 'user']);

        $remainingQuantity = Transaction::query()
            ->where('item_id', $this->transaction->item_id)
            ->where('barcode', $this->transaction->barcode)
            ->where('unit', $this->transaction->unit)
            ->selectRaw(
                'SUM(CASE WHEN transac_type = ? THEN quantity ELSE 0 END) as total_incoming, ' .
                'SUM(CASE WHEN transac_type = ? THEN ABS(quantity) ELSE 0 END) as total_outgoing',
                [Inventory::INCOMING->value, Inventory::OUTGOING->value]
            )
            ->first();

        $remaining = ($remainingQuantity?->total_incoming ?? 0) - ($remainingQuantity?->total_outgoing ?? 0);

        return $this->subject('New Outgoing Inventory Transaction')
            ->view('emails.outgoing-transaction-notification', [
                'transaction' => $this->transaction,
                'remainingQuantity' => $remaining,
            ]);
    }
}
