<?php

namespace App\Repositories;

use App\Models\Item;
use App\Repositories\AbstractRepoService;
use Illuminate\Database\Eloquent\Builder;

class ItemRepo extends AbstractRepoService
{
    protected array $searchable = [
        'id',
        'name',
        'brand',
        'description',
        'category_id',
        'supplier_id',
        'image',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function __construct(Item $model)
    {
        parent::__construct($model);
        $this->appendWith = ['category', 'supplier'];
    }
}
