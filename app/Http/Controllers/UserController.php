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
    // Obtiene los usuarios con la relación 'Role'
        $users = User::with("Role")->get();
        
        // Transforma la colección de usuarios para agregar el nuevo campo 'saludos'
        $usersWithGreetings = $users->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'address' => $user->address,
                'phone'=> $user->phone,
                'role' => $user->role->name, 
                'base_salary'=> $user->base_salary,
                'hire_date' => $user->hire_date,
                'email'=> $user->email,
                'saludos' => 'Hola soy ' . $user->name, // Nuevo campo
            ];
        });

        // También obtén los roles para pasarlos a la vista, si los necesitas
        $roles = Role::all();

        // Pasa las colecciones transformadas a la vista de Inertia
        return Inertia::render("Users/Index", [
            'users' => $usersWithGreetings,
            'roles' => $roles,
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

    // Devolver una redirección para que Inertia la procese y recargue la página
    return redirect()->route('rproducts.index')->with('success', 'Sucursal cambiada correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
