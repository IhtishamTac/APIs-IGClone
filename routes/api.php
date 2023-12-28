<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\FollowController;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::prefix('v1')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('register', [AuthController::class, 'register']);
        Route::post('login', [AuthController::class, 'login']);
        Route::middleware(['auth:sanctum'])->group(function () {
            Route::post('logout', [AuthController::class, 'logout']);
        });
    });
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::resource('posts', PostController::class);
        Route::post('users/{username}/follow',[FollowController::class, 'follAUser']);
        Route::delete('users/{username}/unfollow',[FollowController::class, 'unfollAUser']);
        Route::get('users/{username}/followers',[FollowController::class, 'getFollowersUser']);
        Route::put('users/{username}/accept',[FollowController::class, 'accFollUser']);
        Route::get('users/{username}',[UserController::class, 'getDetailUser']);
        Route::get('users',[UserController::class, 'index']);
        Route::get('following',[FollowController::class, 'getFollUser']);
    });
});
