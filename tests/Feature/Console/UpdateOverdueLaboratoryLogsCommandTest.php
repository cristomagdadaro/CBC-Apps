<?php

namespace Tests\Feature\Console;

use App\Models\Category;
use App\Models\Item;
use App\Models\LaboratoryEquipmentLog;
use App\Models\Personnel;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateOverdueLaboratoryLogsCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_marks_expired_active_logs_as_overdue(): void
    {
        $category = Category::factory()->create(['name' => 'Laboratory Equipment']);
        $supplier = Supplier::factory()->create();
        $item = Item::factory()->create([
            'category_id' => $category->id,
            'supplier_id' => $supplier->id,
        ]);
        $personnel = Personnel::factory()->create(['employee_id' => 'EMP-LAB-CMD']);
        $user = User::factory()->create();

        $overdue = LaboratoryEquipmentLog::query()->create([
            'equipment_id' => $item->id,
            'personnel_id' => $personnel->id,
            'status' => 'active',
            'started_at' => now()->subDay(),
            'end_use_at' => now()->subMinutes(10),
            'active_lock' => true,
            'checked_in_by' => $user->id,
        ]);

        LaboratoryEquipmentLog::query()->create([
            'equipment_id' => $item->id,
            'personnel_id' => $personnel->id,
            'status' => 'active',
            'started_at' => now()->subHour(),
            'end_use_at' => now()->addMinutes(30),
            'active_lock' => true,
            'checked_in_by' => $user->id,
        ]);

        $this->artisan('laboratory:mark-overdue')
            ->expectsOutput('1 laboratory log(s) marked as overdue.')
            ->assertSuccessful();

        $this->assertSame('overdue', $overdue->fresh()->status);
    }
}
