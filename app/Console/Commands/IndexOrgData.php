<?php

namespace App\Console\Commands;

use Predis\Client as Redis;
use Smalot\PdfParser\Parser;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class IndexOrgData extends Command
{
    protected $signature = 'index:org-data';
    protected $description = 'Index documents into Redis using Ollama embeddings';

    public function handle()
    {
        $this->info("Indexing documents into Redis using Ollama...\n");

        $directory = storage_path('app/public/embeddings');
        $pdfParser = new Parser();
        $redis = new Redis();

        // Scan directory for PDF files
        $files = scandir($directory);
        foreach ($files as $file) {
            $filePath = $directory . DIRECTORY_SEPARATOR . $file;

            // Skip directories & non-pdf files
            if (is_dir($filePath) || strtolower(pathinfo($filePath, PATHINFO_EXTENSION)) !== 'pdf') {
                continue;
            }

            $this->info("Processing file: {$file}");

            try {
                // Extract text from PDF
                $pdf = $pdfParser->parseFile($filePath);
                $text = $pdf->getText();

                // Skip empty files
                if (empty(trim($text))) {
                    $this->warn("⚠️ Skipped empty PDF: {$file}");
                    continue;
                }

                // Get embedding from Ollama
                $embedding = $this->getOllamaEmbedding($text);

                // Store in Redis
                $redisKey = "doc:" . pathinfo($file, PATHINFO_FILENAME);
                $redis->hmset($redisKey, [
                    'filename' => $file,
                    'content' => $text,
                    'embedding' => json_encode($embedding),
                ]);

                $this->info("✅ Indexed: {$file}");
            } catch (\Exception $e) {
                $this->error("❌ Failed to process {$file}: " . $e->getMessage());
            }
        }

        $this->info("\nAll documents indexed successfully.");
    }

    private function getOllamaEmbedding($text)
    {
        $url = "http://localhost:11434/api/embeddings";

        $payload = [
            "model" => "nomic-embed-text", // Make sure this model is pulled: ollama pull nomic-embed-text
            "prompt" => $text
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

        $response = curl_exec($ch);
        if ($response === false) {
            throw new \Exception('Ollama request failed: ' . curl_error($ch));
        }
        curl_close($ch);

        $result = json_decode($response, true);
        return $result['embedding'] ?? [];
    }
}
