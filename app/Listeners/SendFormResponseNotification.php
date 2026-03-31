<?php

namespace App\Listeners;

use App\Events\FormResponseChanged;
use App\Notifications\FormResponseReceivedNotification;
use App\Services\Notifications\NotificationDispatchService;

class SendFormResponseNotification
{
    public function __construct(private readonly NotificationDispatchService $dispatch)
    {
    }

    public function handle(FormResponseChanged $event): void
    {
        if ($event->action !== 'created') {
            return;
        }

        $response = $event->response->loadMissing(['parent.form', 'participant', 'registration.participant']);
        $participantName = $response->participant?->name
            ?? $response->registration?->participant?->name
            ?? 'A participant';

        $this->dispatch->dispatchNotification(
            domain: 'forms.responses',
            eventKey: 'forms.response.created',
            notificationClass: FormResponseReceivedNotification::class,
            payload: [
                'event_id' => $event->eventId,
                'event_title' => $response->parent?->form?->title ?? 'Event Form',
                'subform_type' => $response->subform_type,
                'participant_name' => $participantName,
            ],
            meta: [
                'response_id' => $response->id,
                'subform_type' => $response->subform_type,
            ],
            notifiableType: $response::class,
            notifiableId: (string) $response->id,
        );
    }
}
