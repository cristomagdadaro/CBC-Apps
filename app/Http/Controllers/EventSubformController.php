<?php

namespace App\Http\Controllers;

use App\Http\Requests\GetEventSubformRequest;
use App\Repositories\EventSubformRepo;
use App\Models\EventSubform;
use App\Models\Form;
use App\Models\EventCertificateTemplate;
use App\Models\Registration;
use App\Models\EventSubformResponse;
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

    public function show(GetEventSubformRequest $request, string $event_id)
    {
        if (!$event_id) {
            $event_id = request()->input('event_id'); // Fallback to query parameter
        }

        if (!$event_id) {
            $event_id = request()->query('event_id');
        }

        $formRepo = new \App\Repositories\FormRepo(new Form());

        $requirements = EventSubform::where('event_id', $event_id)
            ->withCount('responses')
            ->get();

        $responsesByType = $requirements
            ->mapWithKeys(fn ($requirement) => [$requirement->form_type => $requirement->responses_count]);

        $eventStats = [
            'responses_total' => $requirements->sum('responses_count'),
            'responses_by_type' => $responsesByType,
            'requirements_total' => $requirements->count(),
        ];

        $eventResponsesByType = EventSubformResponse::query()
            ->join('event_subforms', 'event_subform_responses.form_parent_id', '=', 'event_subforms.id')
            ->where('event_subforms.event_id', $event_id)
            ->select([
                'event_subform_responses.id',
                'event_subform_responses.subform_type',
                'event_subform_responses.response_data',
                'event_subform_responses.created_at',
            ])
            ->orderByDesc('event_subform_responses.created_at')
            ->get()
            ->groupBy('subform_type')
            ->map(fn ($items) => $items->values());

        return Inertia::render('Forms/FormUpdate', [
            'data' => Form::where('event_id', $event_id)
                ->with(['requirements' => function ($query) {
                    $query->withCount('responses');
                }])
                ->first(),
            'responsesCount' => $formRepo->getResponsesCountByEventId($event_id),
            'subformRequirements' => EventSubform::select(['id as name', 'form_type as label'])->where('event_id', $event_id)->get(),
            'eventStats' => $eventStats,
            'eventResponsesByType' => $eventResponsesByType,
            'certificateTemplate' => EventCertificateTemplate::where('event_id', $event_id)->first(),
        ]);
    }
}
