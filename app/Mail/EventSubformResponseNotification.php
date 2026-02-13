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
        // Ensure relations are loaded on the existing model instance
        $response = $this->response->load(['parent', 'registration.participant']);

        $title = $response->parent?->title ?? 'New Response';

        return $this->subject($title . ' - New Response Received')
            ->view('emails.event-subform-response-notification', [
                'response' => $response,
            ]);
    }
}
