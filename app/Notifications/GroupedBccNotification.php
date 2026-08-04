<?php

namespace App\Notifications;

use Illuminate\Contracts\Mail\Mailable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class GroupedBccNotification extends Notification
{
    public function __construct(
        private readonly Notification $inner,
        private readonly array $bccRecipients,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage|Mailable
    {
        $message = $this->inner->toMail($notifiable);

        if ($message instanceof MailMessage) {
            $message->bcc = array_merge(
                $message->bcc,
                collect($this->bccRecipients)
                    ->map(fn (string $email) => [trim($email), null])
                    ->all(),
            );

            return $message;
        }

        if ($message instanceof Mailable && !empty($this->bccRecipients)) {
            $message->bcc($this->bccRecipients);
        }

        return $message;
    }
}

