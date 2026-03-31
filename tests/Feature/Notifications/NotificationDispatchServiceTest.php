<?php

namespace Tests\Feature\Notifications;

use App\Jobs\DeliverNotificationMessageJob;
use App\Models\Option;
use App\Models\User;
use App\Notifications\FormResponseReceivedNotification;
use App\Services\Notifications\NotificationDispatchService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class NotificationDispatchServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_audit_logs_and_dispatches_jobs(): void
    {
        config()->set('notifications.domains.test.domain', [
            'enabled' => true,
            'queue' => 'notifications',
            'option_keys' => ['test_notification_emails'],
            'roles' => [],
        ]);

        User::factory()->create(['email' => 'first@example.com']);
        User::factory()->create(['email' => 'second@example.com']);

        Option::factory()->create([
            'key' => 'test_notification_emails',
            'value' => json_encode(['first@example.com', 'second@example.com']),
            'group' => 'notifications',
        ]);

        Queue::fake();

        app(NotificationDispatchService::class)->dispatchNotification(
            domain: 'test.domain',
            eventKey: 'test.domain.created',
            notificationClass: FormResponseReceivedNotification::class,
            payload: [
                'event_title' => 'Realtime Demo',
                'event_id' => 'evt-001',
                'subform_type' => 'registration',
                'participant_name' => 'Tester',
            ],
        );

        $this->assertDatabaseCount('notification_logs', 2);
        $this->assertDatabaseHas('notification_logs', [
            'domain' => 'test.domain',
            'event_key' => 'test.domain.created',
            'recipient_email' => 'first@example.com',
            'status' => 'queued',
        ]);

        Queue::assertPushed(DeliverNotificationMessageJob::class, 2);
    }
}
