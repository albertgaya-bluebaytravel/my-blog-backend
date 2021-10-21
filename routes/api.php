<?php

use App\Http\Controllers\CommentController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

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

Route::prefix('/users')->group(function () {
    Route::get('/{user}/verify/{token}', [UserController::class, 'verify']);
    Route::post('/register', [UserController::class, 'register']);
    Route::post('/login', [UserController::class, 'login']);
    Route::get('/auth', [UserController::class, 'auth'])->middleware('auth:sanctum');
});

Route::prefix('/posts')->group(function () {
    Route::get('/', [PostController::class, 'index']);
    Route::get('/{post}', [PostController::class, 'show']);
    Route::patch('/{post}', [PostController::class, 'update']);
    Route::delete('/{post}', [PostController::class, 'destroy']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/', [PostController::class, 'store']);
        Route::post('/{post}/comments', [CommentController::class, 'store']);
    });
});
