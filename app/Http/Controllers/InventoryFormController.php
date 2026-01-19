<?php

namespace App\Http\Controllers;

use App\Repositories\CategoryRepo;
use App\Repositories\PersonnelRepo;
use Inertia\Inertia;

class InventoryFormController extends BaseController
{
    protected CategoryRepo $categoryRepo;
    protected PersonnelRepo $personnelRepo;

    public function __construct(CategoryRepo $categoryRepo, PersonnelRepo $personnelRepo)
    {
        $this->categoryRepo = $categoryRepo;
        $this->personnelRepo = $personnelRepo;
    }

    public function outgoingForm() {
        return Inertia::render('Inventory/Transactions/OutgoingFormGuest',
        [
            'stockLevel' => config('system.stock_levels'),
            'categories' => $this->categoryRepo->getInventoryFormCategories(),
            'personnels' => $this->personnelRepo->getAllForInventoryForm()
        ]);
    }
}
