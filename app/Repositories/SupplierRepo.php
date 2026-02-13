<?php

namespace App\Repositories;

use App\Models\Supplier;

class SupplierRepo extends AbstractRepoService
{
    public function __construct(Supplier $model)
    {
        parent::__construct($model);
    }

    /**
     * Get all suppliers formatted for select fields
     */
    public function getOptions()
    {
        return $this->model
            ->newQuery()
            ->select('id as name', 'name as label')
            ->get();
    }
}
