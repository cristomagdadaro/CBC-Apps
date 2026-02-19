<?php

namespace App\Repositories;

use App\Models\Personnel;

class PersonnelRepo extends AbstractRepoService
{
    public function __construct(Personnel $model)
    {
        parent::__construct($model);
    }

    /**
     * Get all personnel formatted for select fields
     */
    public function getOptions()
    {
        return $this->model
            ->newQuery()
            ->selectRaw('id as name, CONCAT(fname, " ", COALESCE(mname, ""), " ", lname, " ", COALESCE(suffix, "")) as label')
            ->get();
    }

    /**
     * Get all personnel formatted for inventory form, only including those with transactions and matching the specified category IDs
     * Default category IDs are 1, 2, 3, 5, and 6 (consumables, non-consumables, chemicals, PPEs, and office supplies
    */
    public function getAllForInventoryForm($categoryIds = [1, 2, 3, 5, 6])
    {
        return $this->model->newQuery()->get();
    }
}
