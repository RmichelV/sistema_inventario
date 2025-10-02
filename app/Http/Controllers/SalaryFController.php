<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;


//librerias
use Inertia\Inertia;
//modelos
use App\Models\Salary;
class SalaryFController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $salaries = Salary::with('user','registeredBy')->get();
        $salaries = $salaries->map(function($salary) {
           
            return [
                'user_id'=>$salary->user->name,
                'base_salary'=>$salary->base_salary,
                'salary_adjustment'=>$salary->salary_adjustment,
                'discounts'=>$salary->discounts,
                'total_salary'=>$salary->total_salary,
                'paydate'=>$salary->paydate,
                'user_id_m'=>$salary->registeredBy->name
            ];
        });
        return Inertia::render('Salaries/show', [
            'salaries' => $salaries,
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
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
