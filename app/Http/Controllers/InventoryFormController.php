<?php

namespace App\Http\Controllers;

use App\Repositories\OptionRepo;
use App\Repositories\CategoryRepo;
use App\Repositories\PersonnelRepo;
use App\Repositories\TransactionRepo;
use Inertia\Inertia;

class InventoryFormController extends BaseController
{
    protected CategoryRepo $categoryRepo;
    protected PersonnelRepo $personnelRepo;
    protected OptionRepo $optionRepo;

    public function __construct(CategoryRepo $categoryRepo, PersonnelRepo $personnelRepo, OptionRepo $optionRepo, TransactionRepo $transactionRepo)
    {
        $this->service = $transactionRepo;
        $this->categoryRepo = $categoryRepo;
        $this->personnelRepo = $personnelRepo;
        $this->optionRepo = $optionRepo;
    }

    public function outgoingForm() {
        return Inertia::render('Inventory/Transactions/OutgoingFormGuest',
        [
            'stockLevel' => $this->optionRepo->getStockLevels(),
            'categories' => $this->categoryRepo->getInventoryFormCategories([1,2,3,5,6,11,12]),
            'personnels' => $this->personnelRepo->getAllForInventoryForm(),
            'projectCodes' => $this->transactionRepo()->getAvailableProjectCodes(),
            'storage_locations' => $this->optionRepo->getStorageLocations(),
        ]);
    }

    protected function transactionRepo(): TransactionRepo
    {
        return $this->requireService();
    }
}
