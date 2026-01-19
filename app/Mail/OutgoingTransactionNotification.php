<?php

namespace App\Mail;

use App\Models\Transaction;
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

        return $this->subject('New Outgoing Inventory Transaction')
            ->view('emails.outgoing-transaction-notification', [
                'transaction' => $this->transaction,
            ]);
    }
}
