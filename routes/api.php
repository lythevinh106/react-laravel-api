<?php

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
Route::resource('category', CategoryController::class);


// Route::prefix('product')->group(function () {


//     Route::get('/', [ProductController::class, "index"]);
// });




// Route::prefix('category')->group(function () {
//     Route::get('add', [CategoryController::class, "create"]);
//     Route::get('/', [CategoryController::class, "index"]);
// });
