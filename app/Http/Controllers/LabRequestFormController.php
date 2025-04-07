<?php

namespace App\Http\Controllers;

use App\Models\LabRequest;
use App\Models\LabRequestForm;
use App\Repositories\LabRequestRepo;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LabRequestFormController extends BaseController
{
    public function __construct(LabRequestRepo $repository)
    {
        $this->service = $repository;
    }

    public function labReqFormGuestView(Request $request, $request_id = null)
    {
        $temp = (new LabRequest())->newQuery();
        return Inertia::render('LabRequest/LabRequestFormGuest', [
            'requestForm' => $temp->where('id', $request_id)->with(['requester','request_form'])->first(),
        ]);
    }
}
