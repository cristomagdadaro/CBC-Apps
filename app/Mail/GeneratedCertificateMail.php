<?php

namespace App\Mail;

use App\Models\Form;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;

class GeneratedCertificateMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public ?string $recipientName = null;

    public function __construct(
        public string $attachmentPath,
        public string $displayName,
        public string $eventId
    ) {
    }

    public function withRecipientName(?string $recipientName): self
    {
        $this->recipientName = $recipientName;
        return $this;
    }

    public function build(): self
    {
        $mimeType = File::mimeType($this->attachmentPath) ?: 'application/octet-stream';

        $event = Form::query()
            ->where('event_id', $this->eventId)
            ->first(['title', 'date_from', 'date_to']);

        $eventDate = null;
        if ($event?->date_from && $event?->date_to) {
            $eventDate = $event->date_from->format('M d, Y') . ' to ' . $event->date_to->format('M d, Y');
        } elseif ($event?->date_from) {
            $eventDate = $event->date_from->format('M d, Y');
        } elseif ($event?->date_to) {
            $eventDate = $event->date_to->format('M d, Y');
        }

        return $this->subject('Your Certificate is Ready')
            ->view('emails.generated-certificate', [
                'eventId' => $this->eventId,
                'recipientName' => $this->recipientName,
                'eventTitle' => $event?->title,
                'eventDate' => $eventDate,
            ])
            ->attach($this->attachmentPath, [
                'as' => $this->displayName,
                'mime' => $mimeType,
            ]);
    }
}
