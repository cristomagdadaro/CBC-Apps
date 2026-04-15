<?php

namespace Tests\Feature\Notifications;

use App\Events\EquipmentLogChanged;
use App\Listeners\SendEquipmentLogLifecycleNotification;
use App\Models\Category;
use App\Models\Item;
use App\Models\LaboratoryEquipmentLog;
use App\Models\Personnel;
use App\Models\Supplier;
use App\Services\Notifications\NotificationDispatchService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class EquipmentLogLifecycleNotificationListenerTest extends TestCase
{
    use RefreshDatabase;

    public function test_created_action_sends_checked_in_status_label(): void
    {
        $log = $this->createEquipmentLog();
        $dispatch = Mockery::mock(NotificationDispatchService::class);
        $listener = new SendEquipmentLogLifecycleNotification($dispatch);

        $dispatch->shouldReceive('dispatchNotification')
            ->once()
            ->withArgs(function (
                string $domain,
                string $eventKey,
                string $notificationClass,
                array $payload,
                array $meta,
                ?string $notifiableType,
                ?string $notifiableId
            ) use ($log) {
                return $domain === 'laboratory.logs'
                    && $eventKey === 'equipment.log.created'
                    && $payload['status_label'] === 'checked in'
                    && $payload['equipment_name'] === $log->equipment->name
                    && $payload['personnel_name'] === 'Taylor Marie Santos'
                    && $meta['action'] === 'created'
                    && $notifiableType === LaboratoryEquipmentLog::class
                    && $notifiableId === (string) $log->id;
            });

        $listener->handle(new EquipmentLogChanged('created', 'laboratory', (string) $log->equipment_id, $log));

        $this->addToAssertionCount(1);
    }

    public function test_completed_action_sends_checked_out_status_label(): void
    {
        $log = $this->createEquipmentLog();
        $dispatch = Mockery::mock(NotificationDispatchService::class);
        $listener = new SendEquipmentLogLifecycleNotification($dispatch);

        $dispatch->shouldReceive('dispatchNotification')
            ->once()
            ->withArgs(function (
                string $domain,
                string $eventKey,
                string $notificationClass,
                array $payload,
                array $meta,
                ?string $notifiableType,
                ?string $notifiableId
            ) use ($log) {
                return $domain === 'laboratory.logs'
                    && $eventKey === 'equipment.log.completed'
                    && $payload['status_label'] === 'checked out'
                    && $meta['action'] === 'completed'
                    && $notifiableId === (string) $log->id;
            });

        $listener->handle(new EquipmentLogChanged('completed', 'laboratory', (string) $log->equipment_id, $log));

        $this->addToAssertionCount(1);
    }

    private function createEquipmentLog(): LaboratoryEquipmentLog
    {
        $category = Category::factory()->create();
        $supplier = Supplier::factory()->create();
        $item = Item::factory()->create([
            'name' => 'PCR Machine',
            'category_id' => $category->id,
            'supplier_id' => $supplier->id,
        ]);
        $personnel = Personnel::factory()->create([
            'fname' => 'Taylor',
            'mname' => 'Marie',
            'lname' => 'Santos',
            'suffix' => null,
        ]);

        return LaboratoryEquipmentLog::factory()->active()->create([
            'equipment_id' => $item->id,
            'personnel_id' => $personnel->id,
        ])->load(['equipment', 'personnel']);
    }

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }
}


