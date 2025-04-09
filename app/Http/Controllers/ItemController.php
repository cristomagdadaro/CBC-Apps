<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateItemRequest;
use App\Http\Requests\GetItemRequest;
use App\Http\Requests\UpdateItemRequest;
use App\Repository\ItemRepo;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ItemController extends BaseController
{
    public function __construct(ItemRepo $repository)
    {
        $this->repository = $repository;
    }

    public function all()
    {
        return new Collection($this->repository->all());
    }

    /**
     * Display a listing of the resource.
     * @param GetItemRequest $request
     * @throws Exception
     */
    public function index(GetItemRequest $request)
    {
        return $this->repository->search(new Collection($request->validated()));
    }

    /**
     * Store a newly created resource in storage.
     * @param CreateItemRequest $request
     * @return Model | JsonResponse
     * @throws Exception
     */
    public function store(CreateItemRequest $request) : Model | JsonResponse
    {
        $validated = $request->validated();
        $validated['id'] = (string) Str::uuid();
        return $this->repository->create($validated);
    }

    /**
     * Update the specified resource in storage.
     * @param UpdateItemRequest $request
     * @param string $id
     * @return JsonResponse
     * @throws Exception
     */
    public function update(UpdateItemRequest $request, string $id): JsonResponse
    {
        return $this->repository->update($id, $request->validated());
    }

    /**
     * Remove the specified resource from storage.
     * @param string $id
     * @return Model|JsonResponse
     * @throws Exception
     */
    public function destroy(string $id): Model | JsonResponse
    {
        return $this->repository->delete($id);
    }
}
