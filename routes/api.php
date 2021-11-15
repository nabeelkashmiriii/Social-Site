<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Usercontroller;
use App\Http\Controllers\PostController;
use App\Http\Controllers\CommentController;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::post('/register', [UserController::class,'register']);
Route::get('/verifyEmail/{email}', [UserController::class,'verify']);
Route::post('login', [UserController::class,'login']);
Route::get('logout', [UserController::class,'logout']);
Route::post('createpost', [PostController::class,'post']);
Route::delete('deletepost', [PostController::class,'deletPost']);
Route::get('searchpost', [PostController::class,'searchPost']);
Route::post('comment', [CommentController::class,'comment']);
Route::delete('deletecomment', [PostController::class,'deleteComment']);


