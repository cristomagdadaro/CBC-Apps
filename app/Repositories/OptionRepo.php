<?php

namespace App\Repositories;

use App\Models\Option;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class OptionRepo extends AbstractRepoService
{
    public function __construct(Option $model)
    {
        parent::__construct($model);
    }

    public function create(array $data)
    {
        return parent::create($this->normalizeOptionPayload($data));
    }

    public function update(int|string $id, array $data): Model
    {
        return parent::update($id, $this->normalizeOptionPayload($data));
    }

    protected function normalizeOptionPayload(array $data): array
    {
        if (array_key_exists('key', $data) && is_string($data['key'])) {
            $data['key'] = strtolower(str_replace(' ', '_', trim($data['key'])));
        }

        if (($data['type'] ?? null) !== 'select') {
            $data['options'] = null;
        } elseif (array_key_exists('options', $data) && is_string($data['options'])) {
            $decoded = json_decode($data['options'], true);
            $data['options'] = json_last_error() === JSON_ERROR_NONE ? $decoded : null;
        }

        if (array_key_exists('value', $data)) {
            $value = $data['value'];

            if (is_bool($value)) {
                $data['value'] = $value ? 'true' : 'false';
            } elseif (is_array($value) || is_object($value)) {
                $data['value'] = json_encode($value);
            } elseif ($value !== null) {
                $data['value'] = (string) $value;
            }
        }

        return $data;
    }

    /**
     * Get all options by group
     */
    public function getByGroup($group)
    {
        return $this->model
            ->newQuery()
            ->where('group', $group)
            ->pluck(column: 'label', key: 'value')
            ->toArray();
    }

    /**
     * Get option value by key
     */
    public function getByKey($key)
    {
        return $this->model
            ->newQuery()
            ->where('key', $key)
            ->first()?->value;
    }

    public function getBooleanByKey(string $key, bool $default = false): bool
    {
        $value = $this->getByKey($key);

        if ($value === null) {
            return $default;
        }

        if (is_bool($value)) {
            return $value;
        }

        if (is_int($value)) {
            return $value === 1;
        }

        if (is_string($value)) {
            $normalized = strtolower(trim($value));

            if (in_array($normalized, ['1', 'true', 'yes', 'on'], true)) {
                return true;
            }

            if (in_array($normalized, ['0', 'false', 'no', 'off'], true)) {
                return false;
            }
        }

        return $default;
    }

    public function upsertBooleanOption(string $key, bool $value, array $meta = []): Model
    {
        return $this->model
            ->newQuery()
            ->updateOrCreate(
                ['key' => $key],
                [
                    'value' => $value ? 'true' : 'false',
                    'label' => Arr::get($meta, 'label', $key),
                    'description' => Arr::get($meta, 'description'),
                    'type' => 'boolean',
                    'group' => Arr::get($meta, 'group', 'forms'),
                    'options' => Arr::get($meta, 'options'),
                ]
            );
    }

    /**
     * Get all options grouped by group
     */
    public function getAllGrouped()
    {
        return $this->model
            ->newQuery()
            ->get()
            ->groupBy('group')
            ->map(function ($group) {
                return $group->pluck('value', 'key')->toArray();
            })
            ->toArray();
    }

    /**
     * Get options formatted for dropdowns
     */
    public function getForDropdown($group = null)
    {
        $query = $this->model->newQuery();

        if ($group) {
            $query->where('group', $group);
        }

        return $query
            ->select('id', 'key as value', 'label')
            ->get();
    }

    /**
     * Get options with their metadata
     */
    public function getWithMetadata($group = null)
    {
        $query = $this->model->newQuery();

        if ($group) {
            $query->where('group', $group);
        }

        return $query
            ->select('id', 'key', 'value', 'label', 'description', 'type', 'group', 'options')
            ->get();
    }

    /**
     * Get all options keyed by storage_locations
     */
    public function getStorageLocations()
    {
        $groups = ['storage_locations', 'event_halls', 'laboratories', 'offices', 'screenhouses'];

        $normalized = $this->model
            ->newQuery()
            ->whereIn('group', $groups)
            ->whereNotNull('value')
            ->select('value', 'label')
            ->orderByRaw('CAST(value AS UNSIGNED) asc')
            ->get()
            ->map(function ($record) {
                return [
                    'label' => $record->label,
                    'name' => (string) $record->value,
                ];
            })
            ->values()
            ->toArray();

        return $normalized;
    }

    /**
     * Get all options grouped by fes
     */
    public function getRequestTypes()
    {
        $records = $this->model
            ->newQuery()
            ->where('group', 'fes')
            ->get();

        $result = [];

        foreach ($records as $record) {
            $decoded = json_decode($record->value, true);

            if (is_array($decoded)) {
                $result = array_merge($result, $decoded);
            }
        }

        return collect($result)->map(function ($type) {
            return [
                'name' => $type,
                'label' => $type,
            ];
        })->sortBy('label')->values();
    }

    /**
     * Get all options keyed by stock_levels
     * Only return stock levels for categories 1, 2, 3, 5, and 6 (consumables, non-consumables, chemicals, PPEs, and office supplies)
     */
    public function getStockLevels($categoryIds = [1, 2, 3, 5, 6])
    {
        return json_decode($this->getByKey('stock_levels'), true) ?? [];
    }

    /**
     * Get all options keyed by event_halls
     */
    public function getEventHalls()
    {
        $raw = $this->getByKey('event_halls') ?? $this->getByKey('event_halls');
        $decoded = is_string($raw) ? json_decode($raw, true) : $raw;
        if (is_array($decoded) && !empty($decoded)) {
            return collect($decoded)
                ->map(function ($hall) {
                    $name = $hall['name'] ?? null;
                    $label = $hall['label'] ?? $name;

                    if (!$name) {
                        return null;
                    }

                    return [
                        'name' => (string) $name,
                        'label' => (string) $label,
                    ];
                })
                ->filter()
                ->values();
        }

        return $this->model
            ->newQuery()
            ->where('group', 'event_halls')
            ->whereNotNull('value')
            ->select('value', 'label')
            ->orderByRaw('CAST(value AS UNSIGNED) asc')
            ->get()
            ->map(function ($record) {
                return [
                    'label' => $record->label,
                    'name' => (string) $record->value,
                ];
            })
            ->values()
            ->toArray();
    }

    /**
     * Get all options keyed by laboratories
     */
    public function getLaboratories()
    {
        return collect($this->getByGroup('laboratories'))->map(function ($label, $name) {
            return [
                'value' => (int) $name,
                'label' => $label,
            ];
        })->sortBy('label')->values();
    }

    /**
     * Get available vehicles from options key `vehicles` (or `vehicle`),
     * with a fallback to inventory transactions for backward compatibility.
     */
    public function getVehicles()
    {
        $raw = $this->getByKey('vehicles') ?? $this->getByKey('vehicle');
        $decoded = is_string($raw) ? json_decode($raw, true) : $raw;

        if (is_array($decoded) && !empty($decoded)) {
            return collect($decoded)
                ->map(function ($vehicle) {
                    $name = $vehicle['name'] ?? null;
                    $label = $vehicle['label'] ?? $name;

                    if (!$name) {
                        return null;
                    }

                    return [
                        'name' => (string) $name,
                        'label' => (string) $label,
                    ];
                })
                ->filter()
                ->values();
        }

        return \App\Models\Transaction::join('items', 'transactions.item_id', '=', 'items.id')
            ->where('items.category_id', 8)
            ->selectRaw('items.description as name, concat(items.brand, " (", items.description, ")") as label')
            ->get();
    }

    /**
     * Get approving officers
     */
    public function getApprovingOfficers()
    {
        return json_decode($this->getByKey('approving_officers'), true) ?? null;
    }

    /**
     * Get event response notification email
     */
    public function getEventResponseNotificationEmail()
    {
        return json_decode($this->getByKey('event_response_notification_email'), true) ?? [];
    }

    /**
     * Get center chief
     */
    public function getCenterChief()
    {
        return $this->getByKey('center_chief') ?? 'Default Center Chief';
    }

    /**
     * Get sex options
     */
    public function getSexOptions()
    {
        $value = $this->getByKey('sex');

        if (!$value) {
            return [];
        }

        $decoded = json_decode($value, true);

        return is_array($decoded) ? $decoded : [];
    }
}
