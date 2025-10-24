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
        // Usuario autenticado y sucursal
        $authUser = auth()->user();
        $branchId = $authUser->branch_id ?? null;

        // Si no hay sucursal asignada, devolver colección vacía
        if (!$branchId) {
            $attendance_records = collect([]);
        } else {
            // Filtrar registros por la sucursal del usuario (mediante la relación user)
            $attendance_records = Attendance_record::with('User')
                ->whereHas('user', function ($q) use ($branchId) {
                    $q->where('branch_id', $branchId);
                })->get();
        }

        $attendance_recordsList = $attendance_records->map(function ($attendance_record) {
            return [
                'id' => $attendance_record->id,
                'user' => $attendance_record->user->name ?? null,
                'attendance_status' => $attendance_record->attendance_status,
                'attendance_date' => $attendance_record->attendance_date,
                'check_in_at' => $attendance_record->check_in_at,
                'check_out_at' => $attendance_record->check_out_at,
                'minutes_late' => $attendance_record->late_minutes ?? null, 
            ];
        });

        $branches = \App\Models\branch::all();
        $currentBranch = null;
        if ($authUser && $authUser->branch_id) {
            $currentBranch = $branches->firstWhere('id', $authUser->branch_id);
        }

        return inertia('AttendanceRecords/Index', ['attendanceRecords'=> $attendance_recordsList, 'branches' => $branches, 'currentBranch' => $currentBranch, 'currentUser' => $authUser]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Usuario autenticado y su sucursal
        $authUser = auth()->user();
        $branchId = $authUser->branch_id ?? null;

        // Si no hay sucursal asignada devolvemos colección vacía (se podrá asignar mediante el selector)
        if ($branchId) {
            $users = User::with('Role')->where('branch_id', $branchId)->get();
        } else {
            $users = collect([]);
        }

        // Transforma la colección de usuarios para la vista
        $userList = $users->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'asistencia_registrada' => false,
            ];
        });

    // Obtener sucursales y currentBranch para el selector en frontend
    $branches = \App\Models\branch::all();
        $currentBranch = null;
        if ($authUser && $authUser->branch_id) {
            $currentBranch = $branches->firstWhere('id', $authUser->branch_id);
        }

        // Pasa las colecciones transformadas a la vista de Inertia
        return Inertia::render('AttendanceRecords/create', [
            'users' => $userList,
            'roles' => Role::all(),
            'branches' => $branches,
            'currentBranch' => $currentBranch,
            'currentUser' => $authUser,
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
    public function show(Attendance_recordStoreRequest $request)
    {
        dd($request->all());
          // Los datos ya están validados y disponibles en $request->validated()
        $data = $request->validated();
        $status = $data['attendance_status'];
        
        // 1. Inicializar la hora de entrada (check_in_at)
        $checkInTime = null;

        // 2. Lógica condicional: Si es Presente o Tarde, se registra la hora actual del servidor.
        if (in_array($status, ['Permiso'])) {
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
            'check_in_at' => null,
            'check_out_at' => null, 
            'minutes_worked' => $data['minutes_worked'],
        ];

        try {
            // 4. Crear el registro
            Attendance_record::create($finalData);
            
            // Mensaje de éxito: usamos back() para quedarnos en la misma página
            return back()->with('success', 'Permiso Registrado exitosamente.');
            
        } catch (QueryException $e) {
            // 5. Manejar el error de restricción única de la base de datos (SQLSTATE 23000)
            if ($e->getCode() === '23000') {
                // Mensaje de error personalizado para el usuario
                // IMPORTANTE: Eliminamos ->withInput() para evitar posibles conflictos con Inertia/Vue
                return back()->with('error', 'El empleado ya tiene un registro de permiso para esta fecha.');
            }
            
            // Registrar y devolver otros errores inesperados
            Log::error("Error al crear registro de asistencia: " . $e->getMessage());
            // IMPORTANTE: Eliminamos ->withInput()
            return back()->with('error', 'Ocurrió un error inesperado al guardar el registro.');
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Attendance_record $attendance_record)
    {
        //
    }

   public function update(Request $request, string $id)
    {
        // El $id recibido es el user_id enviado por Vue, usado para la URL de recurso.
        $userId = $id;

        Log::info('Intentando registrar Salida (update_checkout_trick - UPDATE).', ['user_id_from_url' => $userId, 'request_data' => $request->all()]);

        // 1. Validación de datos mínimos requeridos
        $data = $request->validate([
            'attendance_date' => ['required', 'date_format:Y-m-d'],
            'check_out_at' => ['required', 'date_format:H:i:s'],
        ]);

        try {
            // 2. Buscar el registro de entrada abierto para el usuario y la fecha
            // El registro debe tener check_out_at NULL (aún abierto)
            $record = Attendance_record::where('user_id', $userId)
                ->where('attendance_date', $data['attendance_date'])
                ->whereNull('check_out_at')
                ->first();

            // 3. Manejar caso: No se encontró un registro de entrada
            if (!$record) {
                Log::info('Fallo de Checkout: No se encontró registro activo.', ['user_id' => $userId, 'date' => $data['attendance_date']]);
                return back()->with('error', 'No se encontró un registro de entrada activo para este empleado hoy.');
            }

            // 4. Manejar caso: El registro es de "Permiso" (no debe registrar salida)
            if ($record->attendance_status === 'Permiso') {
                Log::warning('Fallo de Checkout: El registro es Permiso y se bloquea la salida.', ['user_id' => $userId]);
                return back()->with('error', 'El registro actual es un **Permiso** y no requiere hora de salida.');
            }
            
            // 5. Preparar horas para el cálculo
            $checkIn = Carbon::parse($record->attendance_date . ' ' . $record->check_in_at);
            $checkOut = Carbon::parse($data['attendance_date'] . ' ' . $data['check_out_at']);

            // 6. Calcular minutos trabajados
            $minutesWorked = ($checkOut->diffInMinutes($checkIn)*-1);
            
            // 7. Actualizar el registro
            $record->update([
                'check_out_at' => $data['check_out_at'],
                'minutes_worked' => $minutesWorked,
            ]);

            Log::info('Salida registrada y minutos calculados exitosamente.', ['user_id' => $userId, 'minutes' => $minutesWorked]);
            return back()->with('success', 'Salida registrada y minutos calculados exitosamente.');

        } catch (\Exception $e) {
            // Manejo de otros errores
            Log::error("Error al registrar salida: " . $e->getMessage(), ['user_id' => $userId]);
            return back()->with('error', 'Ocurrió un error inesperado al guardar la salida.');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function updateCheckOut(Request $request)
    {
     Log::info('Intentando registrar Salida (updateCheckOut)...', ['request_data' => $request->all()]);

     // 1. Validación de datos mínimos requeridos
        $data = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'attendance_date' => ['required', 'date_format:Y-m-d'],
            'check_out_at' => ['required', 'date_format:H:i:s'],
        ]);

        try {
            // 2. Buscar el registro de entrada abierto para el usuario y la fecha
            // El registro debe tener check_out_at NULL (aún abierto)
            $record = Attendance_record::where('user_id', $data['user_id'])
                ->where('attendance_date', $data['attendance_date'])
                ->whereNull('check_out_at')
                ->first();

            // 3. Manejar caso: No se encontró un registro de entrada
            if (!$record) {
                Log::info('Fallo de Checkout: No se encontró registro activo.', ['user_id' => $data['user_id'], 'date' => $data['attendance_date']]);
                return back()->with('error', 'No se encontró un registro de entrada activo para este empleado hoy.');
            }

            // 4. Manejar caso: El registro es de "Permiso" (no debe registrar salida)
            if ($record->attendance_status === 'Permiso') {
                Log::warning('Fallo de Checkout: El registro es Permiso y se bloquea la salida.', ['user_id' => $data['user_id']]);
                return back()->with('error', 'El registro actual es un **Permiso** y no requiere hora de salida.');
            }
            
            // 5. Preparar horas para el cálculo
            $checkIn = Carbon::parse($record->attendance_date . ' ' . $record->check_in_at);
            $checkOut = Carbon::parse($data['attendance_date'] . ' ' . $data['check_out_at']);

            // 6. Calcular minutos trabajados
            // Usamos diffInMinutes() para obtener la diferencia total
            $minutesWorked = $checkOut->diffInMinutes($checkIn);
            
            // 7. Actualizar el registro
            $record->update([
                'check_out_at' => $data['check_out_at'],
                'minutes_worked' => $minutesWorked,
            ]);

            Log::info('Salida registrada y minutos calculados exitosamente.', ['user_id' => $data['user_id'], 'minutes' => $minutesWorked]);
            return back()->with('success', 'Salida registrada y minutos calculados exitosamente.');

        } catch (\Exception $e) {
            // Manejo de otros errores
            Log::error("Error al registrar salida: " . $e->getMessage(), ['user_id' => $data['user_id']]);
            return back()->with('error', 'Ocurrió un error inesperado al guardar la salida.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Attendance_record $attendance_record)
    {
        //
    }

    function permition(Attendance_recordStoreRequest $request)
        {
            
              // Los datos ya están validados y disponibles en $request->validated()
            $data = $request->validated();
            $status = $data['attendance_status'];
            
            // 1. Inicializar la hora de entrada (check_in_at)
            $checkInTime = null;
    
            // 2. Lógica condicional: Si es Presente o Tarde, se registra la hora actual del servidor.
            if (in_array($status, ['Permiso'])) {
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
                'check_in_at' => null,
                'check_out_at' => null, 
                'minutes_worked' => $data['minutes_worked'],
            ];
    
            try {
                // 4. Crear el registro
                Attendance_record::create($finalData);
                
                // Mensaje de éxito: usamos back() para quedarnos en la misma página
                return back()->with('success', 'Permiso Registrado exitosamente.');
                
            } catch (QueryException $e) {
                // 5. Manejar el error de restricción única de la base de datos (SQLSTATE 23000)
                if ($e->getCode() === '23000') {
                    // Mensaje de error personalizado para el usuario
                    // IMPORTANTE: Eliminamos ->withInput() para evitar posibles conflictos con Inertia/Vue
                    return back()->with('error', 'El empleado ya tiene un registro de permiso para esta fecha.');
                }
                
                // Registrar y devolver otros errores inesperados
                Log::error("Error al crear registro de asistencia: " . $e->getMessage());
                // IMPORTANTE: Eliminamos ->withInput()
                return back()->with('error', 'Ocurrió un error inesperado al guardar el registro.');
            }
    }
}