<?php
namespace App\Http\Controllers;

use App\Http\Requests\GetEventSubformRequest;
use App\Models\EventCertificateTemplate;
use App\Models\EventSubform;
use App\Models\EventSubformResponse;
use App\Models\Form;
use App\Repositories\EventSubformRepo;
use App\Repositories\FormRepo;
use Inertia\Inertia;

class EventSubformController extends BaseController
{
    public function __construct(EventSubformRepo $repository)
    {
        $this->service = $repository;
    }

    public function index(GetEventSubformRequest $request, string $event_id)
    {
        return parent::_index($request);
    }

    public function show(GetEventSubformRequest $request, string $event_id, FormRepo $formRepo)
    {
        $id   = $event_id ?: $request->validated('event_id') ?: request('event_id');
        $form = Form::where('event_id', $id)
            ->with([
                'requirements' => fn($q) => $q->with('template:id,name')
                    ->withCount('responses')
                    ->orderByRaw('step_order IS NULL, step_order ASC, created_at ASC'),
            ])
            ->firstOrFail();
        $requirements         = EventSubform::where('event_id', $id)->withCount('responses')->withTrashed()->get();

        $responsesQuery = EventSubformResponse::query()
            ->whereHas('parent', function ($query) use ($id) {
                $query->withTrashed()->where('event_id', $id);
            });

        $eventResponsesByType = (clone $responsesQuery)
            ->latest()
            ->get()
            ->groupBy('subform_type')
            ->map->values();

        $responsesByType = (clone $responsesQuery)
            ->selectRaw('subform_type, COUNT(*) as aggregate')
            ->groupBy('subform_type')
            ->pluck('aggregate', 'subform_type');

        $responsesTotal = (int) $responsesByType->sum();

        return Inertia::render('Forms/FormUpdate', [
            'data'                 => $form,
            'responsesCount'       => $formRepo->getResponsesCountByEventId($id),
            'subformRequirements'  => $requirements->map(fn($r) => ['name' => $r->id, 'label' => $r->form_type]),
            'eventStats'           => [
                'responses_total'    => $responsesTotal,
                'responses_by_type'  => $responsesByType,
                'requirements_total' => $requirements->count(),
            ],
            'eventResponsesByType' => $eventResponsesByType,
            'certificateTemplate'  => EventCertificateTemplate::where('event_id', $id)->first(),
        ]);
    }
}
