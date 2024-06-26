<?php

use App\Http\Controllers\Api\PublicController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', [PublicController::class, 'welcome'])
    ->name('home');
Route::get('/unauthenticated', [PublicController::class, 'unauthenticated'])
    ->name('authentication.notice');
Route::get('/unverified', [PublicController::class, 'unverified'])
    ->name('verification.notice');
