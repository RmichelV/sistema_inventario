<?php

namespace App\Http\Controllers;

use App\Models\Attendance_record;
use Illuminate\Http\Request;


//Modelos 
use App\Models\User;
use App\Models\Role;

//librerias
use Inertia\Inertia;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule; // ¡Necesitas esta importación!
use Carbon\Carbon; // ¡Necesitas esta importación!

class AttendanceRecordController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $attendance_records = Attendance_record::with('User')->get();
        $attendance_recordsList = $attendance_records->map(function ($attendance_record) {
            return [
                'id' => $attendance_record->id,
                'user' => $attendance_record->user->name,
                'attendance_status' => $attendance_record->attendance_status,
                'attendance_date' => $attendance_record->attendance_date,
                'check_in_at' => $attendance_record->check_in_at,
                'check_out_at' => $attendance_record->check_out_at,
                'minutes_late' => $attendance_record->late_minutes, 
            ];
        });
        return inertia('AttendanceRecords/Index', ['attendanceRecords'=> $attendance_recordsList]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
// Obtiene los usuarios con la relación 'Role'
        $users = User::with("Role")->get();
        
        // Transforma la colección de usuarios para agregar el nuevo campo 'saludos'
        $userList = $users->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'asistencia_registrada'=>false
            ];
        });

        // También obtén los roles para pasarlos a la vista, si los necesitas
        $roles = Role::all();

        // Pasa las colecciones transformadas a la vista de Inertia
        return Inertia::render("AttendanceRecords/create", [
            'users' => $userList,
            'roles' => $roles,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
        public function store(Request $request)
    {
        // 1. Definir la fecha de hoy para la validación
        $todayDate = Carbon::today()->toDateString();

        // 2. Definir y ejecutar la validación con la regla de unicidad
        $validator = Validator::make($request->all(), [
            'user_id' => [
                'required',
                'exists:users,id',
                // REGLA DE UNICIDAD COMPUESTA:
                // Verifica que no exista otro registro en 'attendance_records'
                // donde el 'user_id' sea el de la solicitud Y el 'attendance_date' sea hoy.
                Rule::unique('attendance_records')->where(function ($query) use ($request, $todayDate) {
                    return $query->where('user_id', $request->user_id)
                                 ->where('attendance_date', $todayDate);
                }),
            ],
            'attendace_status' => 'required|string|in:Presente,Tarde,Ausente,Permiso',
            'late_minutes' => 'nullable|integer', // Asegúrate de validar este campo si existe en el form
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // 3. Si la validación pasa (no hay registro de hoy), se procede a guardar.

        $attendanceRecord = new Attendance_record();
        $attendanceRecord->user_id = $request->user_id;
        $attendanceRecord->attendance_status = $request->attendace_status;
        
        // Usamos la variable $todayDate ya definida, o Carbon::now()->toDateString()
        $attendanceRecord->attendance_date = $todayDate; 

        if ($request->attendace_status === 'Presente') {
            $attendanceRecord->check_in_at = now();
        } elseif ($request->attendace_status === 'Tarde') {
            $attendanceRecord->check_in_at = now();
            // Asegúrate de que $request->late_minutes exista si el estado es Tarde
            $attendanceRecord->late_minutes = $request->late_minutes;
            
        } elseif ($request->input('attendace_status') === 'Ausente') {
            // No se necesita hacer nada especial para ausentes
        }

        $attendanceRecord->save();

        return redirect()->route('rattendance_records.create')->with('success', 'Registro de asistencia creado exitosamente.');
    }


    /**
     * Display the specified resource.
     */
    public function show(Attendance_record $attendance_record)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Attendance_record $attendance_record)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Attendance_record $attendance_record)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Attendance_record $attendance_record)
    {
        //
    }
}
