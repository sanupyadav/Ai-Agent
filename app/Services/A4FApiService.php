<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class A4FApiService
{
    protected $apiKey;
    protected $baseUrl;

    public function __construct()
    {
        $this->apiKey = env('A4F_API_KEY');
        $this->baseUrl = 'https://api.a4f.co/v1'; // example base URL
    }

    /**
     * Send audio file for speech-to-text
     * 
     * @param string $audioBase64 Audio content as base64 encoded string
     * @param string $model Model to use for transcription
     * @return array
     */
    public function speechToText(string $audioBase64, string $model = 'provider-6/distil-whisper-large-v3-en'): array
    {
        $url = $this->baseUrl . '/speech-to-text'; // Adjust endpoint to actual speech-to-text API

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
        ])->post($url, [
            'model' => $model,
            'audio' => $audioBase64,  // or 'file_url' if API accepts URL
        ]);

        if ($response->successful()) {
            return $response->json();
        }

        throw new \Exception('API call failed: ' . $response->body());
    }
}
