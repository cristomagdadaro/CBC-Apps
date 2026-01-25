<?php

namespace App\Http\Controllers;
use App\Repositories\EventRequirementRepo;

class EventRequirementController extends BaseController
{
    public function __construct(EventRequirementRepo $repository)
    {
        $this->service = $repository;
    }
    
}
