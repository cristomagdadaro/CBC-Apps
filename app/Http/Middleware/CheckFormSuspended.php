<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Form;

class CheckFormSuspended
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check()) {
            return $next($request);
        }

        $eventId = $request->input('event_id'); // Assuming the form ID is in the request body
        if (!$eventId) {
            $eventId = $request->route('event_id'); // Or in the route
        }

        $formParentId = $request->input('form_parent_id');
        $formId = $eventId;

        // If form_parent_id is provided, resolve it to the event_id
        if ($formParentId && !$eventId) {
            $requirement = \App\Models\EventSubform::find($formParentId);
            $formId = $requirement?->event_id;
        }

        if (!$formId) {
            return response()->json(['errors' => [
                'suspended' => ['Form ID is required.']
            ]], 400);
        }

        $form = Form::where('event_id', $formId)->first();

        if (!$form) {
            return response()->json(['errors' =>  [
                'suspended' => ['Form not found.']
            ]], 404);
        }

        if ($form->isSuspended()) {
            return response()->json(['errors' =>  [
                'suspended' => ['This form is currently suspended.']
            ]], 403);
        }

        return $next($request);
    }
}
