<?php

namespace Tests\Feature\Dashboard;

use App\Enums\Inventory as InventoryEnum;
use App\Models\Category;
use App\Models\Form;
use App\Models\Item;
use App\Models\LaboratoryEquipmentLog;
use App\Models\Personnel;
use App\Models\RentalVehicle;
use App\Models\RentalVenue;
use App\Models\RequestFormPivot;
use App\Models\Supplier;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class DashboardControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_dashboard_with_aggregated_stats(): void
    {
        $user = User::factory()->create(['is_admin' => true]);
        $this->actingAs($user);

        Form::factory()->create([
            'is_suspended' => false,
            'is_expired' => false,
            'date_from' => now()->addDay()->toDateString(),
        ]);

        RequestFormPivot::factory()->create(['request_status' => 'pending']);
        RentalVehicle::factory()->create(['status' => 'approved']);
        RentalVenue::factory()->create(['status' => 'pending']);

        $transaction = $this->createIncomingTransaction($user);

        LaboratoryEquipmentLog::query()->create([
            'equipment_id' => $transaction->item_id,
            'personnel_id' => $transaction->personnel_id,
            'status' => 'active',
            'started_at' => now()->subHour(),
            'end_use_at' => now()->addHour(),
            'active_lock' => true,
            'checked_in_by' => $user->id,
        ]);

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Dashboard')
                ->where('stats.events.total', 1)
                ->where('stats.events.active', 1)
                ->where('stats.events.upcoming', 1)
                ->where('stats.access_requests.pending', 1)
                ->where('stats.vehicle_rentals.approved', 1)
                ->where('stats.venue_rentals.pending', 1)
                ->where('stats.inventory.transactions_today', 1)
                ->where('stats.laboratory_equipment.active', 1)
                ->has('recentTransactions', 1)
                ->where('recentTransactions.0.id', $transaction->id)
            );
    }

    private function createIncomingTransaction(User $user): Transaction
    {
        $category = Category::factory()->create();
        $supplier = Supplier::factory()->create();
        $item = Item::factory()->create([
            'category_id' => $category->id,
            'supplier_id' => $supplier->id,
        ]);
        $personnel = Personnel::factory()->create(['employee_id' => 'EMP-DASH']);

        return Transaction::query()->create([
            'item_id' => $item->id,
            'barcode' => 'CBC-01-100001',
            'transac_type' => InventoryEnum::INCOMING->value,
            'quantity' => 12,
            'unit_price' => 15,
            'unit' => 'pc',
            'total_cost' => 180,
            'personnel_id' => $personnel->id,
            'user_id' => $user->id,
            'expiration' => now()->addMonth(),
            'remarks' => 'Dashboard seed',
            'project_code' => 'PC-DASH',
        ]);
    }
}
