<?php

namespace App\Repositories;

use App\Models\Supplier;

class SupplierRepo extends AbstractRepoService
{
    protected array $searchable = [
        'id',
        'name',
        'email',
        'address',
        'phone',
        'description'
    ];

    public function __construct(Supplier $model)
    {
        parent::__construct($model);
    }
}
