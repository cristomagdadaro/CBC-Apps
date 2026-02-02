<?php

namespace App\Http\Controllers;

use App\Http\Requests\GetEventSubformRequest;
use App\Repositories\EventSubformRepo;

class EventSubformController extends BaseController
{
    public function __construct(EventSubformRepo $repository)
    {
        $this->service = $repository;
    }
    
    public function index(GetEventSubformRequest $request, string $event_id)
    {
        return parent::_index($request);
    }
}
