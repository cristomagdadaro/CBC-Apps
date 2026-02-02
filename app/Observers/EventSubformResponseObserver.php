<?php

namespace App\Observers;

use App\Models\EventSubformResponse;
use App\Mail\EventSubformResponseNotification;
use Illuminate\Support\Facades\Mail;

class EventSubformResponseObserver
{
    /**
     * Handle the EventSubformResponse "created" event.
     */
    public function created(EventSubformResponse $response)
    {
        $notificationEmail = config('system.event_response_notification_email');
        if (empty($notificationEmail)) {
            return;
        }
        Mail::to($notificationEmail)
            ->send(new EventSubformResponseNotification($response));
    }
}
