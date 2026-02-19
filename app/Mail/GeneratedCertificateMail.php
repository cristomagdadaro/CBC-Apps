<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;

class GeneratedCertificateMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $attachmentPath,
        public string $displayName,
        public string $eventId
    ) {
    }

    public function build(): self
    {
        $mimeType = File::mimeType($this->attachmentPath) ?: 'application/octet-stream';

        return $this->subject('Your Certificate is Ready')
            ->view('emails.generated-certificate', [
                'eventId' => $this->eventId,
            ])
            ->attach($this->attachmentPath, [
                'as' => $this->displayName,
                'mime' => $mimeType,
            ]);
    }
}
