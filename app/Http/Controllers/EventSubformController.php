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
                'requirements' => fn($q) => $q->withCount('responses')
                    ->orderByRaw('step_order IS NULL, step_order ASC, created_at ASC'),
            ])
            ->firstOrFail();
        $requirements         = EventSubform::where('event_id', $id)->withCount('responses')->get();
        $eventResponsesByType = EventSubformResponse::whereRelation('parent', 'event_id', $id)
            ->latest()
            ->get()
            ->groupBy('subform_type')
            ->map->values();
        return Inertia::render('Forms/FormUpdate', [
            'data'                 => $form,
            'responsesCount'       => $formRepo->getResponsesCountByEventId($id),
            'subformRequirements'  => $requirements->map(fn($r) => ['name' => $r->id, 'label' => $r->form_type]),
            'eventStats'           => [
                'responses_total'    => $requirements->sum('responses_count'),
                'responses_by_type'  => $requirements->pluck('responses_count', 'form_type'),
                'requirements_total' => $requirements->count(),
            ],
            'eventResponsesByType' => $eventResponsesByType,
            'certificateTemplate'  => EventCertificateTemplate::where('event_id', $id)->first(),
        ]);
    }
}
