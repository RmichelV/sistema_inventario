<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\branch as Branch;
use App\Models\User;
use App\Models\product_branch as ProductBranch;
use App\Models\Product_store;

class BranchController extends Controller
{
    public function index()
    {
        $branches = Branch::all();
        return Inertia::render('Branches/Index', [
            'branches' => $branches,
        ]);
    }

    public function create()
    {
        return Inertia::render('Branches/Create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
        ]);

        Branch::create([
            'name' => $request->name,
            'address' => $request->address,
        ]);

        return redirect()->route('rbranches.index')->with('success', 'Sucursal creada');
    }

    public function edit($id)
    {
        $branch = Branch::findOrFail($id);
        return Inertia::render('Branches/Edit', [
            'branch' => $branch,
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
        ]);

        $branch = Branch::findOrFail($id);
        $branch->name = $request->name;
        $branch->address = $request->address;
        $branch->save();

        return redirect()->route('rbranches.index')->with('success', 'Sucursal actualizada');
    }

    public function destroy(Request $request, $id)
    {
        $branch = Branch::findOrFail($id);

        $authUser = auth()->user();

        // Detectar si la petición viene desde fetch/ajax (el frontend usa fetch con _method=DELETE)
        $isFetchDelete = $request->input('_method') && strtolower($request->input('_method')) === 'delete';
        $expectsJson = $request->wantsJson() || $request->ajax() || $isFetchDelete;

        Log::info('Branch destroy request', ['branch_id' => $id, 'auth_user_id' => $authUser->id ?? null, 'auth_user_branch' => $authUser->branch_id ?? null, 'is_fetch_delete' => $isFetchDelete]);

        // No permitir eliminar la sucursal si el usuario autenticado pertenece a ella
        if ($authUser && $authUser->branch_id && (int)$authUser->branch_id === (int)$id) {
            $msg = 'No puedes eliminar la sucursal asignada al usuario autenticado.';
            Log::warning('Branch deletion blocked: user belongs to branch', ['user_id' => $authUser->id ?? null, 'branch_id' => $id]);
            if ($expectsJson) {
                return response()->json(['success' => false, 'message' => $msg], 403);
            }
            return redirect()->back()->with('error', $msg);
        }

        DB::beginTransaction();
        try {
            // Eliminar empleados (usuarios) asociados a la sucursal
            User::where('branch_id', $id)->delete();

            // Eliminar product_branches para la sucursal
            ProductBranch::where('branch_id', $id)->delete();

            // Eliminar product_stores para la sucursal (si existe columna branch_id)
            if (\Schema::hasTable('product_stores') && \Schema::hasColumn('product_stores', 'branch_id')) {
                Product_store::where('branch_id', $id)->delete();
            }

            // Finalmente eliminar la sucursal
            $branch->delete();

            DB::commit();
            $msg = 'Sucursal eliminada';
            Log::info('Branch deleted', ['branch_id' => $id]);
            if ($expectsJson) {
                return response()->json(['success' => true, 'message' => $msg], 200);
            }
            return redirect()->route('rbranches.index')->with('success', $msg);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error eliminando sucursal: ' . $e->getMessage(), ['branch_id' => $id]);
            $msg = 'Ocurrió un error al eliminar la sucursal.';
            if ($expectsJson) {
                return response()->json(['success' => false, 'message' => $msg, 'error' => $e->getMessage()], 500);
            }
            return redirect()->back()->with('error', $msg);
        }
    }
}

