<?php

namespace Tests\Feature;

use App\Enums\Inventory as InventoryEnum;
use App\Models\Category;
use App\Models\Item;
use App\Models\Personnel;
use App\Models\SuppEquipReport;
use App\Models\Supplier;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class TransactionReportsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_includes_attached_reports_for_incoming_transactions(): void
    {
        ['item' => $item, 'personnel' => $personnel, 'user' => $user] = $this->buildInventoryContext();

        $this->actingAs($user);

        $transaction = Transaction::create([
            'item_id' => $item->id,
            'barcode' => 'CBC-01-000001',
            'transac_type' => InventoryEnum::INCOMING->value,
            'quantity' => 15,
            'unit_price' => 10,
            'unit' => 'pc',
            'total_cost' => 150,
            'personnel_id' => $personnel->id,
            'user_id' => $user->id,
            'expiration' => now()->addMonth(),
            'remarks' => 'Initial stock',
            'project_code' => 'PC-10001',
        ]);

        SuppEquipReport::factory()->count(2)->create([
            'transaction_id' => $transaction->id,
            'item_id' => $item->id,
            'user_id' => $user->id,
            'report_type' => 'incident',
            'report_data' => [
                'details' => 'Damaged box',
            ],
            'reported_at' => now(),
        ]);

        $this->get(route('transactions.show', $transaction->id))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Inventory/Transactions/components/IncomingUpdateForm')
                ->has('attachedReports', 2)
                ->where('attachedReports.0.transaction_id', $transaction->id)
                ->where('attachedReports.0.item.id', $item->id)
            );
    }

    /** @test */
    public function it_includes_attached_reports_for_outgoing_transactions(): void
    {
        ['item' => $item, 'personnel' => $personnel, 'user' => $user] = $this->buildInventoryContext();

        $this->actingAs($user);

        $incoming = Transaction::create([
            'item_id' => $item->id,
            'barcode' => 'CBC-02-000010',
            'transac_type' => InventoryEnum::INCOMING->value,
            'quantity' => 20,
            'unit_price' => 5,
            'unit' => 'pc',
            'total_cost' => 100,
            'personnel_id' => $personnel->id,
            'user_id' => $user->id,
            'expiration' => now()->addMonth(),
            'remarks' => 'Seed stock',
            'project_code' => 'PC-20001',
        ]);

        $outgoing = Transaction::create([
            'item_id' => $item->id,
            'barcode' => $incoming->barcode,
            'transac_type' => InventoryEnum::OUTGOING->value,
            'quantity' => -5,
            'unit_price' => 5,
            'unit' => 'pc',
            'total_cost' => -25,
            'personnel_id' => $personnel->id,
            'user_id' => $user->id,
            'expiration' => now()->addMonth(),
            'remarks' => 'Issued to lab',
            'project_code' => 'PC-20002',
        ]);

        SuppEquipReport::factory()->create([
            'transaction_id' => $outgoing->id,
            'item_id' => $item->id,
            'user_id' => $user->id,
            'report_type' => 'audit',
            'report_data' => [
                'details' => 'Issued for experiment',
            ],
            'reported_at' => now(),
        ]);

        $this->get(route('transactions.show', $outgoing->id))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Inventory/Transactions/components/OutgoingUpdateForm')
                ->has('attachedReports', 1)
                ->where('attachedReports.0.transaction_id', $outgoing->id)
                ->where('attachedReports.0.item.id', $item->id)
                ->where('attachedReports.0.transaction.barcode', $incoming->barcode)
            );
    }

    private function buildInventoryContext(): array
    {
        $category = Category::factory()->create();
        $supplier = Supplier::factory()->create();
        $item = Item::factory()->create([
            'category_id' => $category->id,
            'supplier_id' => $supplier->id,
        ]);
        $personnel = Personnel::factory()->create();
        $user = User::factory()->create();

        return compact('item', 'personnel', 'user');
    }
}
