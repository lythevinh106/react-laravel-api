<?php

use App\Http\Controllers\Api\Auth;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use Illuminate\Http\Request;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});




Route::resource('product', ProductController::class);


Route::post('product/edit/{id}', [ProductController::class, "update"]);
Route::get('products', [ProductController::class, "list"]);
Route::get('productImages/{id}', [ProductController::class, "show_product_images"]);
Route::get('product/ofCategory/{product_id}', [ProductController::class, "show_other_product"]);



Route::resource('category', CategoryController::class);
Route::post('category/edit/{id}', [CategoryController::class, "update"]);
Route::get('category', [CategoryController::class, "list"]);




/////cart
Route::resource('cart', CartController::class);



////auth

// Route::resource('auth', AuthController::class);
Route::post('auth/register', [AuthController::class, "register"]);
Route::get('auth/verify/{id}/{token}', [AuthController::class, "verify"]);

Route::post('auth/login', [AuthController::class, "login"]);

Route::post('auth/logout', [AuthController::class, "logout"]);

Route::post('auth/refresh/{refresh_token}', [AuthController::class, "refresh"]);
Route::post('auth/me', [AuthController::class, "me"]);
Route::post('auth/payload', [AuthController::class, "payload"]);






// Route::prefix('product')->group(function () {


//     Route::get('/', [ProductController::class, "index"]);
// });




// Route::prefix('category')->group(function () {
//     Route::get('add', [CategoryController::class, "create"]);
//     Route::get('/', [CategoryController::class, "index"]);
// });
