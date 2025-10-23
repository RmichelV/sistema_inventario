<?php

use App\Http\Controllers\showSalaries;
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
use App\Http\Controllers\SalaryFController;
use App\Http\Controllers\BranchController;

//modelos
use App\Models\Usd_exchange_rate;
use App\Models\Product;
use App\Models\Product_Store;

//librerias
use Carbon\Carbon;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    $usd = Usd_exchange_rate::find(1);
    // Aseguramos un valor por defecto si no existe el registro para evitar accesos sobre null
    $exchangeRate = $usd?->exchange_rate ?? 1;
    // `quantity_in_stock` fue movida a la tabla de inventarios por sucursal (Product_Store / product_branches).
    // Aquí solo traemos los datos básicos del producto (metadata) para búsquedas en el dashboard.
    $products = Product::all(['id','name','code']);
    
    // --- 1. PRODUCTOS EN TIENDA (RAW) - NECESARIO PARA LA FUNCIÓN DE BÚSQUEDA DEL DASHBOARD ---
    // Obtenemos todos los productos en tienda (sin el 'with("product")' para hacerlo más ligero
    // ya que el frontend solo necesita estas tres columnas para la búsqueda)
    $productStoresRaw = Product_Store::all(['product_id', 'quantity', 'unit_price']);
    
    // --- 2. PRODUCTOS NO ACTUALIZADOS EN LOS ÚLTIMOS 15 DÍAS ---
    $cutoff15Days = Carbon::now()->subDays(15);
    
    // Consulta para productos cuya última actualización fue ANTERIOR a 15 días
    $productinStore15 = Product_Store::with("product")
        ->where('last_update', '<', $cutoff15Days)
        ->get();

    $productStores15Days = $productinStore15->map(function ($productstore ) use ($exchangeRate) {
            // Lógica de cálculo y formato de precios
            $unit_price_bs = 'Bs. ' . number_format(($exchangeRate * $productstore->unit_price),2); 
            // Corregí la lógica del porcentaje para ser consistente, aunque el frontend también tiene un cálculo similar.
            $porcentaje = 'Bs. ' . number_format((($exchangeRate * $productstore->unit_price) * 1.1),2); 
            
            return [
                "id"=> $productstore->id,
                "product_id"=> $productstore->product->code,
                "quantity"=> $productstore->quantity,
                "unit_price"=> $productstore->unit_price,
                "unit_price_bs"=>$unit_price_bs,
                "porcentaje"=>$porcentaje,
                "last_update" => $productstore->last_update, // Usamos diffForHumans para mostrar hace cuánto tiempo se actualizó
            ];
        }); 
    
    // --- 3. PRODUCTOS NO ACTUALIZADOS EN LOS ÚLTIMOS 30 DÍAS ---
    $cutoff30Days = Carbon::now()->subDays(30);

    // Consulta para productos cuya última actualización fue ANTERIOR a 30 días
    $productinStore30 = Product_Store::with("product")
        ->where('last_update', '<', $cutoff30Days)
        ->get();
    
    $productStores30Days = $productinStore30->map(function ($productstore ) use ($exchangeRate) {
            // Lógica de cálculo y formato de precios (es la misma)
            $unit_price_bs = 'Bs. ' . number_format(($exchangeRate * $productstore->unit_price),2); 
            // Corregí la lógica del porcentaje para ser consistente
            $porcentaje = 'Bs. ' . number_format((($exchangeRate * $productstore->unit_price) * 1.011),2); 
            
            return [
                "id"=> $productstore->id,
                "product_id"=> $productstore->product->code,
                "quantity"=> $productstore->quantity,
                "unit_price"=> $productstore->unit_price,
                "unit_price_bs"=>$unit_price_bs,
                "porcentaje"=>$porcentaje,
                "last_update" => $productstore->last_update, // Usamos diffForHumans
            ];
        }); 
      
    return Inertia::render('Dashboard',[
        'usd'=>$usd,
        'products'=>$products,
        // AHORA PASAMOS LA LISTA COMPLETA DE PRODUCTOS EN TIENDA (RAW)
        'productStores' => $productStoresRaw,
        // Y las listas filtradas
        'productStores15Days' => $productStores15Days,
        'productStores30Days' => $productStores30Days,
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
Route::resource('rsalary_adjustments', SalaryAdjustmentController::class)->middleware(['auth', 'role:1,2']);

// Rutas de "Productos en Bodega". Los roles 1 (aa) y 4 (dd) pueden gestionarlos.
Route::resource('rproducts', ProductController::class)->middleware(['auth', 'role:1,2,4']);

// Rutas de "Historial de compras". Solo los roles 1 (aa) y 4 (dd) pueden ver las compras.
Route::resource('rpurchases', PurchaseController::class)->middleware(['auth', 'role:1,4']);

// Rutas de "Productos en la tienda". Los roles 1 (aa) y 3 (cc) pueden gestionarlos.
Route::resource('rproductstores', ProductStoreController::class)->middleware(['auth', 'role:1,2,3']);

// Rutas para "Ventas". Los roles 1 (aa) y 3 (cc) pueden ver y registrar ventas.
Route::resource('rsales', SaleController::class)->middleware(['auth', 'role:1,2,3']);

// Rutas de ajuste de tipo de cambio. Solo el rol 1 (aa) puede hacerlo.
Route::resource('rusdexchangerates', UsdExchangeRateController::class)->middleware(['auth', 'role:1,2']);

// Estas rutas probablemente estén relacionadas con las ventas, así que las protegemos para los mismos roles.
Route::resource('rsaleitems',SaleItemController::class)->middleware(['auth', 'role:1,2,3']);
Route::resource('rdevolutions', DevolutionController::class)->middleware(['auth', 'role:1,2']);

Route::resource('rsalaries',SalaryController::class)->middleware(['auth', 'role:1']);


// Rutas para Sucursales - solo rol 1
Route::resource('rbranches', BranchController::class)->middleware(['auth', 'role:1']);

// Endpoint para que el usuario autenticado cambie su branch (usado por admin)
Route::post('rusers/switch-branch', [UserController::class, 'switchBranch'])->middleware(['auth'])->name('rusers.switchBranch');


Route::post(
    'rattendance_records/permition', 
    [AttendanceRecordController::class, 'permition']
)->name('rattendance_records.permition');

Route::put(
    'rattendance_records/updateCheckOut', 
    [AttendanceRecordController::class, 'updateCheckOut']
)->name('rattendance_records.updateCheckOut');

Route::resource('rsalariesF',SalaryFController::class);
