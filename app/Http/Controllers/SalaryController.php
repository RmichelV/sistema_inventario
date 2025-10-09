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
        // 1. Cargar usuarios con todas las relaciones necesarias
        $nusers = User::with('role', 'salary_adjustments', 'attendance_records')->get();
        $roles = Role::all();
        
        // --- CONSTANTE PARA CÁLCULO DE NÓMINA (MOVIMIENTO) ---
        // Definida como variable local, disponible para el closure.
        // 30 días * 8 horas/día * 60 minutos/hora = 14,400 minutos mensuales.
        $totalMonthlyMinutes = 18720; 

        $users = $nusers->map(function($user) use ($totalMonthlyMinutes) { // Usamos 'use' para pasar la variable al closure
            
            // --- CÁLCULO DE SALARIO Y AJUSTES ---
            $base_salary = (float) $user->base_salary;
            
            // 2. Calcular la suma total de ajustes (préstamos o bonificaciones)
            $total_adjustment = $user->salary_adjustments->sum(function ($adjustment) {
                return (float) $adjustment->amount; 
            });
            
            // --- CÁLCULO DE TIEMPO TRABAJADO REAL ---
            
            // 5. Sumar todos los minutos trabajados registrados por el usuario
            $total_minutes_worked = (float) $user->attendance_records->sum('minutes_worked');

            // 6. Conversión minutos totales a formato de Horas y Minutos (H:i)
            $hours = floor($total_minutes_worked / 60);
            $minutes = $total_minutes_worked % 60;
            $total_time_formatted = sprintf('%d:%02d', $hours, $minutes);

            
            // --- CÁLCULO DEL PAGO POR MINUTO ---
            
            // a) Determinar el costo por minuto del salario base
            // Usamos $totalMonthlyMinutes pasada por 'use'
            $cost_per_minute = $base_salary / $totalMonthlyMinutes;

            // b) Calcular el salario devengado
            $salario_neto_minutos = $total_minutes_worked * $cost_per_minute;

            
            // --- CÁLCULO DEL SALARIO FINAL A PAGAR ---
            
            // 3. Salario Final = Salario Devengado por Minutos + Ajustes
            $salario_final_calculado = $salario_neto_minutos + $total_adjustment;
            
            // 4. Aplicar el formato
            $final_salary_formateado = number_format($salario_final_calculado, 2, '.', '');
            
            
            // 7. Retornar el array con los nuevos campos
            return [
                'id'            => $user->id,
                'name'          => $user->name,
                'role'          => $user->role->name,
                'base_salary'   => number_format($base_salary, 2, '.', ''),
                
                // NUEVOS CAMPOS CLAVE
                'cost_per_minute' => number_format($cost_per_minute, 4, '.', ''),
                'salario_devengado' => number_format($salario_neto_minutos, 2, '.', ''), 
                
                // Campos de ajuste y final
                'total_adjustment' => number_format($total_adjustment, 2, '.', ''),
                'final_salary'  => $final_salary_formateado,
                
                // Campos de tiempo trabajado
                'total_minutes_worked' => $total_minutes_worked, 
                'total_time_formatted' => $total_time_formatted, 
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
        // 1. Creación del Salario
        $salary = Salary::create([
            'user_id'           => $request->user_id,
            'base_salary'       => $request->base_salary,
            'salary_adjustment' => $request->salary_adjustment,
            'discounts'         => $request->discounts,
            'total_salary'      => $request->total_salary,
            'paydate'           => $request->paydate,
            'user_id_m'         => $request->user_id_m
        ]);

        // 2. Limpieza de Registros Temporales

        // A. Eliminar todos los ajustes de salario para el usuario pagado
        // (Asegúrate de importar el modelo SalaryAdjustment)
        Salary_adjustment::where('user_id', $request->user_id)->delete();

        // B. Eliminar todos los registros de asistencia para el usuario pagado
        // (Asegúrate de importar el modelo AttendanceRecord)
        Attendance_record::where('user_id', $request->user_id)->delete();

        // 3. Redirección
        // Redirigimos a la ruta definida '/rsalariesF' (asumiendo que es la vista de salarios finalizada)
        return redirect('/rsalariesF')->with('success', 'Salario registrado y registros limpiados');
    }
    /**
     * Display the specified resource.
     */
    public function show(salary $salaries)
    {
       
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
