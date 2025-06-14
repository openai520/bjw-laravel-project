<?php

use App\Http\Controllers\Api\ProductViewController;
use App\Http\Controllers\Frontend\CartController as FrontendCartController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::get('/cart/summary', [FrontendCartController::class, 'summary'])->name('api.cart.summary');

// 产品访问统计路由
Route::post('/products/{product}/view', [ProductViewController::class, 'recordView'])->name('api.products.record-view');
Route::get('/products/{product}/stats', [ProductViewController::class, 'getStats'])->name('api.products.stats');
