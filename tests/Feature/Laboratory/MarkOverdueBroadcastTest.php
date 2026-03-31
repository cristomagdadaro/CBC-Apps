<?php

namespace Tests\Feature\Laboratory;

use App\Events\EquipmentLogChanged;
use App\Models\Category;
use App\Models\Item;
use App\Models\LaboratoryEquipmentLog;
use App\Models\Personnel;
use App\Models\Supplier;
use App\Services\Laboratory\LaboratoryLogService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class MarkOverdueBroadcastTest extends TestCase
{
    use RefreshDatabase;

    public function test_mark_overdue_dispatches_equipment_log_event(): void
    {
        Event::fake([EquipmentLogChanged::class]);

        $category = Category::factory()->create([
            'id' => 7,
            'name' => 'Laboratory Equipment',
        ]);

        $supplier = Supplier::factory()->create();
        $item = Item::factory()->create([
            'category_id' => $category->id,
            'supplier_id' => $supplier->id,
            'name' => 'Spectrometer',
        ]);

        $personnel = Personnel::query()->create([
            'fname' => 'Jane',
            'lname' => 'Doe',
            'employee_id' => 'EMP-001',
            'position' => 'Scientist',
            'phone' => '09123456789',
            'address' => 'Science City',
            'email' => 'jane@example.com',
        ]);

        $log = LaboratoryEquipmentLog::query()->create([
            'equipment_id' => $item->id,
            'personnel_id' => $personnel->id,
            'status' => 'active',
            'started_at' => now()->subDay(),
            'end_use_at' => now()->subHour(),
            'active_lock' => true,
            'purpose' => 'Testing',
        ]);

        $count = app(LaboratoryLogService::class)->markOverdue();

        $this->assertSame(1, $count);
        $this->assertSame('overdue', $log->fresh()->status);

        Event::assertDispatched(EquipmentLogChanged::class, function (EquipmentLogChanged $event) use ($item) {
            return $event->action === 'overdue'
                && $event->equipmentId === $item->id
                && $event->equipmentType === 'laboratory';
        });
    }
}
