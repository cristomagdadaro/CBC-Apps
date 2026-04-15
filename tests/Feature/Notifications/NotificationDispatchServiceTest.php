<?php

namespace Tests\Feature\Notifications;

use App\Jobs\DeliverNotificationMessageJob;
use App\Models\Option;
use App\Models\User;
use App\Notifications\FormResponseReceivedNotification;
use App\Services\Notifications\NotificationDispatchService;
use Illuminate\Mail\Mailable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class NotificationDispatchServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_per_recipient_audit_logs_and_grouped_notification_job(): void
    {
        config()->set('notifications.domains.test.domain', [
            'enabled' => true,
            'queue' => 'notifications',
            'delivery_mode' => 'grouped',
            'option_keys' => ['test_notification_emails'],
            'roles' => [],
        ]);
        config()->set('notifications.grouped.to.address', 'ops@example.com');
        config()->set('notifications.grouped.to.name', 'CBC Ops');

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
            'delivery_mode' => 'grouped',
        ]);

        Queue::assertPushed(DeliverNotificationMessageJob::class, function (DeliverNotificationMessageJob $job) {
            return $job->deliveryMode === 'grouped'
                && $job->recipientEmails === ['first@example.com', 'second@example.com']
                && count($job->logIds) === 2
                && $job->groupedToAddress === 'ops@example.com'
                && $job->groupedToName === 'CBC Ops';
        });
        Queue::assertPushed(DeliverNotificationMessageJob::class, 1);
    }

    public function test_it_keeps_individual_delivery_mode_when_configured(): void
    {
        config()->set('notifications.domains.test.domain', [
            'enabled' => true,
            'queue' => 'notifications',
            'delivery_mode' => 'individual',
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
            'recipient_email' => 'first@example.com',
            'delivery_mode' => 'individual',
        ]);

        Queue::assertPushed(DeliverNotificationMessageJob::class, 2);
    }

    public function test_it_groups_mailable_dispatch_per_domain_while_preserving_audit_logs(): void
    {
        config()->set('notifications.domains.test.mail', [
            'enabled' => true,
            'queue' => 'mail',
            'delivery_mode' => 'grouped',
            'option_keys' => ['test_mail_emails'],
            'roles' => [],
        ]);
        config()->set('notifications.grouped.to.address', 'ops@example.com');

        User::factory()->create(['email' => 'first@example.com']);
        User::factory()->create(['email' => 'second@example.com']);

        Option::factory()->create([
            'key' => 'test_mail_emails',
            'value' => json_encode(['first@example.com', 'second@example.com']),
            'group' => 'notifications',
        ]);

        Queue::fake();

        app(NotificationDispatchService::class)->dispatchMailable(
            domain: 'test.mail',
            eventKey: 'test.mail.created',
            mailableClass: TestGroupedNotificationMailable::class,
            constructorArguments: ['Grouped Subject'],
        );

        $this->assertDatabaseCount('notification_logs', 2);
        $this->assertDatabaseHas('notification_logs', [
            'domain' => 'test.mail',
            'recipient_email' => 'first@example.com',
            'delivery_mode' => 'grouped',
        ]);

        Queue::assertPushed(DeliverNotificationMessageJob::class, function (DeliverNotificationMessageJob $job) {
            return $job->mode === 'mailable'
                && $job->deliveryMode === 'grouped'
                && $job->recipientEmails === ['first@example.com', 'second@example.com']
                && count($job->logIds) === 2;
        });
        Queue::assertPushed(DeliverNotificationMessageJob::class, 1);
    }
}

class TestGroupedNotificationMailable extends Mailable
{
    public function __construct(private readonly string $subjectLine)
    {
    }

    public function build(): self
    {
        return $this->subject($this->subjectLine)
            ->html('<p>Grouped notification body</p>');
    }
}
