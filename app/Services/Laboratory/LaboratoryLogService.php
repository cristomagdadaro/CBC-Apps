<?php

namespace App\Services\Laboratory;

use App\Models\Item;
use App\Models\LaboratoryEquipmentLog;
use App\Models\Personnel;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class LaboratoryLogService
{
    public function listEligibleEquipment(): Collection
    {
        return Transaction::query()
            ->select([
                'items.id as equipment_id',
                'items.name',
                'items.brand',
                'items.description',
                'items.category_id',
                'categories.name as category_name',
                DB::raw('MAX(transactions.barcode) as barcode'),
            ])
            ->join('items', 'transactions.item_id', '=', 'items.id')
            ->join('categories', 'items.category_id', '=', 'categories.id')
            ->where(function (Builder $query) {
                $query->where('categories.id', 7)
                    ->orWhere('categories.name', 'Laboratory Equipment');
            })
            ->groupBy('items.id', 'items.name', 'items.brand', 'items.description', 'items.category_id', 'categories.name')
            ->orderBy('items.name')
            ->get();
    }

    public function getEquipmentDetails(string $equipmentId): array
    {
        $equipment = $this->findEligibleEquipment($equipmentId);

        if (!$equipment) {
            return [
                'equipment' => null,
                'active_log' => null,
                'allowed_actions' => [],
            ];
        }

        $activeLog = $this->getActiveLog($equipmentId);

        return [
            'equipment' => $equipment,
            'active_log' => $activeLog,
            'allowed_actions' => $activeLog ? ['check-out'] : ['check-in'],
            'max_end_use_hours' => (int) config('laboratory.max_end_use_hours', 24),
        ];
    }

    public function checkIn(string $equipmentId, array $payload): LaboratoryEquipmentLog
    {
        $equipment = $this->findEligibleEquipment($equipmentId);
        if (!$equipment) {
            abort(404, 'Equipment not found or not eligible for laboratory logs.');
        }

        return DB::transaction(function () use ($equipmentId, $payload) {
            $activeLog = $this->lockActiveLog($equipmentId);
            if ($activeLog) {
                abort(409, 'Equipment already has an active log.');
            }

            $personnelId = null;

            if (!empty($payload['is_guest'])) {
                $nameParts = preg_split('/\s+/', trim($payload['guest_name'] ?? ''), -1, PREG_SPLIT_NO_EMPTY);
                $firstName = $nameParts[0] ?? '';
                $lastName = count($nameParts) > 1 ? $nameParts[count($nameParts) - 1] : $firstName;
                $middleName = count($nameParts) > 2 ? implode(' ', array_slice($nameParts, 1, -1)) : null;

                $guestPersonnel = Personnel::create([
                    'fname' => $firstName,
                    'mname' => $middleName,
                    'lname' => $lastName,
                    'suffix' => null,
                    'position' => $payload['guest_position'],
                    'phone' => $payload['guest_phone'],
                    'address' => $payload['guest_affiliation'],
                    'email' => $payload['guest_email'],
                    'employee_id' => null,
                ]);

                $personnelId = $guestPersonnel->id;
            } else {
                $personnel = Personnel::where('employee_id', $payload['employee_id'])->first();
                if (!$personnel) {
                    abort(422, 'Personnel not found for the provided PhilRice ID.');
                }
                $personnelId = $personnel->id;
            }

            $log = new LaboratoryEquipmentLog();
            $log->equipment_id = $equipmentId;
            $log->personnel_id = $personnelId;
            $log->status = 'active';
            $log->started_at = Carbon::now();
            $log->end_use_at = Carbon::parse($payload['end_use_at']);
            $log->purpose = $payload['purpose'] ?? null;
            $log->active_lock = true;
            $log->checked_in_by = optional(auth()->user())->id;

            try {
                $log->save();
            } catch (QueryException $exception) {
                if (str_contains($exception->getMessage(), 'active_lock_key')) {
                    abort(409, 'Equipment already has an active log.');
                }
                throw $exception;
            }

            return $log->fresh(['personnel', 'equipment']);
        });
    }

    public function checkOut(string $equipmentId, array $payload): LaboratoryEquipmentLog
    {
        $equipment = $this->findEligibleEquipment($equipmentId);
        if (!$equipment) {
            abort(404, 'Equipment not found or not eligible for laboratory logs.');
        }

        return DB::transaction(function () use ($equipmentId, $payload) {
            $activeLog = $this->lockActiveLog($equipmentId);
            if (!$activeLog) {
                abort(409, 'No active log found for this equipment.');
            }

            $isAdminOverride = !empty($payload['admin_override']) && (auth()->user()?->is_admin ?? false);
            $checkedInPersonnel = $activeLog->personnel_id
                ? Personnel::find($activeLog->personnel_id)
                : null;

            if (!$checkedInPersonnel) {
                abort(409, 'No personnel record found for this log.');
            }

            if (!$checkedInPersonnel->employee_id) {
                if (!$isAdminOverride) {
                    abort(403, 'Admin override required to check out guest logs.');
                }
            } else {
                $personnel = Personnel::where('employee_id', $payload['employee_id'])->first();
                if (!$personnel) {
                    abort(422, 'Personnel not found for the provided PhilRice ID.');
                }

                if ($personnel->id !== $activeLog->personnel_id && !$isAdminOverride) {
                    abort(403, 'Only the original check-in personnel can check out this equipment.');
                }
            }

            $activeLog->status = 'completed';
            $activeLog->actual_end_at = Carbon::now();
            $activeLog->active_lock = false;
            $activeLog->checked_out_by = optional(auth()->user())->id;
            $activeLog->save();

            return $activeLog->fresh(['personnel', 'equipment']);
        });
    }

    public function markOverdue(): int
    {
        return LaboratoryEquipmentLog::where('status', 'active')
            ->where('end_use_at', '<', Carbon::now())
            ->update([
                'status' => 'overdue',
                'active_lock' => true,
            ]);
    }

    public function getDashboardMetrics(): array
    {
        $activeLogs = LaboratoryEquipmentLog::with(['equipment', 'personnel'])
            ->where('status', 'active')
            ->orderBy('started_at')
            ->get();

        $overdueLogs = LaboratoryEquipmentLog::with(['equipment', 'personnel'])
            ->where('status', 'overdue')
            ->orderBy('end_use_at')
            ->get();

        $mostUsedRows = LaboratoryEquipmentLog::query()
            ->select([
                'equipment_id',
                DB::raw('COUNT(*) as usage_count'),
                DB::raw('SUM(TIMESTAMPDIFF(SECOND, started_at, COALESCE(actual_end_at, end_use_at))) as total_duration_seconds'),
            ])
            ->whereIn('status', ['completed', 'overdue'])
            ->groupBy('equipment_id')
            ->orderByDesc('usage_count')
            ->limit(10)
            ->get();

        $equipmentMap = Item::whereIn('id', $mostUsedRows->pluck('equipment_id'))
            ->get()
            ->keyBy('id');

        $mostUsed = $mostUsedRows->map(function ($row) use ($equipmentMap) {
            $equipment = $equipmentMap->get($row->equipment_id);
            return [
                'equipment_id' => $row->equipment_id,
                'equipment_name' => $equipment?->name,
                'usage_count' => (int) $row->usage_count,
                'total_duration_seconds' => (int) $row->total_duration_seconds,
            ];
        });

        $heatmap = LaboratoryEquipmentLog::query()
            ->select([
                DB::raw('DAYOFWEEK(started_at) as day_of_week'),
                DB::raw('HOUR(started_at) as hour_of_day'),
                DB::raw('COUNT(*) as usage_count'),
            ])
            ->groupBy('day_of_week', 'hour_of_day')
            ->get();

        return [
            'active' => $activeLogs,
            'overdue' => $overdueLogs,
            'most_used' => $mostUsed,
            'heatmap' => $heatmap,
        ];
    }

    private function findEligibleEquipment(string $equipmentId): ?Item
    {
        return Item::query()
            ->with('category')
            ->where('items.id', $equipmentId)
            ->whereHas('category', function (Builder $query) {
                $query->where('categories.id', 7)
                    ->orWhere('categories.name', 'Laboratory Equipment');
            })
            ->whereHas('transactions')
            ->first();
    }

    private function getActiveLog(string $equipmentId): ?LaboratoryEquipmentLog
    {
        return LaboratoryEquipmentLog::with(['personnel'])
            ->where('equipment_id', $equipmentId)
            ->whereIn('status', ['active', 'overdue'])
            ->orderByDesc('started_at')
            ->first();
    }

    private function lockActiveLog(string $equipmentId): ?LaboratoryEquipmentLog
    {
        return LaboratoryEquipmentLog::where('equipment_id', $equipmentId)
            ->whereIn('status', ['active', 'overdue'])
            ->lockForUpdate()
            ->first();
    }
}
