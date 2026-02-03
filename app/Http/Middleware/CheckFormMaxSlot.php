<?php

namespace App\Http\Middleware;

use App\Models\EventSubform;
use App\Models\EventSubformResponse;
use App\Models\Form;
use App\Models\Registration;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckFormMaxSlot
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip validation only for admin users (not all authenticated users)
        if (auth()->check() && auth()->user()?->is_admin && env('APP_ENV') !== 'testing') {
            return $next($request);
        }

        $eventId = $request->input('event_id') ?? $request->route('event_id');
        $formParentId = $request->input('form_parent_id');

        $formId = $eventId;
        $isSubformResponse = false;
        $requirement = null;

        if ($formParentId && !$eventId) {
            $requirement = EventSubform::find($formParentId);
            $formId = $requirement?->event_id;
            $isSubformResponse = true;
        }

        if (!$formId) {
            return response()->json(['errors' => [
                'suspended' => ['Form ID is required.']
            ]], 400);
        }

        $form = Form::where('event_id', $formId)->first();

        if (!$form) {
            return response()->json(['errors' => [
                'suspended' => ['Form not found.']
            ]], 404);
        }

        if (!$isSubformResponse && !$requirement) {
            $requirement = EventSubform::where('event_id', $formId)
                ->where('form_type', 'registration')
                ->first();
        }

        $maxSlots = $requirement?->max_slots ?? $form->max_slots;

        if (is_null($maxSlots) || $maxSlots <= 0) {
            return $next($request);
        }

        if ($isSubformResponse) {
            $currentCount = EventSubformResponse::where('form_parent_id', $formParentId)->count();
            if ($currentCount >= $maxSlots) {
                return response()->json(['errors' => [
                    'full' => ['This form has reached the maximum number of participants.']
                ]], 403);
            }
        } else {
            $currentCount = Registration::where('event_subform_id', $formId)->count();
            if ($currentCount >= $maxSlots) {
                return response()->json(['errors' => [
                    'full' => ['This form has reached the maximum number of participants.']
                ]], 403);
            }
        }

        return $next($request);
    }

}
