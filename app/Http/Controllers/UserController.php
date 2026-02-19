<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\DeleteUserRequest;
use App\Http\Requests\GetUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Repositories\UserRepo;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;

class UserController extends BaseController
{
    public function __construct(UserRepo $repository)
    {
        $this->service = $repository;
    }

    protected function repo(): UserRepo
    {
        return $this->service;
    }

    public function index(GetUserRequest $request): Collection
    {
        return parent::_index($request);
    }

    public function store(CreateUserRequest $request)
    {
        return $this->repo()->createWithRoles($request->validated());
    }

    public function show(string $id): JsonResponse
    {
        $user = $this->repo()->model
            ->newQuery()
            ->with('roles:id,name,label')
            ->findOrFail($id);

        return response()->json(['data' => $user]);
    }

    public function update(UpdateUserRequest $request, string $id)
    {
        return $this->repo()->updateWithRoles($id, $request->validated());
    }

    public function destroy(DeleteUserRequest $request, string $id)
    {
        if ((string) $request->user()?->id === (string) $id) {
            abort(422, 'The active user cannot delete own account in users management.');
        }

        return parent::_destroy($id);
    }
}
