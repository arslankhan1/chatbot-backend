<?php

use App\Http\Controllers\ChatbotController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::prefix('chatbot')->group(function () {
    Route::post('/start-session', [ChatbotController::class, 'startSession']);
    Route::get('/next-question', [ChatbotController::class, 'getNextQuestion']);
    Route::post('/answer', [ChatbotController::class, 'submitAnswer']);
    Route::get('/session-summary', [ChatbotController::class, 'getSessionSummary']);
    Route::post('/restart-session', [ChatbotController::class, 'restartSession']);
    Route::get('/services', [ChatbotController::class, 'getAllServices']);
});

Route::post('/start-session', [ChatbotController::class, 'startSession']);
Route::get('/next-question', [ChatbotController::class, 'getNextQuestion']);
Route::post('/answer', [ChatbotController::class, 'submitAnswer']);
Route::get('/session-summary', [ChatbotController::class, 'getSessionSummary']);
Route::post('/restart-session', [ChatbotController::class, 'restartSession']);
Route::get('/services', [ChatbotController::class, 'getAllServices']);
