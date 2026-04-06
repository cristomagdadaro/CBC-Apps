<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreatePersonnelRequest;
use App\Http\Requests\DeletePersonnelRequest;
use App\Http\Requests\GetPersonnelRequest;
use App\Http\Requests\InitializePersonnelProfileRequest;
use App\Http\Requests\UpdateGuestPersonnelEmailRequest;
use App\Http\Requests\UpdatePersonnelRequest;
use App\Models\Personnel;
use App\Repositories\PersonnelRepo;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class PersonnelController extends BaseController
{
    public function __construct(PersonnelRepo $repository)
    {
        $this->service = $repository;
    }

    public function index(GetPersonnelRequest $request)
    {
        $response = $this->service->search(new Collection($request->validated()));

        $filterOutAdmin = function ($item) {
            return $item->id !== 1;
        };

        if ($response instanceof LengthAwarePaginator) {
            $filtered = $response->getCollection()->filter($filterOutAdmin)->values();
            $response->setCollection($filtered);

            return $response;
        }

        if (is_array($response) && array_key_exists('data', $response)) {
            $response['data'] = collect($response['data'])->filter($filterOutAdmin)->values();

            return $response;
        }

        if ($response instanceof Collection) {
            return $response->filter($filterOutAdmin)->values();
        }

        return $response;
    }

    public function publicLookup(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:32'],
        ]);

        $search = trim((string) ($validated['search'] ?? ''));

        if ($search === '') {
            return response()->json(['data' => []]);
        }

        $personnels = Personnel::query()
            ->select(['id', 'fname', 'mname', 'lname', 'suffix', 'position', 'email', 'updated_at'])
            ->where('employee_id', $search)
            ->orderBy('lname')
            ->orderBy('fname')
            ->limit(5)
            ->get()
            ->map(function (Personnel $personnel) {
                return [
                    'id' => $personnel->id,
                    'fname' => $personnel->fname,
                    'mname' => $personnel->mname,
                    'lname' => $personnel->lname,
                    'suffix' => $personnel->suffix,
                    'position' => $personnel->position,
                    'fullName' => collect([
                        $personnel->fname,
                        $personnel->mname,
                        $personnel->lname,
                        $personnel->suffix,
                    ])->filter()->implode(' '),
                    'profile_requires_update' => $personnel->updated_at === null,
                    'has_email' => filled($personnel->email),
                ];
            })
            ->values();

        return response()->json(['data' => $personnels]);
    }

    public function initializeProfile(InitializePersonnelProfileRequest $request): JsonResponse
    {
        $personnel = Personnel::query()
            ->where('employee_id', $request->validated('employee_id'))
            ->firstOrFail();

        if ($personnel->updated_at !== null) {
            return response()->json([
                'message' => 'Personnel information has already been initialized.',
            ], 409);
        }

        $personnel->fill(collect($request->validated())
            ->except('employee_id')
            ->toArray());
        $personnel->save();

        return response()->json([
            'message' => 'Personnel information updated successfully.',
            'data' => [
                'id' => $personnel->id,
                'fname' => $personnel->fname,
                'mname' => $personnel->mname,
                'lname' => $personnel->lname,
                'suffix' => $personnel->suffix,
                'position' => $personnel->position,
                'fullName' => collect([
                    $personnel->fname,
                    $personnel->mname,
                    $personnel->lname,
                    $personnel->suffix,
                ])->filter()->implode(' '),
                'profile_requires_update' => false,
                'has_email' => filled($personnel->email),
            ],
        ]);
    }

    public function updateGuestEmail(UpdateGuestPersonnelEmailRequest $request): JsonResponse
    {
        $personnel = Personnel::query()
            ->where('employee_id', $request->validated('employee_id'))
            ->firstOrFail();

        $personnel->forceFill([
            'email' => $request->validated('email'),
        ])->save();

        return response()->json([
            'message' => 'Email updated successfully.',
            'data' => [
                'id' => $personnel->id,
                'employee_id' => $personnel->employee_id,
                'email' => $personnel->email,
                'has_email' => filled($personnel->email),
            ],
        ]);
    }

    public function create(CreatePersonnelRequest $request): Model
    {
        return parent::_store($request);
    }

    public function update(UpdatePersonnelRequest $request, string $id): Model
    {
        return parent::_update($id, $request);
    }

    public function destroy(DeletePersonnelRequest $request, string $id): Model|JsonResponse
    {
        return parent::_destroy($id);
    }
}
