<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Services\RisOcrService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RisOcrController extends BaseController
{
    public function extract(Request $request, RisOcrService $ocrService): JsonResponse
    {
        $request->validate([
            'document' => 'required|image|max:5120',
        ]);

        try {
            $data = $ocrService->extract($request->file('document'));
            return response()->json(['data' => $data]);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }
}
