<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;

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

Route::post('/login',[AuthController::class, 'login'])->name('login');
Route::post('/register',[AuthController::class, 'register']);

Route::middleware('auth:api')->group(function(){
    Route::get('/get-users',[AuthController::class, 'getUsers'])->name('users');
    Route::get('/user',[AuthController::class, 'user'])->name('user');
});

