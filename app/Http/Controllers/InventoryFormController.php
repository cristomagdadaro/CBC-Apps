<?php

namespace App\Http\Controllers;

use App\Models\Personnel;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InventoryFormController extends BaseController
{
    public function outgoingForm() {
        return Inertia::render('Inventory/Transactions/OutgoingFormGuest', ['personnels' => Personnel::all()]);
    }
}
