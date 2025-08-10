<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use PhpOffice\PhpWord\IOFactory;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpWord\Element\Text;
use App\Services\EmbeddingGenerator;
use Smalot\PdfParser\Parser as PdfParser;
use PhpOffice\PhpWord\Element\TextRun;

class FileEmbedController extends Controller
{
    protected $embeddingGenerator;

    public function __construct(EmbeddingGenerator $embeddingGenerator)
    {
        $this->embeddingGenerator = $embeddingGenerator;
    }

    public function upload(Request $request)
{
    try {
        $request->validate([
            'file' => 'required|file|mimes:pdf,txt,docx|max:10240',
        ]);

        $file = $request->file('file');
        $text = $this->extractText($file);

        if (empty($text)) {
            return response()->json(['error' => 'Failed to extract text from file'], 400);
        }

        $chunks = $this->chunkText($text);

        if (empty($chunks)) {
            return response()->json(['error' => 'No text chunks created'], 400);
        }

        $allEmbeddings = [];

        foreach ($chunks as $index => $chunk) {
            $embeddingData = $this->embeddingGenerator->generateEmbeddingData(
                text: $chunk,
                fileName: $file->getClientOriginalName(),
                chunkIndex: $index,
                providerName: 'ollama',
                model: 'nomic-embed-text'
            );

            if ($embeddingData !== null) {
                $allEmbeddings[] = $embeddingData;
            }
        }

        if (empty($allEmbeddings)) {
            return response()->json(['error' => 'Failed to generate embeddings for any chunks'], 500);
        }

        $storagePath = 'embeddings';
        $filename = 'all_embeddings.json';

        if (!\Illuminate\Support\Facades\Storage::exists($storagePath)) {
            \Illuminate\Support\Facades\Storage::makeDirectory($storagePath);
        }

        $fullPath = "{$storagePath}/{$filename}";

        // Read existing embeddings from file
        $existingData = [];
        if (\Illuminate\Support\Facades\Storage::exists($fullPath)) {
            $json = \Illuminate\Support\Facades\Storage::get($fullPath);
            $existingData = json_decode($json, true) ?? [];
        }

        // Merge existing embeddings with new ones
        $mergedData = array_merge($existingData, $allEmbeddings);

        // Save merged data back to the same file
        \Illuminate\Support\Facades\Storage::put($fullPath, json_encode($mergedData));

        return response()->json([
            'message' => '✅ File uploaded and embedded successfully, embeddings appended to all_embeddings.json',
            'chunk_count' => count($chunks),
            'successful_chunks' => count($allEmbeddings),
            'stored_file' => $filename
        ], 200);

    } catch (Exception $e) {
        \Illuminate\Support\Facades\Log::error('File upload error: ' . $e->getMessage());
        return response()->json([
            'error' => 'Failed to process file: ' . $e->getMessage()
        ], 500);
    }
}

    

    private function extractText($file)
    {
        try {
            $ext = strtolower($file->getClientOriginalExtension());

            return match ($ext) {
                'pdf' => $this->extractFromPdf($file->getPathname()),
                'docx' => $this->extractFromDocx($file->getPathname()),
                'txt' => $this->extractFromTxt($file->getPathname()),
                default => throw new Exception('Unsupported file type: ' . $ext),
            };
        } catch (Exception $e) {
            Log::error('Text extraction error: ' . $e->getMessage());
            return '';
        }
    }

    private function extractFromPdf($path)
    {
        try {
            $parser = new PdfParser();
            $pdf = $parser->parseFile($path);
            return trim($pdf->getText());
        } catch (Exception $e) {
            Log::error('PDF extraction error: ' . $e->getMessage());
            return '';
        }
    }

    private function extractFromDocx($path)
    {
        try {
            $text = '';
            $phpWord = IOFactory::load($path);

            foreach ($phpWord->getSections() as $section) {
                foreach ($section->getElements() as $element) {
                    if ($element instanceof Text) {
                        $text .= $element->getText() . "\n";
                    } elseif ($element instanceof TextRun) {
                        foreach ($element->getElements() as $subElement) {
                            if ($subElement instanceof Text) {
                                $text .= $subElement->getText() . "\n";
                            }
                        }
                    }
                }
            }

            return trim($text);
        } catch (Exception $e) {
            Log::error('DOCX extraction error: ' . $e->getMessage());
            return '';
        }
    }

    private function extractFromTxt($path)
    {
        try {
            return trim(file_get_contents($path));
        } catch (Exception $e) {
            Log::error('TXT extraction error: ' . $e->getMessage());
            return '';
        }
    }

    private function chunkText($text, $maxTokens = 500, $overlapTokens = 50)
    {
        try {
            $words = explode(' ', trim($text));
            $chunks = [];
            $currentChunk = [];
            $currentTokenCount = 0;
            $wordCount = count($words);

            // Rough token estimation (1 word ≈ 1.3 tokens)
            for ($i = 0; $i < $wordCount; $i++) {
                $word = $words[$i];
                $wordTokens = ceil(mb_strlen($word) / 4); // Approximate tokens per word

                if ($currentTokenCount + $wordTokens > $maxTokens) {
                    if (!empty($currentChunk)) {
                        $chunks[] = implode(' ', $currentChunk);

                        // Handle overlap
                        $overlapWords = array_slice($currentChunk, -$overlapTokens);
                        $currentChunk = $overlapWords;
                        $currentTokenCount = ceil(count($overlapWords) * 1.3);
                    }
                }

                $currentChunk[] = $word;
                $currentTokenCount += $wordTokens;
            }

            // Add final chunk if not empty
            if (!empty($currentChunk)) {
                $chunks[] = implode(' ', $currentChunk);
            }

            return array_filter($chunks, fn($chunk) => !empty(trim($chunk)));
        } catch (Exception $e) {
            Log::error('Text chunking error: ' . $e->getMessage());
            return [];
        }
    }
}