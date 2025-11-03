<?php

use App\Http\Controllers\showSalaries;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

//controladores
use App\Http\Controllers\UserController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\ReservationItemController;
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
    // `quantity_in_stock` fue movida a la tabla de inventarios por sucursal (product_branches).
    // Determinar usuario y sucursal actual (usado para filtrar product_stores)
    $user = auth()->user();
    $branchId = $user?->branch_id ?? null;

    // Si el usuario tiene sucursal, limitamos los productos a los que existen en product_branches
    $productIdsInBranch = [];
    if (\Schema::hasColumn('product_branches', 'branch_id') && $branchId) {
        $productIdsInBranch = \App\Models\product_branch::where('branch_id', $branchId)->pluck('product_id')->toArray();
        // Traer solo metadata de productos que pertenecen a la sucursal
        $products = Product::whereIn('id', $productIdsInBranch)->get(['id','name','code']);
    } else {
        // Si no hay branch o en despliegues intermedios, mantener la lista completa
        $products = Product::all(['id','name','code']);
    }

    // --- OBTENER STOCK EN BODEGA (PRODUCT_BRANCHES) ---
    $productBranches = [];
    if (\Schema::hasTable('product_branches') && $branchId) {
        $productBranches = \App\Models\product_branch::where('branch_id', $branchId)->get(['id', 'product_id', 'quantity_in_stock', 'unit_price']);
    }

    // --- 1. PRODUCTOS EN TIENDA (RAW) - NECESARIO PARA LA FUNCIÓN DE BÚSQUEDA DEL DASHBOARD ---
    // Obtenemos los productos en tienda (sin el 'with("product")' para hacerlo más ligero)
    // y filtramos por branch_id si corresponde. También incluimos filas legacy (branch_id IS NULL)
    // sólo si el producto pertenece a la sucursal según product_branches.
    $productStoresQuery = Product_Store::query()->select(['product_id', 'quantity', 'unit_price', 'branch_id']);
    if (\Schema::hasColumn('product_stores', 'branch_id') && $branchId) {
        // $productIdsInBranch ya fue inicializado arriba cuando corresponde
        $productStoresQuery->where(function($q) use ($branchId, $productIdsInBranch) {
            $q->where('branch_id', $branchId)
              ->orWhere(function($q2) use ($productIdsInBranch) {
                    $q2->whereNull('branch_id')
                       ->whereIn('product_id', $productIdsInBranch);
              });
        });
    }
    $productStoresRaw = $productStoresQuery->get();
    
    // --- 2. PRODUCTOS NO ACTUALIZADOS EN LOS ÚLTIMOS 15 DÍAS ---
    $cutoff15Days = Carbon::now()->subDays(15);
    
    // Consulta para productos cuya última actualización fue ANTERIOR a 15 días
    $productinStore15Query = Product_Store::with('product')->where('last_update', '<', $cutoff15Days);
    if (\Schema::hasColumn('product_stores', 'branch_id') && $branchId) {
        // Reutilizamos $productIdsInBranch (si no se inicializó antes, lo obtenemos aquí)
        if (empty($productIdsInBranch)) {
            $productIdsInBranch = \App\Models\product_branch::where('branch_id', $branchId)->pluck('product_id')->toArray();
        }
        $productinStore15Query->where(function($q) use ($branchId, $productIdsInBranch) {
            $q->where('branch_id', $branchId)
              ->orWhere(function($q2) use ($productIdsInBranch) {
                    $q2->whereNull('branch_id')
                       ->whereIn('product_id', $productIdsInBranch);
              });
        });
    }
    $productinStore15 = $productinStore15Query->get();

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
    $productinStore30Query = Product_Store::with('product')->where('last_update', '<', $cutoff30Days);
    if (\Schema::hasColumn('product_stores', 'branch_id') && $branchId) {
        if (empty($productIdsInBranch)) {
            $productIdsInBranch = \App\Models\product_branch::where('branch_id', $branchId)->pluck('product_id')->toArray();
        }
        $productinStore30Query->where(function($q) use ($branchId, $productIdsInBranch) {
            $q->where('branch_id', $branchId)
              ->orWhere(function($q2) use ($productIdsInBranch) {
                    $q2->whereNull('branch_id')
                       ->whereIn('product_id', $productIdsInBranch);
              });
        });
    }
    $productinStore30 = $productinStore30Query->get();
    
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
      
    // Pasar también información de sucursales y usuario actual para el selector
    $user = auth()->user();
    $branches = \App\Models\branch::all();
    $currentBranch = null;
    if ($user && $user->branch_id) {
        $currentBranch = $branches->firstWhere('id', $user->branch_id);
    }

    return Inertia::render('Dashboard',[
        'usd'=>$usd,
        'products'=>$products,
        // AHORA PASAMOS LA LISTA COMPLETA DE PRODUCTOS EN TIENDA (RAW)
        'productStores' => $productStoresRaw,
        // Y las listas filtradas
        'productStores15Days' => $productStores15Days,
        'productStores30Days' => $productStores30Days,
        // AGREGAR PRODUCT_BRANCHES PARA STOCK EN BODEGA
        'productBranches' => $productBranches,
        'branches' => $branches,
        'currentBranch' => $currentBranch,
        'currentUser' => $user,
    ]);
})->middleware(['auth', 'role:1,2,3,4'])->name('dashboard');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';

// Ahora, aplicamos el middleware 'role' a cada recurso.
// Esto protegerá todas las rutas (index, create, store, etc.) del recurso.

// Rutas para "Datos de empleados". Solo el rol 1 (aa) puede administrar usuarios.
Route::resource('rusers', UserController::class)->middleware(['auth', 'role:1']);

// Rutas para "Clientes". Solo el rol 1 (aa) puede administrar clientes.
Route::resource('rcustomers', CustomerController::class)->middleware(['auth', 'role:1']);

// Rutas de "Asistencias". Los roles 1 (aa) y 2 (bb) pueden ver los registros.
Route::resource('rattendance_records', AttendanceRecordController::class)->middleware(['auth', 'role:1,2']);

// Rutas de "Movimientos de salarios". Solo el rol 1 (aa) puede ver y gestionar salarios.
Route::resource('rsalary_adjustments', SalaryAdjustmentController::class)->middleware(['auth', 'role:1,2']);

// Rutas de "Productos en Bodega". Los roles 1 (aa) y 4 (dd) pueden gestionarlos.
Route::resource('rproducts', ProductController::class)->middleware(['auth', 'role:1,2,4']);

// Eliminar registro de inventario en product_branches para la sucursal actual
Route::delete('rproductbranches/{product}', [ProductController::class, 'destroyBranch'])
    ->middleware(['auth', 'role:1,2,4'])
    ->name('rproductbranches.destroy');

// Rutas de "Historial de compras". Solo los roles 1 (aa) y 4 (dd) pueden ver las compras.
Route::resource('rpurchases', PurchaseController::class)->middleware(['auth', 'role:1,4']);

// Rutas de "Productos en la tienda". Los roles 1 (aa) y 3 (cc) pueden gestionarlos.
Route::resource('rproductstores', ProductStoreController::class)->middleware(['auth', 'role:1,2,3']);

// Rutas para "Ventas". Los roles 1 (aa) y 3 (cc) pueden ver y registrar ventas.
Route::resource('rsales', SaleController::class)->middleware(['auth', 'role:1,2,3']);

// Rutas para "Reservaciones". Los roles 1 (aa) y 3 (cc) pueden ver y registrar reservaciones.
Route::resource('rreservations', ReservationController::class)->middleware(['auth', 'role:1,2,3']);
Route::resource('rreservation_items', ReservationItemController::class)->middleware(['auth', 'role:1,2,3']);

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
