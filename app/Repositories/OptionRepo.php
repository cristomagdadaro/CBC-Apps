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
            ->pluck('value', 'key')
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
}
