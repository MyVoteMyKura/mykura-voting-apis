<?php

use App\Http\Controllers\AdminController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VoterController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

//Public routes
//POST APIs
Route::post('/register', [AdminController::class, 'createAdmin']);
Route::post('/login', [AdminController::class, 'adminLogin']);
Route::post('/signup', [VoterController::class, 'signUp']);
Route::post('/google/login', [VoterController::class, 'googleLogin']);


//GET APIs
Route::get('/positions', [VoterController::class, 'getPositions']);
Route::get('/dashboard', [VoterController::class, 'getResults']);


//Protected routes
Route::post('/assign-position', [AdminController::class, 'assignPosition'])->middleware('auth:sanctum');
Route::post('/vote', [VoterController::class, 'vote'])->middleware('auth:sanctum');
Route::get('/candidates', [VoterController::class, 'getCandidatesV2'])->middleware('auth:sanctum');
Route::get('/vvpat', [VoterController::class, 'vvpat'])->middleware('auth:sanctum');
Route::get('/voters', [AdminController::class, 'getVoters'])->middleware('auth:sanctum');