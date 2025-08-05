<?php

namespace App\Repositories;

use App\Models\Transaction;

class TransactionRepo extends AbstractRepoService
{
    public function __construct(Transaction $model)
    {
        parent::__construct($model);
        $this->appendWith = ['item', 'user','personnel'];
    }
}
