<?php

namespace App\Http\Controllers;

use App\Repositories\RequestFormPivotRepo;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LabRequestFormController extends BaseController
{
    public function __construct(RequestFormPivotRepo $repository)
    {
        $this->service = $repository;
    }

    protected function repo(): RequestFormPivotRepo
    {
        return $this->service;
    }

    public function labReqFormGuestView(Request $request, $request_id = null)
    {
        return Inertia::render('LabRequest/UseRequestFormGuest', [
            'requestForm' => $this->repo()->getGuestFormById($request_id),
            'requestTypeOptions' => collect(config('system.request_types'))->keys()->map(fn ($key) => strtoupper($key))->values(),
        ]);
    }
}
