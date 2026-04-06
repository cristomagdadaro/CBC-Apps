<?php

namespace Tests\Unit\Services\Laboratory;

use App\Enums\Inventory as InventoryEnum;
use App\Models\Item;
use App\Models\LaboratoryEquipmentLog;
use App\Models\Personnel;
use App\Models\Supplier;
use App\Models\Transaction;
use App\Models\User;
use App\Mail\LaboratoryEquipmentLogOverdueMail;
use App\Services\Laboratory\LaboratoryLogService;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class LaboratoryLogServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_resolve_equipment_id_from_barcode_returns_item_id(): void
    {
        ['item' => $item] = $this->createLaboratoryInventoryContext();

        Transaction::query()->create([
            'item_id' => $item->id,
            'barcode' => 'CBC-LAB-0001',
            'transac_type' => InventoryEnum::INCOMING->value,
            'quantity' => 1,
            'unit_price' => 100,
            'unit' => 'pc',
            'total_cost' => 100,
            'personnel_id' => Personnel::query()->first()->id,
            'user_id' => User::query()->first()->id,
            'expiration' => now()->addMonth(),
            'remarks' => 'Laboratory stock',
        ]);

        $resolved = app(LaboratoryLogService::class)->resolveEquipmentId('CBC-LAB-0001');

        $this->assertSame($item->id, $resolved);
    }

    public function test_mark_overdue_updates_only_expired_active_logs(): void
    {
        ['item' => $item, 'personnel' => $personnel, 'user' => $user] = $this->createLaboratoryInventoryContext();

        $overdue = LaboratoryEquipmentLog::query()->create([
            'equipment_id' => $item->id,
            'personnel_id' => $personnel->id,
            'status' => 'active',
            'started_at' => now()->subDay(),
            'end_use_at' => now()->subHour(),
            'active_lock' => true,
            'checked_in_by' => $user->id,
        ]);

        $active = LaboratoryEquipmentLog::query()->create([
            'equipment_id' => $item->id,
            'personnel_id' => $personnel->id,
            'status' => 'active',
            'started_at' => now()->subHour(),
            'end_use_at' => now()->addHour(),
            'active_lock' => true,
            'checked_in_by' => $user->id,
        ]);

        $count = app(LaboratoryLogService::class)->markOverdue();

        $this->assertSame(1, $count);
        $this->assertSame('overdue', $overdue->fresh()->status);
        $this->assertSame('active', $active->fresh()->status);
    }

    public function test_mark_overdue_sends_email_when_personnel_has_email(): void
    {
        Mail::fake();

        ['item' => $item, 'personnel' => $personnel, 'user' => $user] = $this->createLaboratoryInventoryContext();
        $personnel->forceFill(['email' => 'lab.user@example.test'])->save();

        LaboratoryEquipmentLog::query()->create([
            'equipment_id' => $item->id,
            'personnel_id' => $personnel->id,
            'status' => 'active',
            'started_at' => now()->subDay(),
            'end_use_at' => now()->subHour(),
            'active_lock' => true,
            'checked_in_by' => $user->id,
        ]);

        app(LaboratoryLogService::class)->markOverdue();

        Mail::assertSent(LaboratoryEquipmentLogOverdueMail::class, function (LaboratoryEquipmentLogOverdueMail $mail) use ($personnel) {
            return $mail->hasTo($personnel->email);
        });
    }

    public function test_check_in_requires_personnel_profile_initialization(): void
    {
        $context = $this->createLaboratoryInventoryContext();
        $item = $context['item'];
        $user = $context['user'];

        Transaction::query()->create([
            'item_id' => $item->id,
            'barcode' => 'CBC-LAB-0002',
            'transac_type' => InventoryEnum::INCOMING->value,
            'quantity' => 1,
            'unit_price' => 100,
            'unit' => 'pc',
            'total_cost' => 100,
            'personnel_id' => Personnel::query()->first()->id,
            'user_id' => $user->id,
            'expiration' => now()->addMonth(),
            'remarks' => 'Laboratory stock',
        ]);

        $freshPersonnel = Personnel::factory()->create([
            'employee_id' => 'EMP-FRESH-LAB',
            'updated_at' => null,
        ]);

        try {
            app(LaboratoryLogService::class)->checkIn($item->id, [
                'employee_id' => $freshPersonnel->employee_id,
                'end_use_at' => now()->addHour()->toIso8601String(),
                'purpose' => 'Calibration',
            ]);

            $this->fail('Expected a profile initialization exception.');
        } catch (HttpException $exception) {
            $this->assertSame(409, $exception->getStatusCode());
            $this->assertSame(
                'Please update your personnel information before checking in equipment.',
                $exception->getMessage()
            );
        }
    }

    public function test_check_in_requires_personnel_email(): void
    {
        $context = $this->createLaboratoryInventoryContext();
        $item = $context['item'];
        $user = $context['user'];

        Transaction::query()->create([
            'item_id' => $item->id,
            'barcode' => 'CBC-LAB-0003',
            'transac_type' => InventoryEnum::INCOMING->value,
            'quantity' => 1,
            'unit_price' => 100,
            'unit' => 'pc',
            'total_cost' => 100,
            'personnel_id' => Personnel::query()->first()->id,
            'user_id' => $user->id,
            'expiration' => now()->addMonth(),
            'remarks' => 'Laboratory stock',
        ]);

        $personnelWithoutEmail = Personnel::factory()->create([
            'employee_id' => 'EMP-NO-EMAIL',
            'updated_at' => now(),
            'email' => null,
        ]);

        try {
            app(LaboratoryLogService::class)->checkIn($item->id, [
                'employee_id' => $personnelWithoutEmail->employee_id,
                'end_use_at' => now()->addHour()->toIso8601String(),
                'purpose' => 'Calibration',
            ]);

            $this->fail('Expected an email-required exception.');
        } catch (HttpException $exception) {
            $this->assertSame(409, $exception->getStatusCode());
            $this->assertSame(
                'Please provide your email before checking in equipment.',
                $exception->getMessage()
            );
        }
    }

    private function createLaboratoryInventoryContext(): array
    {
        DB::table('categories')->insert([
            'id' => 7,
            'name' => 'Laboratory Equipment',
            'description' => 'Laboratory items',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $supplier = Supplier::factory()->create();
        $item = Item::factory()->create([
            'category_id' => 7,
            'supplier_id' => $supplier->id,
        ]);
        $personnel = Personnel::factory()->create(['employee_id' => 'EMP-LAB-SVC']);
        $user = User::factory()->create();

        return compact('item', 'personnel', 'user');
    }
}
