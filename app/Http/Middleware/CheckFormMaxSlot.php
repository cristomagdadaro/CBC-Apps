<?php

namespace App\Http\Middleware;

use App\Models\Form;
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
        if (auth()->check()) {
            return $next($request);
        }

        $formId = $request->input('event_id') ?? $request->route('event_id');

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

        // ✅ Corrected: Skip validation if `max_slots` is not set (null)
        if (is_null($form->max_slots) || $form->max_slots <= 0) {
            return $next($request);
        }

        if ($form->isFull()) {
            return response()->json(['errors' => [
                'full' => ['This form exceeds the maximum number of slots.']
            ]], 403);
        }

        return $next($request);
    }

}
