<?php

namespace App\Http\Controllers;

use App\Models\LocCity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LocationController extends BaseController
{
    public function regions(): JsonResponse
    {
        $regions = LocCity::query()
            ->select('region')
            ->distinct()
            ->orderBy('region')
            ->pluck('region');

        return response()->json(['data' => $regions]);
    }

    public function provinces(Request $request): JsonResponse
    {
        $region = $request->query('region');

        $query = LocCity::query()->select('province')->distinct();

        if ($region) {
            $query->where('region', $region);
        }

        $provinces = $query->orderBy('province')->pluck('province');

        return response()->json(['data' => $provinces]);
    }

    public function cities(Request $request): JsonResponse
    {
        $province = $request->query('province');
        $region = $request->query('region');

        $query = LocCity::query()->select('city');

        if ($region) {
            $query->where('region', $region);
        }

        if ($province) {
            $query->where('province', $province);
        }

        $cities = $query->orderBy('city')->pluck('city');

        return response()->json(['data' => $cities]);
    }
}
