<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\UploadedFile;
use Exception;

class YoloInferenceService
{
    private string $apiUrl = 'http://127.0.0.1:8001/predict';

    /**
     * Send the image to the Python FastAPI microservice for YOLO inference.
     *
     * @param UploadedFile $file
     * @return array
     * @throws Exception
     */
    public function analyze(UploadedFile $file): array
    {
        $response = Http::attach(
            'image',
            file_get_contents($file->getPathname()),
            $file->getClientOriginalName()
        )->post($this->apiUrl);

        if ($response->failed()) {
            throw new Exception('YOLO Inference API failed: ' . $response->body());
        }

        return $response->json();
    }
}
