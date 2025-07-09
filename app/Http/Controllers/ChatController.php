<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use LarAgent\Facades\LarAgent;
use App\AiAgents\SupportAgent1;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;

class ChatController extends Controller
{
    public function index()
    {
        $models = config('ai_models');
        return view('chat', compact('models'));
    }

    public function ask(Request $request)
    {
        try {
            $provider = $request->provider;
            $model = $request->model;
            $sessionId = $request->session_id;

            $agent = new SupportAgent1($provider, $sessionId, $model);
            $response = $agent->respond($request->input('query'));

            return response()->json(['reply' => $response]);
        } catch (\Throwable $e) {
            
            Log::error('AI Agent Error: ' . $e->getMessage(), [
                'provider' => $request->provider,
                'model' => $request->model,
                'session_id' => $request->session_id,
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'reply' => '⚠️ Sorry, something went wrong while generating the response.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
