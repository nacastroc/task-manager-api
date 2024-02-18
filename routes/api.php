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
Route::get('/list/{model}', [ApiController::class, 'list'])
    ->middleware('auth:sanctum', 'verified');
Route::get('/show/{model}/{id}', [ApiController::class, 'show'])
    ->middleware('auth:sanctum', 'verified');
Route::post('/add/{model}', [ApiController::class, 'add'])
    ->middleware('auth:sanctum', 'verified');
Route::put('/edit/{model}/{id}', [ApiController::class, 'edit'])
    ->middleware('auth:sanctum', 'verified');
Route::delete('/delete/{model}', [ApiController::class, 'delete'])
    ->middleware('auth:sanctum', 'verified');
Route::delete('/delete/{model}/{id}', [ApiController::class, 'delete'])
    ->middleware('auth:sanctum', 'verified');
