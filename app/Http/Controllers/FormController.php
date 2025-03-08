<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateFormRequest;
use App\Http\Requests\GetFormsRequest;
use App\Models\Form;
use App\Repositories\FormRepo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Inertia\Inertia;

class FormController extends BaseController
{

    public function __construct(FormRepo $repository)
    {
        $this->service = $repository;
    }

    public function formGuestView(GetFormsRequest $request, $event_id = null)
    {
        $temp = (new Form)->newQuery();
        return Inertia::render('Forms/FormGuest', [
            'eventForm' => $temp->where('event_id', $event_id)->withCount('participants')->first(),
        ]);
    }

    public function index(GetFormsRequest $request, $event_id = null): Collection
    {
        return parent::_index($request);
    }

    public function post(CreateFormRequest $request, $event_id = null): Model
    {
        return parent::_store($request);
    }
}
