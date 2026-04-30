<?php
// src/routes/api.php
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\FeedController;
use App\Http\Controllers\FollowController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// ---------- Auth (público) ----------
Route::prefix('auth')->middleware('throttle:5,1')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login',    [AuthController::class, 'login']);
});

// ---------- Rotas protegidas ----------
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me',      [AuthController::class, 'me']);
    });

    // Feed
    Route::get('/feed', [FeedController::class, 'index']);

    // Posts
    Route::post('/posts',          [PostController::class, 'store']);
    Route::get('/posts/{id}',      [PostController::class, 'show']);
    Route::put('/posts/{id}',      [PostController::class, 'update']);
    Route::delete('/posts/{id}',   [PostController::class, 'destroy']);

    // Likes
    Route::post('/posts/{id}/like',     [LikeController::class, 'like']);
    Route::delete('/posts/{id}/unlike', [LikeController::class, 'unlike']);
    Route::get('/posts/{id}/likes',     [LikeController::class, 'likers']);

    // Comments
    Route::get('/posts/{id}/comments',    [CommentController::class, 'index']);
    Route::post('/posts/{id}/comments',   [CommentController::class, 'store']);
    Route::put('/comments/{id}',          [CommentController::class, 'update']);
    Route::delete('/comments/{id}',       [CommentController::class, 'destroy']);

    // Users — ATENÇÃO: rotas estáticas ANTES das dinâmicas
    Route::get('/users/search',           [UserController::class, 'search']);
    Route::get('/users/suggestions',      [UserController::class, 'suggestions']);
    Route::put('/users/me',               [UserController::class, 'update']);
    Route::delete('/users/me',            [UserController::class, 'deleteAccount']);
    Route::post('/users/me/avatar',       [UserController::class, 'uploadAvatar']);
    Route::get('/users/{username}',       [UserController::class, 'show']);
    Route::get('/users/{id}/posts',       [UserController::class, 'posts']);

    // Follows
    Route::post('/users/{id}/follow',       [FollowController::class, 'follow']);
    Route::delete('/users/{id}/unfollow',   [FollowController::class, 'unfollow']);
    Route::get('/users/{id}/is-following',  [FollowController::class, 'isFollowing']);
    Route::get('/users/{id}/followers',     [FollowController::class, 'followers']);
    Route::get('/users/{id}/following',     [FollowController::class, 'following']);

});