<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class OpenAIController extends Controller
{
    public function getChatCompletion(Request $request)
    {
        // Validate input
        $validated = $request->validate([
            'prompt' => 'nullable|string|max:500',
            'model' => 'nullable|string',
        ]);

        try {
            // Define the script path
            $scriptPath = base_path('scripts/openai_chat.py');
            if (!file_exists($scriptPath)) {
                throw new \Exception("Python script not found at: $scriptPath");
            }

            $prompt = $validated['prompt'] ?? 'Explain the concept of API gateways.';
            $model = $validated['model'] ?? 'provider-2/gemini-2.0-flash';

            // Create and run the process with prompt and model
            $process = new Process(['python3', $scriptPath, $prompt, $model]);
            $process->setEnv(['A4F_API_KEY' => env('A4F_API_KEY')]);
            $process->run();

            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }

            // Decode JSON output
            $output = json_decode($process->getOutput(), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Invalid JSON response from Python script: ' . $process->getOutput());
            }

            return response()->json([
                'status' => $output['status'],
                'data' => $output['result'] ?? $output['message'],
                'model' => $output['model'],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                'model' => $model ?? null,
            ], 500);
        }
    }
}