<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
  return $request->user();
})->middleware('auth:sanctum');

use App\Http\Controllers\AuthController;

Route::post('/register', [AuthController::class, 'Register']);
Route::post('/login', [AuthController::class, 'Login']);


use App\Http\Controllers\ImageController;

Route::get('/image/asset/{id}', [ImageController::class, 'GetEventAssest']);
Route::get('/image/post/{id}', [ImageController::class, 'GetImage']);
Route::get('/image/logo/{id}', [ImageController::class, 'GetLogo']);
Route::post('/image/upload', [ImageController::class, 'uploadImage']);


use App\Http\Controllers\EventController;

Route::get('/event/events', [EventController::class, 'AllEventData']);
Route::get('/event/featured', [EventController::class, 'FeaturedEvent']);
Route::get('/event/ongoingEvent', [EventController::class, 'OngoingEvent']);
Route::post('/event/register', [EventController::class, 'Register']);
Route::post('/event/registerUser', [EventController::class, 'AddRegisteredUserEvent']);
Route::get('/event/{id}', [EventController::class, 'EventData']);


use App\Http\Controllers\UserController;

Route::get('/user/{uid}', [UserController::class, 'UserData']);
Route::get('/get-uid/{email}', [UserController::class, 'GetUidwithEmail']);
