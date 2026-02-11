<?php

namespace Database\Factories;

use App\Models\Item;
use App\Models\LaboratoryEquipmentLog;
use App\Models\Personnel;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LaboratoryEquipmentLog>
 */
class LaboratoryEquipmentLogFactory extends Factory
{
    protected $model = LaboratoryEquipmentLog::class;

    public function definition(): array
    {
        $status = $this->faker->randomElement(['active', 'completed', 'overdue', 'cancelled']);
        $startedAt = Carbon::now()->subHours($this->faker->numberBetween(1, 72));
        $endUseAt = (clone $startedAt)->addHours($this->faker->numberBetween(1, 8));

        $actualEndAt = null;
        $activeLock = true;

        if ($status === 'completed') {
            $actualEndAt = (clone $endUseAt)->addMinutes($this->faker->numberBetween(1, 60));
            $activeLock = false;
        } elseif ($status === 'cancelled') {
            $activeLock = false;
        }

        return [
            'equipment_id' => $this->resolveEquipmentId(),
            'personnel_id' => $this->resolvePersonnelId(),
            'status' => $status,
            'started_at' => $startedAt,
            'end_use_at' => $endUseAt,
            'actual_end_at' => $actualEndAt,
            'active_lock' => $activeLock,
            'purpose' => $this->faker->optional()->sentence(),
            'checked_in_by' => $this->resolveUserId(),
            'checked_out_by' => $actualEndAt ? $this->resolveUserId() : null,
        ];
    }

    public function active(): self
    {
        return $this->state(function () {
            $startedAt = Carbon::now()->subHours($this->faker->numberBetween(1, 12));
            return [
                'status' => 'active',
                'started_at' => $startedAt,
                'end_use_at' => (clone $startedAt)->addHours($this->faker->numberBetween(1, 6)),
                'actual_end_at' => null,
                'active_lock' => true,
                'checked_out_by' => null,
            ];
        });
    }

    public function overdue(): self
    {
        return $this->state(function () {
            $startedAt = Carbon::now()->subHours($this->faker->numberBetween(24, 72));
            $endUseAt = (clone $startedAt)->addHours($this->faker->numberBetween(1, 6));
            return [
                'status' => 'overdue',
                'started_at' => $startedAt,
                'end_use_at' => $endUseAt,
                'actual_end_at' => null,
                'active_lock' => true,
                'checked_out_by' => null,
            ];
        });
    }

    public function completed(): self
    {
        return $this->state(function () {
            $startedAt = Carbon::now()->subHours($this->faker->numberBetween(6, 48));
            $endUseAt = (clone $startedAt)->addHours($this->faker->numberBetween(1, 6));
            return [
                'status' => 'completed',
                'started_at' => $startedAt,
                'end_use_at' => $endUseAt,
                'actual_end_at' => (clone $endUseAt)->addMinutes($this->faker->numberBetween(1, 90)),
                'active_lock' => false,
            ];
        });
    }

    private function resolveEquipmentId(): ?string
    {
        $equipment = Transaction::query()
            ->select('items.id')
            ->join('items', 'transactions.item_id', '=', 'items.id')
            ->join('categories', 'items.category_id', '=', 'categories.id')
            ->where(function ($query) {
                $query->where('categories.id', 7)
                    ->orWhere('categories.name', 'Laboratory Equipment');
            })
            ->groupBy('items.id')
            ->inRandomOrder()
            ->first();

        if ($equipment) {
            return $equipment->id;
        }

        return Item::query()->inRandomOrder()->value('id');
    }

    private function resolvePersonnelId(): ?int
    {
        return Personnel::query()->inRandomOrder()->value('id');
    }

    private function resolveUserId(): ?int
    {
        return User::query()->inRandomOrder()->value('id');
    }
}
