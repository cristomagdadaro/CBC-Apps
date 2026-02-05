<?php

namespace App\Observers;

use App\Models\EventSubformResponse;
use App\Models\Participant;
use App\Mail\EventSubformResponseNotification;
use Illuminate\Support\Facades\Mail;

class EventSubformResponseObserver
{
    /**
     * Subform types that insert/update participant data
     */
    private const PARTICIPANT_SYNC_TYPES = [
        'preregistration',
        'registration',
        'preregistration_biotech',
        'preregistration_quizbee',
    ];

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

    /**
     * Handle the EventSubformResponse "updated" event.
     * Sync participant data when response_data is updated.
     */
    public function updated(EventSubformResponse $response): void
    {
        // Only sync if this is a subform type that contains participant data
        if (!$this->shouldSyncParticipant($response)) {
            return;
        }

        // Only sync if participant_id exists
        if (!$response->participant_id) {
            return;
        }

        $this->syncParticipantData($response);
    }

    /**
     * Determine if participant data should be synced for this subform type
     */
    private function shouldSyncParticipant(EventSubformResponse $response): bool
    {
        return in_array($response->subform_type, self::PARTICIPANT_SYNC_TYPES);
    }

    /**
     * Sync response_data fields to the participant record
     */
    private function syncParticipantData(EventSubformResponse $response): void
    {
        $participant = Participant::find($response->participant_id);
        if (!$participant) {
            return;
        }

        $responseData = $response->response_data;
        if (!is_array($responseData)) {
            return;
        }

        $fieldsToSync = [
            'name',
            'email',
            'phone',
            'sex',
            'age',
            'organization',
            'designation',
            'is_ip',
            'is_pwd',
            'city_address',
            'province_address',
            'region_address',
            'country_address',
            'agreed_tc',
        ];

        $updateData = [];
        foreach ($fieldsToSync as $field) {
            if (array_key_exists($field, $responseData)) {
                $updateData[$field] = $responseData[$field];
            }
        }

        if (!empty($updateData)) {
            $participant->update($updateData);
        }
    }
}
