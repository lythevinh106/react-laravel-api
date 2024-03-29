<?php

use App\Http\Controllers\Api\Auth;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\api\OrderController;
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



///product
Route::resource('product', ProductController::class);

Route::post('product/edit/{id}', [ProductController::class, "update"]);
Route::get('products', [ProductController::class, "list"]);
Route::get('productImages/{id}', [ProductController::class, "show_product_images"]);
Route::get('product/ofCategory/{product_id}', [ProductController::class, "show_other_product"]);

Route::post('product/ratingProduct/{product_id}', [ProductController::class, "ratingProduct"]);

Route::post('product/checkRatingProduct/{product_id}', [ProductController::class, "checkRatingProduct"]);

Route::post('product/addComment/{product_id}', [ProductController::class, "addComment"]);
Route::post('product/showComment/{product_id}', [ProductController::class, "showComment"]);

Route::resource('category', CategoryController::class);
Route::post('category/edit/{id}', [CategoryController::class, "update"]);
Route::get('category', [CategoryController::class, "list"]);






/////cart
Route::resource('cart', CartController::class);
Route::post('addCartAuth', [CartController::class, "add_cart_auth"]);
Route::get('showCartAuth/{userId}', [CartController::class, "show_cart_auth"]);
Route::post('updateCartAuth', [CartController::class, "update_cart_auth"]);
Route::post('deleteCartAuth', [CartController::class, "delete_cart_auth"]);
Route::post('removeAllCartAuth', [CartController::class, "remove_all_cart_auth"]);
Route::post('paymentAuth', [CartController::class, "payment_auth"]);
//nofication
Route::get('showNofi', [CartController::class, "show_nofi"]);
Route::get('updateNofi', [CartController::class, "update_nofi"]);






////auth

// Route::resource('auth', AuthController::class);
Route::post('auth/register', [AuthController::class, "register"]);
Route::get('auth/verify/{id}/{token}', [AuthController::class, "verify"]);

Route::post('auth/login', [AuthController::class, "login"]);

Route::post('auth/logout', [AuthController::class, "logout"]);

Route::post('auth/refresh/{refresh_token}', [AuthController::class, "refresh"]);
Route::post('auth/me', [AuthController::class, "me"]);
Route::post('auth/payload', [AuthController::class, "payload"]);

Route::post('auth/updateInfo', [AuthController::class, "update_info"]);








/////order



Route::resource('order', OrderController::class);
Route::post('order/showAll', [OrderController::class, "showAll"]);
Route::post('order/showDetailOrder/{order_id}', [OrderController::class, "showDetail"]);
Route::post('order/removeOrder/{order_id}', [OrderController::class, "removeOrder"]);

Route::post('order/checkToken/{token}', [OrderController::class, "checkToken"]);


Route::get('demoDeploy', function () {
    dd("deloy demo");
});



// Route::post('order/showAll', [OrderController::class, "showAll"]);



// Route::prefix('product')->group(function () {


//     Route::get('/', [ProductController::class, "index"]);
// });




// Route::prefix('category')->group(function () {
//     Route::get('add', [CategoryController::class, "create"]);
//     Route::get('/', [CategoryController::class, "index"]);
// });