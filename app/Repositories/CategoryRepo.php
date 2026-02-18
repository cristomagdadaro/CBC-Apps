<?php

namespace App\Repositories;

use App\Models\Category;

class CategoryRepo extends AbstractRepoService
{
    public function __construct(Category $model)
    {
        parent::__construct($model);
    }
    
    /**
     * Get categories for inventory form, only including those with items and matching the specified category IDs
     * Default category IDs are 1, 2, 3, 5, and 6 (consumables, non-consumables, chemicals, PPEs, and office supplies)
    */
    public function getInventoryFormCategories($categoryIds = [1, 2, 3, 5, 6])
    {
        return $this->model
            ->newQuery()
            ->select('id as name', 'name as label')
            ->whereIn('id', (array) $categoryIds)
            ->has('items')
            ->get();
    }

    /**
     * Get all categories formatted for select fields
     */
    public function getOptions()
    {
        return $this->model
            ->newQuery()
            ->select('id as name', 'name as label')
            ->get();
    }
}
