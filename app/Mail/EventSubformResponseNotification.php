<?php

namespace App\Mail;

use App\Models\EventSubformResponse;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EventSubformResponseNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public EventSubformResponse $response)
    {
    }

    public function build(): self
    {
        return $this->subject('New EventSubformResponse Submitted')
            ->view('emails.event-subform-response-notification', [
                'response' => $this->response->with(['formParent', 'registration.participant'])->first(),
            ]);
    }
}
