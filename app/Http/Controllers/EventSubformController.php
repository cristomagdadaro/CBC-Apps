<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateEventSubformRequest;
use App\Http\Requests\GetEventSubformRequest;
use App\Repositories\EventSubformRepo;

class EventSubformController extends BaseController
{
    public function __construct(EventSubformRepo $repository)
    {
        $this->service = $repository;
    }

    public function index(GetEventSubformRequest $request)
    {
        return parent::_index($request);
    }

    public function create(CreateEventSubformRequest $request)
    {
        return parent::_store($request);
    }


}
