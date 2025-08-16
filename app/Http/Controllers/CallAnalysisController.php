<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use LarAgent\Facades\LarAgent;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class CallAnalysisController extends Controller
{
    public function index()
    {
        return view('welcome');
    }

   
        public function uploadAudio(Request $request)
        {
            $request->validate([
                'audio' => 'required|file|mimes:mp3,wav,m4a|max:10240', // max 10MB
            ]);
    
            $file = $request->file('audio');
            $path = $file->store('audio');
    
            // For demo, return a fake transcript
            $transcript = "This is a demo transcript of the uploaded call.";
    
            // You would call your transcription logic here
    
            return response()->json([
                'message' => 'File uploaded successfully!',
                'path' => $path,
                'transcript' => $transcript,
            ]);
        }
    

    // Analyze conversation text with AI agent
    public function analyzeConversation(Request $request)
    {
        $request->validate([
            'transcript' => 'required|string',
        ]);

        $transcript = $request->input('transcript');

        $prompt = <<<EOT
Analyze the following customer-employee conversation and provide:
1. Employee politeness and performance.
2. Customer satisfaction level.
3. Key points where the employee did well or poorly.

Conversation:
$transcript
EOT;

        try {
            $response = LarAgent::agent('support_agent')->respond($prompt);

            return response()->json(['analysis' => $response]);
        } catch (\Throwable $e) {
            Log::error('Analysis Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['error' => 'Failed to analyze conversation.'], 500);
        }
    }

    // Example local Whisper transcription helper (optional)
    // protected function runWhisperTranscription(string $filePath): string
    // {
    //     $command = escapeshellcmd("python3 /path/to/whisper_transcribe.py " . escapeshellarg($filePath));
    //     exec($command, $output, $returnVar);

    //     if ($returnVar === 0) {
    //         return implode("\n", $output);
    //     }

    //     return "Error transcribing audio.";
    // }
}
