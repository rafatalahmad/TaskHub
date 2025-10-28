<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('Register',[AuthController::class,'Register']);
Route::post('login',[AuthController::class,'login']);
Route::post('logout',[AuthController::class,'logout'])->middleware('auth:sanctum');

Route::apiResource('projects',ProjectController::class)->middleware('auth:sanctum');

Route::post('projects/{project}/add-member', [ProjectController::class, 'addMember'])->middleware('auth:sanctum');
Route::delete('projects/{project}/remove-member', [ProjectController::class, 'removeMember'])->middleware('auth:sanctum');
Route::post('projects/{project}/tasks', [TaskController::class, 'store'])->middleware('auth:sanctum');
