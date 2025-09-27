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
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SaleItemController;
use App\Http\Controllers\DevolutionController;
use App\Http\Controllers\SalaryController;

//modelos
use App\Models\Usd_exchange_rate;
use App\Models\Product;
use App\Models\Product_Store;


Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

// La ruta del dashboard debe estar protegida para todos los roles que pueden acceder a él.
// Reemplazamos 'verified' por el middleware 'role' que acabamos de crear.
Route::get('dashboard', function () {
    $usd = Usd_exchange_rate::find(1);
    $products = Product::all(['id','name','code','quantity_in_stock']);
    $productStores = Product_Store::all();
    return Inertia::render('Dashboard',[
        'usd'=>$usd,
        'products'=>$products,
        'productStores' => $productStores
    ]);
})->middleware(['auth', 'role:1,2,3,4'])->name('dashboard');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';

// Ahora, aplicamos el middleware 'role' a cada recurso.
// Esto protegerá todas las rutas (index, create, store, etc.) del recurso.

// Rutas para "Datos de empleados". Solo el rol 1 (aa) puede administrar usuarios.
Route::resource('rusers', UserController::class)->middleware(['auth', 'role:1']);

// Rutas de "Asistencias". Los roles 1 (aa) y 2 (bb) pueden ver los registros.
Route::resource('rattendance_records', AttendanceRecordController::class)->middleware(['auth', 'role:1,2']);

// Rutas de "Movimientos de salarios". Solo el rol 1 (aa) puede ver y gestionar salarios.
Route::resource('rsalary_adjustments', SalaryAdjustmentController::class)->middleware(['auth', 'role:1']);

// Rutas de "Productos en Bodega". Los roles 1 (aa) y 2 (bb) pueden gestionarlos.
Route::resource('rproducts', ProductController::class)->middleware(['auth', 'role:1,2']);

// Rutas de "Historial de compras". Solo los roles 1 (aa) y 2 (bb) pueden ver las compras.
Route::resource('rpurchases', PurchaseController::class)->middleware(['auth', 'role:1,2']);

// Rutas de "Productos en la tienda". Los roles 1 (aa) y 3 (cc) pueden gestionarlos.
Route::resource('rproductstores', ProductStoreController::class)->middleware(['auth', 'role:1,3']);

// Rutas para "Ventas". Los roles 1 (aa) y 3 (cc) pueden ver y registrar ventas.
Route::resource('rsales', SaleController::class)->middleware(['auth', 'role:1,3']);

// Rutas de ajuste de tipo de cambio. Solo el rol 1 (aa) puede hacerlo.
Route::resource('rusdexchangerates', UsdExchangeRateController::class)->middleware(['auth', 'role:1']);

// Estas rutas probablemente estén relacionadas con las ventas, así que las protegemos para los mismos roles.
Route::resource('rsaleitems',SaleItemController::class)->middleware(['auth', 'role:1,3']);
Route::resource('rdevolutions', DevolutionController::class)->middleware(['auth', 'role:1,3']);

Route::resource('rsalaries',SalaryController::class)->middleware(['auth', 'role:1']);
