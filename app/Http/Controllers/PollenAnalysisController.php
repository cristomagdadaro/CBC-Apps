<?php

namespace App\Http\Controllers;

use App\Repositories\PollenAnalysisRepo;
use App\Services\YoloInferenceService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

class PollenAnalysisController extends BaseController
{
    public function __construct(
        PollenAnalysisRepo $service,
        private readonly YoloInferenceService $yoloService
    ) {
        $this->service = $service;
        $this->authorizeResource($this->service->getModelClass(), 'pollen_analysis');
    }

    public function index(Request $request)
    {
        return Inertia::render('PollenCounter/Index', [
            'paginator' => $this->service->search(collect($request->all())),
            'flash' => [
                'success' => session('success'),
                'error' => session('error'),
                'inference_result' => session('inference_result'),
            ],
        ]);
    }

    public function analyze(Request $request)
    {
        $this->authorize('create', $this->service->getModelClass());

        $request->validate([
            'image' => 'required|image|max:10240', // 10MB max
        ]);

        $file = $request->file('image');

        // Send to Python Microservice
        try {
            $result = $this->yoloService->analyze($file);
        } catch (\Exception $e) {
            return back()->with('error', 'AI Inference failed: ' . $e->getMessage());
        }

        // Store image privately
        $path = $file->store('pollen_analyses', 'private');

        // Save history in database
        $analysis = $this->service->create([
            'user_id' => $request->user()->id,
            'image_path' => $path,
            'pollen_count' => $result['count'],
            'inference_time_ms' => $result['inference_time_ms'] ?? null,
        ]);

        // Return Inertia flash data containing the boxes
        // so the frontend can draw them immediately.
        return back()->with('success', 'Image analyzed successfully! Found ' . $result['count'] . ' pollen grains.')
                     ->with('inference_result', [
                         'id' => $analysis->id,
                         'count' => $result['count'],
                         'boxes' => $result['boxes'],
                         'image_url' => route('pollen_analysis.image', $analysis->id)
                     ]);
    }

    public function image(Request $request, $id)
    {
        $analysis = $this->service->findById($id);
        
        $this->authorize('view', $analysis);

        if (!Storage::disk('private')->exists($analysis->image_path)) {
            abort(404);
        }

        return Storage::disk('private')->response($analysis->image_path);
    }
}
