<?php

namespace App\Http\Controllers;

use App\Http\Requests\GetEventRequirementRequest;
use App\Repositories\EventRequirementRepo;

class EventRequirementController extends BaseController
{
    public function __construct(EventRequirementRepo $repository)
    {
        $this->service = $repository;
    }
    
    public function index(GetEventRequirementRequest $request, string $event_id)
    {
        return parent::_index($request);
    }
}
