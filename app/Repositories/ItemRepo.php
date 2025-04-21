<?php

namespace App\Repositories;

use App\Models\Item;
use App\Repositories\AbstractRepoService;
use Illuminate\Database\Eloquent\Builder;

class ItemRepo extends AbstractRepoService
{
    public function __construct(Item $model)
    {
        parent::__construct($model);
    }
}
