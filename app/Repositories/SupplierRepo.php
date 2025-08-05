<?php

namespace App\Repositories;

use App\Models\Supplier;

class SupplierRepo extends AbstractRepoService
{
    public function __construct(Supplier $model)
    {
        parent::__construct($model);
    }
}
