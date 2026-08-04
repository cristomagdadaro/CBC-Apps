<?php

namespace Tests\Feature\LabEquipment;

use App\Mail\UseRequestLifecycleMail;
use App\Models\RequestFormPivot;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use Tests\WithTestRoles;

class RequestWorkflowTest extends TestCase
{
    use RefreshDatabase;
    use WithTestRoles;

    protected $seeder = \Database\Seeders\DatabaseSeeder::class;

    public function test_admin_can_transition_request_from_approved_to_released_to_returned(): void
    {
        Sanctum::actingAs($this->createAdminUser());
        Mail::fake();

        $pivot = RequestFormPivot::factory()->create([
            'request_status' => RequestFormPivot::STATUS_PENDING,
            'approved_by' => null,
            'approved_at' => null,
            'released_by' => null,
            'released_at' => null,
            'returned_by' => null,
            'returned_at' => null,
        ]);

        $this->putJson(route('api.requestFormPivot.put', ['request_pivot_id' => $pivot->id]), [
            'request_status' => RequestFormPivot::STATUS_APPROVED,
            'approval_constraint' => 'Return immediately after use.',
        ])->assertOk()
            ->assertJsonPath('request_status', RequestFormPivot::STATUS_APPROVED);

        $pivot->refresh();
        $this->assertNotNull($pivot->approved_at);
        $this->assertSame(RequestFormPivot::STATUS_APPROVED, $pivot->request_status);

        $this->putJson(route('api.requestFormPivot.put', ['request_pivot_id' => $pivot->id]), [
            'request_status' => RequestFormPivot::STATUS_RELEASED,
            'approval_constraint' => 'Return immediately after use.',
        ])->assertOk()
            ->assertJsonPath('request_status', RequestFormPivot::STATUS_RELEASED);

        $pivot->refresh();
        $this->assertNotNull($pivot->released_at);
        $this->assertSame(RequestFormPivot::STATUS_RELEASED, $pivot->request_status);

        $this->putJson(route('api.requestFormPivot.put', ['request_pivot_id' => $pivot->id]), [
            'request_status' => RequestFormPivot::STATUS_RETURNED,
            'approval_constraint' => 'Return immediately after use.',
        ])->assertOk()
            ->assertJsonPath('request_status', RequestFormPivot::STATUS_RETURNED);

        $pivot->refresh();
        $this->assertNotNull($pivot->returned_at);
        $this->assertSame(RequestFormPivot::STATUS_RETURNED, $pivot->request_status);

        Mail::assertQueued(UseRequestLifecycleMail::class, 3);
    }

    public function test_overdue_released_request_queues_reminder_email_from_scheduler(): void
    {
        Mail::fake();

        $pivot = RequestFormPivot::factory()->create([
            'request_status' => RequestFormPivot::STATUS_RELEASED,
            'released_at' => now()->subDay(),
            'returned_at' => null,
            'overdue_notified_at' => null,
        ]);

        $pivot->request_form()->update([
            'date_of_use' => now()->subDays(2)->toDateString(),
            'time_of_use' => '08:00:00',
            'date_of_use_end' => now()->subDay()->toDateString(),
            'time_of_use_end' => '09:00:00',
        ]);

        $this->artisan('fes:send-overdue-reminders')
            ->assertSuccessful();

        $pivot->refresh();

        $this->assertNotNull($pivot->overdue_notified_at);
        Mail::assertQueued(UseRequestLifecycleMail::class, fn (UseRequestLifecycleMail $mail) => $mail->event === 'overdue');
    }
}
