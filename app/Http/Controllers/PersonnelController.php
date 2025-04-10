<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreatePersonnelRequest;
use App\Http\Requests\DeletePersonnelRequest;
use App\Http\Requests\GetPersonnelRequest;
use App\Http\Requests\UpdatePersonnelRequest;
use App\Repositories\PersonnelRepo;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;

class PersonnelController extends BaseController
{

    public function __construct(PersonnelRepo $repository)
    {
        $this->service = $repository;
    }

    public function index(GetPersonnelRequest $request, $personnel_id = null): Collection
    {
        return parent::_index($request);
    }

    public function create(CreatePersonnelRequest $request): Model
    {
        return parent::_store($request);
    }

    public function update(UpdatePersonnelRequest $request, string $id): Model
    {
        return parent::_update($request, $id);
    }

    public function destroy(DeletePersonnelRequest $request, string $id): Model | JsonResponse
    {
        return parent::_destroy($id);
    }
}
