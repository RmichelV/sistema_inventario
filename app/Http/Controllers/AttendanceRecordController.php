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
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

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
        // Los datos ya están validados y disponibles en $request->validated()
        $data = $request->validated();
        $status = $data['attendance_status'];
        
        // 1. Inicializar la hora de entrada (check_in_at)
        $checkInTime = null;

        // 2. Lógica condicional: Si es Presente o Tarde, se registra la hora actual del servidor.
        if (in_array($status, ['Presente', 'Tarde'])) {
            // Usamos la hora actual del servidor para mayor precisión
            // Si la hora se envía desde el frontend (como en 'Presente'), deberías usar $data['check_in_at']
            // Pero si quieres la hora del servidor, esta línea es correcta:
            $checkInTime = now()->format('H:i:s');
        }

        // 3. Preparar el array final para la creación del registro
        $finalData = [
            'user_id' => $data['user_id'],
            'attendance_status' => $status,
            'attendance_date' => $data['attendance_date'],
            'check_in_at' => $checkInTime,
            
            // Los campos de salida y minutos trabajados siempre son nulos en la entrada
            'check_out_at' => null, 
            'minutes_worked' => null,
            
            // Omitimos 'late_minutes' ya que no está en la definición de la tabla.
        ];

        try {
            // 4. Crear el registro
            Attendance_record::create($finalData);
            
            // Mensaje de éxito: usamos back() para quedarnos en la misma página
            return back()->with('success', 'Asistencia Registrada exitosamente.');
            
        } catch (QueryException $e) {
            // 5. Manejar el error de restricción única de la base de datos (SQLSTATE 23000)
            if ($e->getCode() === '23000') {
                // Mensaje de error personalizado para el usuario
                // IMPORTANTE: Eliminamos ->withInput() para evitar posibles conflictos con Inertia/Vue
                return back()->with('error', 'El empleado ya tiene un registro de asistencia para esta fecha.');
            }
            
            // Registrar y devolver otros errores inesperados
            Log::error("Error al crear registro de asistencia: " . $e->getMessage());
            // IMPORTANTE: Eliminamos ->withInput()
            return back()->with('error', 'Ocurrió un error inesperado al guardar el registro.');
        }
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
