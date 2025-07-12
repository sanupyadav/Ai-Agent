<?php

namespace App\Http\Controllers;

use App\Models\VectorChunk;
use Illuminate\Http\Request;
use PhpOffice\PhpWord\IOFactory;
use Illuminate\Routing\Controller;
use OpenAI\Laravel\Facades\OpenAI;
use PhpOffice\PhpWord\Element\Text;
use Smalot\PdfParser\Parser as PdfParser;
use PhpOffice\PhpWord\Element\TextRun;
use Illuminate\Support\Facades\Storage;

class FileEmbedController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:pdf,txt,docx',
        ]);
    
        $file = $request->file('file');
        $text = $this->extractText($file);
        $chunks = $this->chunkText($text);
    
        foreach ($chunks as $chunk) {
            $response = OpenAI::embeddings()->create([
                'model' => 'text-embedding-ada-002',
                'input' => $chunk,
            ]);

            if (!Storage::exists('embeddings')) {
                Storage::makeDirectory('embeddings');
            }            
    
            // Save to storage instead of DB
            $filename = 'embedding_' . uniqid() . '.json';
    
            $data = [
                'text' => $chunk,
                'embedding' => $response->embeddings[0]->embedding,
            ];
    
            Storage::put("embeddings/{$filename}", json_encode($data));
        }
    
        return response()->json(['message' => '✅ File uploaded and embedded successfully']);
    }

    private function extractText($file)
    {
        $ext = $file->getClientOriginalExtension();

        return match ($ext) {
            'pdf' => (new PdfParser())->parseFile($file->getPathname())->getText(),
            'docx' => $this->extractFromDocx($file->getPathname()),
            'txt' => file_get_contents($file->getPathname()),
            default => '',
        };
    }

    
    private function extractFromDocx($path)
    {
        $text = '';
        $phpWord = IOFactory::load($path);
    
        foreach ($phpWord->getSections() as $section) {
            foreach ($section->getElements() as $element) {
                // If the element is plain text
                if ($element instanceof Text) {
                    $text .= $element->getText() . "\n";
                }
    
                // If the element is a TextRun (contains multiple Text elements)
                elseif ($element instanceof TextRun) {
                    foreach ($element->getElements() as $subElement) {
                        if ($subElement instanceof Text) {
                            $text .= $subElement->getText() . "\n";
                        }
                    }
                }
            }
        }
    
        return $text;
    }
    

    private function chunkText($text, $size = 300, $overlap = 50)
    {
        $words = explode(' ', $text);
        $chunks = [];

        for ($i = 0; $i < count($words); $i += $size - $overlap) {
            $chunk = array_slice($words, $i, $size);
            $chunks[] = implode(' ', $chunk);
        }

        return $chunks;
    }
}
