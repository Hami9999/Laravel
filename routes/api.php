<?php

use App\Http\Controllers\CommentController;
use App\Http\Controllers\PostController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);


Route::middleware('auth:api')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/refresh', [AuthController::class, 'refresh']);

    // Prefix for Post routes
    Route::prefix('posts')->group(function () {
        Route::get('all-posts', [PostController::class, 'index']);
        Route::post('add-post', [PostController::class, 'store']);
        Route::get('/post/{id}', [PostController::class, 'show']);
        Route::put('/post/{post}', [PostController::class, 'update']);
        Route::delete('/post/{post}', [PostController::class, 'destroy']);
        Route::get('/post/search', [PostController::class, 'search']);
    });

    // Prefix for Comment routes
    Route::prefix('comments')->group(function () {
        Route::get('/{postId}/comments', [CommentController::class, 'index']);
        Route::post('/{postId}/comments', [CommentController::class, 'store']);
        Route::put('/{id}', [CommentController::class, 'update']);
        Route::delete('/{id}', [CommentController::class, 'destroy']);
    });
});



