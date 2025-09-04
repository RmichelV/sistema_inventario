<?php


use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

//controladores 
use App\Http\Controllers\UserController;
use App\Http\Controllers\AttendanceRecordController;
use App\Http\Controllers\SalaryAdjustmentController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\ProductStoreController;
use App\Http\Controllers\UsdExchangeRateController;

//modelos
use App\Models\Usd_exchange_rate;
use App\Models\Product;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    $usd = Usd_exchange_rate::find(1);
    $products = Product::all(['id','name','code']);
    return Inertia::render('Dashboard',[
        'usd'=>$usd,
        'products'=>$products
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';

//rutas propias
Route::resource('rusers', UserController::class);
Route::resource('rattendance_records', AttendanceRecordController::class);
Route::resource('rsalary_adjustments', SalaryAdjustmentController::class);
Route::resource('rproducts', ProductController::class);
Route::resource('rpurchases', PurchaseController::class);
Route::resource('rproductstores', ProductStoreController::class);
Route::resource('rusdexchangerates', UsdExchangeRateController::class);