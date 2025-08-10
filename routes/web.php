<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\FileEmbedController;


Route::get('/', function () {
    return view('welcome');
});



Route::get('/', [ChatController::class, 'index']);
Route::post('/upload-file', [FileEmbedController::class, 'upload']);
