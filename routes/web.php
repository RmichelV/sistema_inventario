<?php


use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

//controladores 
use App\Http\Controllers\UserController;
use App\Http\Controllers\AttendanceRecordController;
use App\Http\Controllers\SalaryAdjustmentController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PurchaseController;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';

//rutas propias
Route::resource('rusers', UserController::class);
Route::resource('rattendance_records', AttendanceRecordController::class);
Route::resource('rsalary_adjustments', SalaryAdjustmentController::class);
Route::resource('rproducts', ProductController::class);
Route::resource('rpurchases', PurchaseController::class);