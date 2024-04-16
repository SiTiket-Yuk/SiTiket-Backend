<?php

use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
  return $request->user();
})->middleware('auth:sanctum');

// Impelement API Routes here
Route::post('/add-user', [UserController::class, 'create']);
Route::get('/', [UserController::class, 'index']);
Route::put('/', [UserController::class, 'edit']);
Route::delete('/', [UserController::class, 'delete']);
