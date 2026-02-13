<?php

namespace App\Http\Controllers;

use App\Repositories\OptionRepo;
use App\Repositories\CategoryRepo;
use App\Repositories\PersonnelRepo;
use Inertia\Inertia;

class InventoryFormController extends BaseController
{
    protected CategoryRepo $categoryRepo;
    protected PersonnelRepo $personnelRepo;
    protected OptionRepo $optionRepo;

    public function __construct(CategoryRepo $categoryRepo, PersonnelRepo $personnelRepo, OptionRepo $optionRepo)
    {
        $this->categoryRepo = $categoryRepo;
        $this->personnelRepo = $personnelRepo;
        $this->optionRepo = $optionRepo;
    }

    public function outgoingForm() {
        return Inertia::render('Inventory/Transactions/OutgoingFormGuest',
        [
            'stockLevel' => $this->optionRepo->getStockLevels(),
            'categories' => $this->categoryRepo->getInventoryFormCategories(),
            'personnels' => $this->personnelRepo->getAllForInventoryForm()
        ]);
    }
}
