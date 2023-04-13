<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\PostController;
use App\Http\Controllers\API\CategoryController;
use App\Http\Controllers\API\CommentController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::post('login', [AuthController::class, 'signin'])->middleware('log.route');
Route::post('register', [AuthController::class, 'signup'])->middleware('log.route');

Route::middleware('auth:sanctum')->group(function () {
    Route::resource('posts', PostController::class)->middleware('log.route');
    Route::resource('categories', CategoryController::class)->middleware('log.route');
    Route::resource('comments', CommentController::class)->middleware('log.route');
});
