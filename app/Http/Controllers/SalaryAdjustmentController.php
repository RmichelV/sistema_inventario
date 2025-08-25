<?php

namespace App\Http\Controllers;

use App\Models\Salary_adjustment;
use Illuminate\Http\Request;
use function Termwind\render;

//modelos
use App\Models\User;

//librerias
use Inertia\Inertia;
use Illuminate\Support\Facades\Validator;

class SalaryAdjustmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $salaryAdjustments = Salary_adjustment::with('User')->get();
        $users = User::all();
       
        $salaryAdjustmentWithDetails = $salaryAdjustments->map(function ($adjustment) {
            return [
                'id' => $adjustment->id,
                'user_name' => $adjustment->user->name, // Accede al nombre del usuario relacionado
                'salary_adjustment_type' => $adjustment->salary_adjustment_type, // Accede al nombre del tipo de ajuste relacionado
                'amount' => $adjustment->amount,
                'description' => $adjustment->description,
                'date' => $adjustment->date,
            ];
        });
        return Inertia::render('SalaryAdjustment/Index', [
            'salaryAdjustments' => $salaryAdjustmentWithDetails,
            'users' => $users,
            
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $salaryAdjustment = Salary_adjustment::all();
       
        $users = User::all();
        return Inertia::render('SalaryAdjustment/create', [
            'salaryAdjustments' => $salaryAdjustment,
            'users' => $users,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), []);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $salaryAdjustment = new Salary_adjustment();
        $salaryAdjustment->user_id = $request->input('user_id');
        $salaryAdjustment->salary_adjustment_type = $request->input('salary_adjustment_type');
        if($salaryAdjustment->salary_adjustment_type == 'Bonificacion' ){
            $salaryAdjustment->amount = $request->input('amount');
        }
        else if($salaryAdjustment->salary_adjustment_type == 'Descuento' ){
            $salaryAdjustment->amount = -1 * abs($request->input('amount'));
        }
        $salaryAdjustment->description = $request->input('description');
        $salaryAdjustment->date = $request->input('date');
        $salaryAdjustment->save(); 
        return redirect()->route('rsalary_adjustments.index')->with('success','Suceso registrado exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Salary_adjustment $salary_adjustment)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Salary_adjustment $salary_adjustment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Salary_adjustment $salary_adjustment)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Salary_adjustment $salary_adjustment)
    {
        //
    }
}
