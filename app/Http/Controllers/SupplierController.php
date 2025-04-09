<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateSupplierRequest;
use App\Http\Requests\GetSupplierRequest;
use App\Http\Requests\UpdateSupplierRequest;
use App\Repository\SupplierRepo;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;

class SupplierController extends BaseController
{

    public function __construct(SupplierRepo $repository)
    {
        $this->repository = $repository;
    }

    public function all()
    {
        return new Collection($this->repository->all());
    }

    /**
     * Display a listing of the resource.
     * @param GetSupplierRequest $request
     * @return Collection
     * @throws Exception
     */
    public function index(GetSupplierRequest $request): Collection
    {
        return $this->repository->search(new Collection($request->validated()));
    }

    /**
     * Store a newly created resource in storage.
     * @param CreateSupplierRequest $request
     * @return Model | JsonResponse
     * @throws Exception
     */
    public function store(CreateSupplierRequest $request): Model | JsonResponse
    {
        return $this->repository->create($request->validated());
    }

    /**
     * Update the specified resource in storage.
     * @param UpdateSupplierRequest $request
     * @param string $id
     * @return JsonResponse
     * @throws Exception
     */
    public function update(UpdateSupplierRequest $request, string $id): JsonResponse
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
