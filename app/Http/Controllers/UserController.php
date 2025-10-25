<?php

namespace App\Http\Controllers;

use GuzzleHttp\Promise\Create;
use Illuminate\Http\Request;

//modelos 
use App\Models\User;
use App\Models\Role;
use App\Models\branch as Branch;

//librerias
use Inertia\Inertia;
use Illuminate\Support\Facades\Validator;

//request 
use App\Http\Requests\Employees\UserRequest;
class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    // public function index()
    // {
    //     $users = User::with("Role")->get();
    
    //     $roles = Role::all();
    //     return Inertia::render("Users/Index", compact("users","roles"));
    // }
    public function index()
    {
        // Usuario autenticado
        $authUser = auth()->user();
        $branchId = $authUser->branch_id ?? null;

        // Filtrar siempre por branch_id: cada usuario pertenece a una sucursal.
        // Si el usuario autenticado no tiene branch asignada, devolvemos colección vacía.
        if ($branchId) {
            $users = User::with('Role')->where('branch_id', $branchId)->get();
        } else {
            $users = collect([]);
        }

        // Transforma la colección de usuarios para agregar el nuevo campo 'saludos'
        $usersWithGreetings = collect($users)->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'address' => $user->address,
                'phone'=> $user->phone,
                'role' => $user->role->name ?? null, 
                'base_salary'=> $user->base_salary,
                'hire_date' => $user->hire_date,
                'email'=> $user->email,
                'saludos' => 'Hola soy ' . $user->name,
            ];
        });

        // También obtén los roles y sucursales para pasarlos a la vista
        $roles = Role::all();
        $branches = Branch::all();
        $currentBranch = null;
        if ($authUser && $authUser->branch_id) {
            $currentBranch = $branches->firstWhere('id', $authUser->branch_id);
        }

        // Pasa las colecciones transformadas a la vista de Inertia
        return Inertia::render('Users/Index', [
            'users' => $usersWithGreetings,
            'roles' => $roles,
            'branches' => $branches,
            'currentBranch' => $currentBranch,
            'currentUser' => $authUser,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $roles = Role::all();
        $branches = Branch::all();
        return Inertia::render('Users/create',[
            'roles' => $roles,
            'branches' => $branches,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(UserRequest $request)
    {
        // Usar los datos validados para evitar accesos a propiedades mágicas que los analizadores marcan como indefinidas
        $data = $request->validated();
        try {
            $user = User::create([
                'name' => $data['name'],
                'address' => $data['address'],
                'phone' => $data['phone'] ?? null,
                'role_id' => $data['role_id'],
                'branch_id' => $data['branch_id'] ?? null,
                'base_salary' => $data['base_salary'],
                'hire_date' => $data['hire_date'],
                'email' => $data['email'],
                'password' => bcrypt($data['password']), // Asegúrate de hashear la contraseña
            ]);

            return redirect()->route('rusers.index')->with('success','!Registro Exitoso¡');
        } catch (\Exception $e) {
            // Devolver mensaje de error para que el frontend (Inertia) lo muestre en flash
            return redirect()->back()->withInput()->with('error', 'Error al crear usuario: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $user = User::findOrFail($id);
        $roles = Role::all();
        $branches = Branch::all();
        return Inertia::render('Users/edit', [
            'user'=> $user,
            'roles' => $roles,
            'branches' => $branches,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = User::findOrFail($id);
        $user->name = $request->input('name');
        $user->address = $request->input('address');
        $user->phone = $request->input('phone');
        $user->role_id = $request->input('role_id');
        $user->branch_id = $request->input('branch_id');
        $user->base_salary = $request->input('base_salary');
        $user->email = $request->input('email');
        if ($request->filled('password')) {
            $user->password = bcrypt($request->input('password')); 
        }
        $user->save();

        return redirect()->route('rusers.index')->with('success','¡Actualización Exitosa!');
    }

    /**
     * Switch the branch of the authenticated user.
     */
    public function switchBranch(Request $request)
    {
        $request->validate([
            'branch_id' => 'required|exists:branches,id',
        ]);

        $user = auth()->user();
        $user->branch_id = $request->input('branch_id');
        $user->save();

        // No enviamos flash messages aquí para evitar que el front muestre SweetAlert
        // al cambiar de sucursal. La UI seguirá recargando/visitando para aplicar la nueva sucursal.
        return redirect()->back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // Buscar y eliminar el usuario
        $user = User::find($id);
        if (!$user) {
            return redirect()->back()->with('error', 'Usuario no encontrado.');
        }

        // Evitar que un administrador se elimine a sí mismo accidentalmente
        $authUser = auth()->user();
        if ($authUser && $authUser->id == $user->id) {
            return redirect()->back()->with('error', 'No puedes eliminar tu propio usuario.');
        }

        try {
            $user->delete();
            return redirect()->route('rusers.index')->with('success', 'Usuario eliminado.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al eliminar el usuario.');
        }
    }
}
