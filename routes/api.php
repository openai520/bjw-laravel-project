<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Frontend\CartController as FrontendCartController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::get('/cart/summary', [FrontendCartController::class, 'summary'])->name('api.cart.summary'); 