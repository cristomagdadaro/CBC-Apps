<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreatePersonnelRequest;
use App\Http\Requests\GetPersonnelRequest;
use App\Http\Requests\UpdatePersonnelRequest;
use App\Repository\PersonnelRepo;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;

class PersonnelController extends BaseController
{

    public function __construct(PersonnelRepo $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     * @param GetPersonnelRequest $request
     * @throws Exception
     */
    public function index(GetPersonnelRequest $request)
    {
        return $this->repository->search(new Collection($request->validated()));
    }

    /**
     * Store a newly created resource in storage.
     * @param CreatePersonnelRequest $request
     * @return Model | JsonResponse
     * @throws Exception
     */
    public function store(CreatePersonnelRequest $request): Model | JsonResponse
    {
        return $this->repository->create($request->validated());
    }

    /**
     * Update the specified resource in storage.
     * @param UpdatePersonnelRequest $request
     * @param string $id
     * @return JsonResponse
     * @throws Exception
     */
    public function update(UpdatePersonnelRequest $request, string $id): JsonResponse
    {
        return $this->repository->update($id, $request->validated());
    }

    /**
     * Remove the specified resource from storage.
     * @param string $id
     * @return Model | JsonResponse
     * @throws Exception
     */
    public function destroy(string $id): Model | JsonResponse
    {
        return $this->repository->delete($id);
    }
}
