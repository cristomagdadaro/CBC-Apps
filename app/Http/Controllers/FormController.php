<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateFormRequest;
use App\Http\Requests\DeleteFormRequest;
use App\Http\Requests\GetFormsRequest;
use App\Http\Requests\UpdateFormRequest;
use App\Models\Form;
use App\Repositories\FormRepo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Collection;
use Inertia\Inertia;

class FormController extends BaseController
{

    public function __construct(FormRepo $repository)
    {
        $this->service = $repository;
        $this->service->appendCount = ['participants'];
    }

    public function formGuestView(GetFormsRequest $request, $event_id = null)
    {
        $temp = (new Form)->newQuery();
        return Inertia::render('Forms/FormGuest', [
            'eventForm' => $temp->where('event_id', $event_id)->withCount('participants')->withCount('participants')->first(),
            'quote' => Inspiring::quote(),
        ]);
    }

    public function index(GetFormsRequest $request, $event_id = null): Collection
    {
        return parent::_index($request);
    }

    public function create(CreateFormRequest $request, $event_id = null): Model
    {
        return parent::_store($request);
    }

    public function update(UpdateFormRequest $request, $event_id = null): Model
    {
        $model = $this->service->model->where('event_id', $event_id)->first();
        $model->fill($request->validated());
        $model->save();

        return $model;
    }

    public function delete(DeleteFormRequest $request, $event_id = null): Model
    {
        $model = $this->service->model->where('event_id', $request->validated('event_id'))->first();
        $model->delete();
        return $model;
    }
}
