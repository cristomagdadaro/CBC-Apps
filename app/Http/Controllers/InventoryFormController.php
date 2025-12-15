<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\Personnel;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InventoryFormController extends BaseController
{
    public function outgoingForm() {
        return Inertia::render('Inventory/Transactions/OutgoingFormGuest',
        [
            'stockLevel' => [
                [ 'name' => 'empty', 'label' => 'Empty Stock' ],
                [ 'name' => 'low', 'label' => 'Low Stock' ],
                [ 'name' => 'mid', 'label' => 'Mid Stock' ],
                [ 'name' => 'high', 'label' => 'High Stock' ],
            ],
            'categories' => Category::select('id as name', 'name as label')
                ->has('items')
                ->get(),
            'personnels' => Personnel::all()
        ]);
    }
}
