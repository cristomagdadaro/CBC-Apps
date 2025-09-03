<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateSupplierRequest;
use App\Http\Requests\DeleteSupplierRequest;
use App\Http\Requests\GetSupplierRequest;
use App\Http\Requests\UpdateSupplierRequest;
use App\Repositories\SupplierRepo;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;

class SupplierController extends BaseController
{

    public function __construct(SupplierRepo $repository)
    {
        $this->service = $repository;
    }

    public function index(GetSupplierRequest $request, $supplier_id = null): Collection
    {
        return parent::_index($request);
    }

    public function create(CreateSupplierRequest $request): Model
    {
        return parent::_store($request);
    }

    public function update(UpdateSupplierRequest $request, string $id): Model
    {
        return parent::_update($id, $request);
    }

    public function destroy(DeleteSupplierRequest $request, string $id = null): Model
    {
        return parent::_destroy($id);
    }
}
