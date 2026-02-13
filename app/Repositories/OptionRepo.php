<?php

namespace App\Repositories;

use App\Models\Option;

class OptionRepo extends AbstractRepoService
{
    public function __construct(Option $model)
    {
        parent::__construct($model);
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
        $locations =  $this->getByGroup('storage_locations');
        $normalized = collect($locations)
            ->map(function ($label, $jsonKey) {
                $decoded = json_decode($jsonKey, true);

                return [
                    'label' => $label,
                    'name' => $decoded ?? null,
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
     */
    public function getStockLevels()
    {
        return json_decode($this->getByKey('stock_levels'), true) ?? [];
    }

    /**
     * Get all options keyed by event_halls
     */
    public function getEventHalls()
    {
        return json_decode($this->getByKey('event_halls'), true) ?? [];
    }

    /**
     * Get all options keyed by laboratories
     */
    public function getLaboratories()
    {
        return json_decode($this->getByKey('laboratories'), true) ?? [];
    }

    /**
     * Get all vehicles from the transactions table join with items table and category_id of 8 for vehicles
     */
    public function getVehicles()
    {
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
