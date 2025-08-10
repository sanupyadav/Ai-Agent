<?php

use OpenAI\Resources\Chat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\FileEmbedController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::post('/ask-agent', [ChatController::class, 'ask']);
Route::post('/upload-file', [FileEmbedController::class, 'upload']);

