<?php

namespace App\Http\Controllers;

use App\Models\salary;
use Illuminate\Http\Request;

//librerias necesarias
use Inertia\Inertia;

//modelos
use App\Models\User;
use App\Models\Salary_adjustment;
use App\Models\Attendance_record;
use App\Models\Role;

class SalaryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
public function index()
    {
        $nusers = User::with('role', 'salary_adjustments')->get();
        $roles = Role::all();
        
        $users = $nusers->map(function($user){
            
            // 1. Inicializa el cálculo de salario en 0 (base en 0)
            $salario_inicial = 0.00;
            
            // 2. Calcular la suma total de ajustes (positivo o negativo)
            $total_adjustment = $user->salary_adjustments->sum(function ($adjustment) {
                return (float) $adjustment->amount; 
            });
            
            // 3. El salario final es la suma del inicial (0) + el total de ajustes
            $salario_final_calculado = $salario_inicial + $total_adjustment;
            
            // 4. Aplicar el formato
            $final_salary_formateado = number_format($salario_final_calculado, 2, '.', '');
            
            return [
                'id'            => $user->id,
                'name'          => $user->name,
                'role'          => $user->role->name,
                'base_salary'   => $user->base_salary,
                'total_adjustment' => number_format($total_adjustment, 2, '.', ''),
                'final_salary'  => $final_salary_formateado, // Aquí estará el total de ajustes
            ];
        });

        return Inertia::render('Salaries/Index', [
            'users' => $users,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(salary $salary)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(salary $salary)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, salary $salary)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(salary $salary)
    {
        //
    }
}
