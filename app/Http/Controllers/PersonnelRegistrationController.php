<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreatePersonnelRegistrationRequest;
use App\Http\Requests\GetPersonnelRequest;
use App\Http\Requests\UpdatePersonnelRegistrationStatusRequest;
use App\Models\PersonnelRegistration;
use App\Repositories\PersonnelRegistrationRepo;
use App\Services\Personnel\PersonnelIdCardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;

class PersonnelRegistrationController extends BaseController
{
    public function __construct(PersonnelRegistrationRepo $repository)
    {
        $this->service = $repository;
    }

    protected function repo(): PersonnelRegistrationRepo
    {
        return $this->requireService();
    }

    public function guestCreate(): Response
    {
        return Inertia::render('Inventory/Personnel/PersonnelRegistrationGuest');
    }

    public function store(CreatePersonnelRegistrationRequest $request): JsonResponse
    {
        $registration = $this->repo()->createGuestRegistration($request->validated());

        return response()->json([
            'message' => 'Registration submitted. Please verify your email address before administrator review.',
            'data' => [
                'id' => $registration->id,
                'status' => $registration->status,
                'email' => $registration->email,
            ],
        ], 201);
    }

    public function verify(Request $request, PersonnelRegistration $registration): Response
    {
        abort_unless(
            $request->hasValidSignature() || $request->hasValidRelativeSignature(),
            403,
        );

        $registration = $this->repo()->verifyEmail($registration);

        return Inertia::render('Inventory/Personnel/PersonnelRegistrationVerified', [
            'registration' => [
                'id' => $registration->id,
                'email' => $registration->email,
                'status' => $registration->status,
                'full_name' => $registration->full_name,
                'email_verified_at' => $registration->email_verified_at,
            ],
        ]);
    }

    public function index(GetPersonnelRequest $request): Collection
    {
        return parent::_index($request);
    }

    public function updateStatus(UpdatePersonnelRegistrationStatusRequest $request, string $id): JsonResponse
    {
        $registration = $this->repo()->updateStatus($id, $request->validated(), optional($request->user())->id);

        return response()->json([
            'message' => 'Personnel registration updated.',
            'data' => $registration,
        ]);
    }

    public function idCardsPrint(PersonnelIdCardService $idCards): Response
    {
        $registrations = $this->repo()
            ->getApprovedIdCardRegistrations()
            ->map(fn (PersonnelRegistration $registration) => $idCards->cardData(
                $registration,
                route('personnels.id-cards.photo', $registration),
            ))
            ->values();

        return Inertia::render('Inventory/Personnel/PersonnelIdCardsPrint', [
            'cards' => $registrations,
            'fromUrl' => route('personnels.registrations.index'),
        ]);
    }

    public function idCardPhoto(PersonnelRegistration $registration)
    {
        abort_unless($registration->requires_cbc_id_card && $registration->id_photo_path, 404);
        abort_unless(Storage::disk('local')->exists($registration->id_photo_path), 404);

        return response(Storage::disk('local')->get($registration->id_photo_path), 200, [
            'Content-Type' => Storage::disk('local')->mimeType($registration->id_photo_path) ?: 'image/jpeg',
            'Cache-Control' => 'private, max-age=3600',
        ]);
    }

    public function markPrinted(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['required', 'string', 'uuid'],
        ]);

        $this->repo()->markIdCardsAsPrinted($validated['ids']);

        return response()->json([
            'message' => 'ID cards marked as printed successfully.',
        ]);
    }
}
