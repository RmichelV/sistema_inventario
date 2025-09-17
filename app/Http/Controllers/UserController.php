<?php

namespace App\Http\Controllers;

use GuzzleHttp\Promise\Create;
use Illuminate\Http\Request;

//modelos 
use App\Models\User;
use App\Models\Role;

//librerias
use Inertia\Inertia;
use Illuminate\Support\Facades\Validator;
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
        return Inertia::render('Users/create',[
            'roles' => $roles
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = User::create([
            'name' => $request->name,
            'address' => $request->address,
            'phone' => $request->phone,
            'role_id' => $request->role_id,
            'base_salary' => $request->base_salary,
            'hire_date' => $request->hire_date,
            'email' => $request->email,
            'password' => bcrypt($request->password), // Asegúrate de hashear la contraseña
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
        return Inertia::render('Users/edit', [
            'user'=> $user,
            'roles' => $roles
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
        $user->base_salary = $request->input('base_salary');
        $user->email = $request->input('email');
        if ($request->filled('password')) {
            $user->password = bcrypt($request->input('password')); 
        }
        $user->save();

        return redirect()->route('rusers.index')->with('success','¡Actualización Exitosa!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
