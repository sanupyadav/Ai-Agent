<?php

use OpenAI\Resources\Chat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\A4fApiController;
use App\Http\Controllers\OpenAIController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');





use App\Http\Controllers\FileEmbedController;

Route::post('/chat-completion', [OpenAIController::class, 'getChatCompletion']);

Route::post('/ask-agent', [ChatController::class, 'ask']);
Route::post('/upload-file', [FileEmbedController::class, 'upload']);

