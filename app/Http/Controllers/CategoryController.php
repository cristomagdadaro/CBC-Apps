<?php

namespace App\Http\Controllers;

use App\Http\Requests\GetRequest;
use App\Http\Requests\CreateRequest;
use App\Http\Requests\DeleteRequest;
use App\Http\Requests\UpdateRequest;
use App\Repositories\CategoryRepo;

class CategoryController extends BaseController
{
    public function __construct(CategoryRepo $repository)
    {
        $this->service = $repository;
    }

    public function index(GetRequest $request)
    {
        return parent::_index($request);
    }

    public function store(CreateRequest $request)
    {
        return parent::_store($request);
    }

    public function update(UpdateRequest $request, string $id)
    {
        return parent::_update($id, $request);
    }

    public function destroy(DeleteRequest $request, string $id)
    {
        return parent::_destroy($$id);
    }
}
