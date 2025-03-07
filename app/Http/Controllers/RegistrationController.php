<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateParticipantRequest;
use App\Repositories\RegistrationRepo;
use Illuminate\Http\JsonResponse;

class RegistrationController extends BaseController
{
    public function __construct(RegistrationRepo $repository)
    {
        $this->service = $repository;
    }
}
