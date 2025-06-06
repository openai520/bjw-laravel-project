<?php

use Illuminate\Support\Facades\Route;

// 前台控制器
use App\Http\Controllers\Frontend\LanguageController;
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\ProductController;
use App\Http\Controllers\Frontend\CartController;
use App\Http\Controllers\Frontend\InquiryController;

// 后台控制器
use App\Http\Controllers\Admin\AdminLoginController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminCategoryController;
use App\Http\Controllers\Admin\AdminProductController;
use App\Http\Controllers\Admin\AdminBatchProductUploadController;
use App\Http\Controllers\Admin\AdminInquiryController;
use App\Http\Controllers\Admin\AdminIpAddressController;
use App\Http\Controllers\Admin\AdminHomeSettingController;
use App\Http\Controllers\Admin\ProductAnalyticsController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// !!! 临时测试路由，放在最前面 !!!
Route::post('/cart/update/{itemId}', [CartController::class, 'update'])->name('cart.update.temp');
Route::post('/cart/remove/{itemId}', [CartController::class, 'destroy'])->name('cart.remove.temp');

// 语言切换路由
Route::get('/language/{lang}', [LanguageController::class, 'switchLanguage'])
    ->where('lang', '[a-z]{2}|[a-z]{2}_[A-Z]{2}')
    ->name('language.switch');

// 根路径重定向到主页
Route::get('/', function () {
    return redirect()->route('frontend.home', ['lang' => 'en']);
});

// 带语言前缀的前台路由组
Route::prefix('{lang}')
    ->where(['lang' => '[a-z]{2}|[a-z]{2}_[A-Z]{2}'])
    ->middleware('setLocale')
    ->name('frontend.')
    ->group(function () {
        // 主页
        Route::get('/', [HomeController::class, 'index'])->name('home');

        // 产品路由
        Route::get('/products', [ProductController::class, 'index'])->name('products.index');
        Route::get('/products/{product:id}', [ProductController::class, 'show'])->name('products.show');
        
        // 产品模态框API路由
        Route::get('/api/products/{product}/modal', [ProductController::class, 'getModalData'])->name('products.modal');

        // 购物车路由
        Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
        Route::post('/cart', [CartController::class, 'store'])->name('cart.store');
        Route::post('/cart/update/{itemId}', [CartController::class, 'update'])->name('cart.update');
        Route::post('/cart/remove/{itemId}', [CartController::class, 'destroy'])->name('cart.remove');

        // 询价路由
        Route::post('/inquiries', [InquiryController::class, 'store'])->name('inquiries.store');
    });

// 后台管理路由组
Route::prefix('admin')->name('admin.')->group(function () {
    // 登录相关路由
    Route::middleware('guest')->group(function () {
        Route::get('login', [AdminLoginController::class, 'showLoginForm'])->name('login');
        Route::post('login', [AdminLoginController::class, 'login'])->name('login.submit');
    });

    // 需要管理员权限的路由
    Route::middleware(['auth', 'admin'])->group(function () {
        Route::get('dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::post('logout', [AdminLoginController::class, 'logout'])->name('logout');
        Route::resource('categories', AdminCategoryController::class)->except(['show']);
        Route::post('categories/{category}/move', [AdminCategoryController::class, 'move'])->name('categories.move');
        Route::get('products/batch-upload', [AdminBatchProductUploadController::class, 'showUploadForm'])->name('products.batch_upload.form');
        Route::post('products/batch-import', [AdminBatchProductUploadController::class, 'handleBatchImport'])->name('products.batch_import.handle');
        Route::post('products/batch-upload-image', [AdminBatchProductUploadController::class, 'uploadTemporaryImage'])->name('products.batch_upload.image');
        Route::delete('products/batch-destroy', [AdminProductController::class, 'batchDestroy'])->name('products.batch-destroy');
        Route::patch('products/batch-update-status', [AdminProductController::class, 'batchUpdateStatus'])->name('products.batch-update-status');
        Route::post('products/check-name', [AdminProductController::class, 'checkName'])->name('products.check-name');
        Route::resource('products', AdminProductController::class);
        Route::patch('products/{product}/update-price', [AdminProductController::class, 'updatePrice'])->name('products.update-price');
        Route::delete('products/{product}/images/{image}', [AdminProductController::class, 'destroyImage'])->name('products.images.destroy');
        Route::get('inquiries', [AdminInquiryController::class, 'index'])->name('inquiries.index');
        Route::get('inquiries/{inquiry}', [AdminInquiryController::class, 'show'])->name('inquiries.show');
        Route::patch('inquiries/{inquiry}/status', [AdminInquiryController::class, 'updateStatus'])->name('inquiries.updateStatus');
        Route::get('ip-addresses', [AdminIpAddressController::class, 'index'])->name('ip_addresses.index');
        Route::post('ip-addresses/block', [AdminIpAddressController::class, 'block'])->name('ip_addresses.block');
        Route::delete('ip-addresses/unblock/{ip}', [AdminIpAddressController::class, 'unblock'])->name('ip_addresses.unblock');
        Route::post('ip-addresses/clear-logs', [AdminIpAddressController::class, 'clearLogs'])->name('ip_addresses.clear-logs');

        // 首页内容管理
        Route::get('home-settings', [AdminHomeSettingController::class, 'index'])->name('home_settings.index');
        Route::get('home-settings/category/{category}/featured-products', [AdminHomeSettingController::class, 'editFeaturedProducts'])->name('home_settings.edit_featured_products');
        Route::put('home-settings/category/{category}/featured-products', [AdminHomeSettingController::class, 'updateFeaturedProducts'])->name('home_settings.update_featured_products');

        // 产品访问统计
        Route::get('product-analytics', [ProductAnalyticsController::class, 'index'])->name('product_analytics.index');
        Route::get('product-analytics/{product}', [ProductAnalyticsController::class, 'productDetail'])->name('product_analytics.product');

        // 测试路由
        Route::get('/test', function () {
            return view('admin.test');
        })->name('test');
        
        Route::get('/simple-test', function () {
            return view('admin.simple-test');
        })->name('simple-test');
    });
});
