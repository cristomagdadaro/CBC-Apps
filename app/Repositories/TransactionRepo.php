<?php

namespace App\Repositories;

use App\Models\Transaction;

class TransactionRepo extends AbstractRepoService
{
    protected array $searchable = [
        'id',
        'item_id',
        'barcode',
        'transac_type',
        'quantity',
        'unit_price',
        'unit',
        'total_cost',
        'project_code',
        'personnel_id',
        'user_id',
        'expiration',
        'remarks',
        'created_at',
    ];

    public function __construct(Transaction $model)
    {
        parent::__construct($model);
        $this->appendWith = ['item', 'user','personnel'];
    }
}
