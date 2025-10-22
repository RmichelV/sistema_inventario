<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\branch as Branch;

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

    public function destroy($id)
    {
        $branch = Branch::findOrFail($id);
        $branch->delete();
        return redirect()->route('rbranches.index')->with('success', 'Sucursal eliminada');
    }
}

