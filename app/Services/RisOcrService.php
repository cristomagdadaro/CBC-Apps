<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;

class RisOcrService
{
    public function extract(UploadedFile $image): ?array
    {
        $baseUrl = rtrim(config('services.sproutai.host', 'http://127.0.0.1:8000'), '/');
        $token = env('LLM_API_KEY'); // Chatbot uses LLM_API_KEY in app.blade.php

        $response = Http::withToken($token)
            ->attach('document', file_get_contents($image->getRealPath()), $image->getClientOriginalName())
            ->post("{$baseUrl}/api/ocr/extract-ris");

        if ($response->successful()) {
            return $response->json('data');
        }

        throw new \Exception('Failed to extract RIS data: ' . $response->body());
    }
}
