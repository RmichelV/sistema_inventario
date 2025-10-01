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

//request
use App\Http\Requests\Attendance_Record\Attendance_recordStoreRequest;

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
    public function store(Attendance_recordStoreRequest $request)
    {
        
        dd($request->all());
            //  Attendance_record::create([
            //     'user_id' => $request->user_id,
            //     'attendance_status' => $request->attendance_status, // 'Presente'
            //     'attendance_date' => $request->attendance_date,     // Formato 'AAAA-MM-DD'
            //     'check_in_at' => $request->check_in_at,             // Formato 'HH:MM:SS'
            //     'check_out_at' => null, // Puede ser nulo
            //     'minutes_worked' => null, // Puede
            // ]);
            // return redirect()->route('rattendance_records.create')->with('success', 'Registro de asistencia creado exitosamente.');
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
