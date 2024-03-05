<?php

use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EmailVerificationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Authentication routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth:sanctum');

// Email verification route
Route::get('email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
    ->middleware(['signed', 'throttle:6,1'])
    ->name('verification.verify');

// Generic routes
Route::middleware(['auth:sanctum', 'verified', 'add.model'])->group(function () {
    $allowedModels = config('constants.messages.validation.model_routes');
    Route::get('/{model}', [ApiController::class, 'list'])
        ->where('model', $allowedModels);
    Route::get('/{model}/{id}', [ApiController::class, 'show'])
        ->where('model', $allowedModels);
    Route::post('/{model}', [ApiController::class, 'create'])
        ->where('model', $allowedModels);
    Route::put('/{model}/{id}', [ApiController::class, 'update'])
        ->where('model', $allowedModels);
    Route::delete('/{model}', [ApiController::class, 'delete'])
        ->where('model', $allowedModels);
    Route::delete('/{model}/{id}', [ApiController::class, 'delete'])
        ->where('model', $allowedModels);
});
