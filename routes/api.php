<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\QueryController;

Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login',    [AuthController::class, 'login']);

Route::middleware('auth:api')->group(function () {
    Route::post('/documents/upload', [DocumentController::class, 'upload']); // protegido
});

Route::middleware(['auth:api','throttle:uploads'])->post('/documents/upload', [DocumentController::class,'upload']);
Route::middleware(['throttle:queries'])->post('/query', [QueryController::class,'search']);


Route::post('/query', [QueryController::class, 'search']); // puede ser público o protegido 
