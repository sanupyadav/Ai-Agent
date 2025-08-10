<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class EmbeddingGenerator
{
    public function generateEmbeddingData(
        string $text,
        string $fileName = 'unknown',
        int $chunkIndex = 0,
        string $providerName = 'ollama',
        string $model = 'nomic-embed-text'
    ) {
        $embedding = $this->generateEmbedding($text, $providerName, $model);
    
        if (!$embedding) {
            Log::error("Failed to generate embedding for chunk {$chunkIndex} of file {$fileName}");
            return null;
        }
    
        return [
            'text' => $text,
            'embedding' => $embedding,
            'created_at' => now()->toISOString(),
            'file_name' => $fileName,
            'chunk_index' => $chunkIndex,
        ];
    }
    

    public function generateEmbedding(string $text, string $providerName = 'ollama', string $model = 'nomic-embed-text')
    {
        $provider = config("laragent.providers.{$providerName}");
        
        if (!$provider) {
            Log::error("Provider '{$providerName}' not configured in laragent.php");
            throw new \Exception("Provider '{$providerName}' not configured");
        }

        $apiUrl = rtrim($provider['api_url'], '/') . '/embeddings';
        
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $provider['api_key'],
                'Content-Type' => 'application/json',
            ])->post($apiUrl, [
                'model' => $model,
                'input' => $text,
            ]);

            if ($response->failed()) {
                $errorBody = $response->body();
                Log::error("Embedding generation failed for provider {$providerName}, model {$model}: {$errorBody}");
                throw new \Exception("API request failed: {$errorBody}");
            }

            $data = $response->json();

            if (!isset($data['data'][0]['embedding'])) {
                Log::error("Invalid embedding response from provider {$providerName}, model {$model}: " . json_encode($data));
                throw new \Exception('Invalid embedding response structure');
            }

            return $data['data'][0]['embedding'];
        } catch (\Exception $e) {
            Log::error("Embedding error for provider {$providerName}, model {$model}: " . $e->getMessage());
            return null;
        }
    }
}