<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/register', [AuthController::class, 'register']);
Route::post('users/update/{id}', [AuthController::class, 'update']);

Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);

Route::post('/products/add', [ProductController::class, 'store']);
Route::get('products/all', [ProductController::class, 'index']);
Route::get('products/{id}', [ProductController::class, 'show']);
Route::get('products/user/{id}', [ProductController::class, 'showAllProductUser']);
Route::put('products/update/{id}', [ProductController::class, 'update']);
Route::delete('products/delete/{id}', [ProductController::class, 'delete']);
Route::get('products/search/{keyword}', [ProductController::class, 'search']);

Route::post('/orders/add', [OrderController::class, 'store']);
Route::get('/orders', [OrderController::class, 'index']);
