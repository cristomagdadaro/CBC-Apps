<?php

namespace App\Http\Controllers;

use App\Http\Requests\GoogleCalendarBatchSyncRequest;
use App\Http\Requests\GoogleCalendarIndexRequest;
use App\Http\Requests\GoogleCalendarSyncRequest;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use RuntimeException;

class GoogleCalendarController extends BaseController
{
    private $calendarService;

    public function __construct()
    {
        $this->calendarService = app('App\\Services\\GoogleCalendar\\GoogleCalendarSyncService');
    }

    public function index(GoogleCalendarIndexRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $status = $this->calendarService->status();

        if (!$status['configured']) {
            return response()->json([
                'data' => [],
                'meta' => $status,
            ]);
        }

        return response()->json([
            'data' => $this->calendarService->listEvents(
                isset($validated['start']) ? Carbon::parse($validated['start']) : null,
                isset($validated['end']) ? Carbon::parse($validated['end']) : null,
            ),
            'meta' => $status,
        ]);
    }

    public function sync(GoogleCalendarSyncRequest $request): JsonResponse
    {
        try {
            $event = $this->calendarService->syncPortalEvent($request->validated('event'));

            return response()->json([
                'data' => $event,
                'message' => 'Event synced to Google Calendar.',
            ]);
        } catch (RuntimeException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 503);
        }
    }

    public function syncBatch(GoogleCalendarBatchSyncRequest $request): JsonResponse
    {
        try {
            $events = $this->calendarService->syncPortalEvents($request->validated('events'));

            return response()->json([
                'data' => $events,
                'message' => 'Loaded events synced to Google Calendar.',
            ]);
        } catch (RuntimeException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 503);
        }
    }

    public function redirectToOauth(Request $request): RedirectResponse
    {
        try {
            return redirect()->away($this->calendarService->getOauthAuthorizationUrl($request->user()?->getAuthIdentifier()));
        } catch (RuntimeException $exception) {
            return redirect()->route('rentals.calendar.index', [
                'google_calendar_notice' => 'oauth_failed',
                'google_calendar_message' => $exception->getMessage(),
            ]);
        }
    }

    public function handleOauthCallback(Request $request): RedirectResponse
    {
        try {
            if ($request->filled('error')) {
                $errorMessage = $request->string('error_description')->toString() ?: $request->string('error')->toString();

                throw new RuntimeException($errorMessage ?: 'Google Calendar OAuth authorization was denied.');
            }

            $this->calendarService->handleOauthCallback(
                $request->string('state')->toString(),
                $request->string('code')->toString(),
                $request->user()?->getAuthIdentifier(),
            );

            return redirect()->route('rentals.calendar.index', [
                'google_calendar_notice' => 'connected',
            ]);
        } catch (RuntimeException $exception) {
            return redirect()->route('rentals.calendar.index', [
                'google_calendar_notice' => 'oauth_failed',
                'google_calendar_message' => $exception->getMessage(),
            ]);
        }
    }

    public function disconnect(): JsonResponse
    {
        $this->calendarService->disconnectOauth();

        return response()->json([
            'message' => 'Google Calendar OAuth token removed.',
        ]);
    }
}