<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DrugController;
use App\Http\Controllers\Api\UserDrugController;


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

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('throttle:30,1')->get('/search', [DrugController::class, 'search']);


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();

});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/user/drugs', [UserDrugController::class, 'store']);
    Route::get('/user/drugs', [UserDrugController::class, 'index']);
    Route::delete('/user/drugs/{rxcui}', [UserDrugController::class, 'destroy']);
});
