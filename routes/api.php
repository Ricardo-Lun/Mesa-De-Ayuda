<?php

use App\Http\Controllers\ServiceController;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::apiResource('services', ServiceController::class);

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');