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
    $users = User::with(["Role", "salary_adjustments"])->get();
    $roles = Role::all(); 

    $usersWithCalculations = $users->map(function ($user) {
        
        $total_adjustment = $user->salary_adjustments->sum(function ($adjustment) {
            
            return (float) $adjustment->amount; 
            
        });
        
        $baseSalary = (float) $user->base_salary;
        
        // Devolvemos números flotantes puros
        return [
            'id' => $user->id,
            'name' => $user->name,
            'role' => $user->role->name,
            'base_salary' => $baseSalary,
            'hire_date' => $user->hire_date,
            'total_salary_adjustment' => $total_adjustment, 
            
            // La suma sigue funcionando correctamente: 3000 + (-200) = 2800
            'final_salary' => $baseSalary + $total_adjustment, 
        ];
    });

    return Inertia::render("Salaries/Index", [
        'users' => $usersWithCalculations,
        'roles' => $roles,
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
