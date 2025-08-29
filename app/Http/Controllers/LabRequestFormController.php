<?php

namespace App\Http\Controllers;

use App\Models\RequestFormPivot;
use App\Models\UseRequestForm;
use App\Repositories\RequestFormPivotRepo;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LabRequestFormController extends BaseController
{
    public function __construct(RequestFormPivotRepo $repository)
    {
        $this->service = $repository;
    }

    public function labReqFormGuestView(Request $request, $request_id = null)
    {
        $temp = (new RequestFormPivot())->newQuery();
        return Inertia::render('LabRequest/UseRequestFormGuest', [
            'requestForm' => $temp->where('id', $request_id)->with(['requester','request_form'])->first(),
        ]);
    }
}
