<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Attendance_record; 
use Carbon\Carbon;

class AttendanceRecordSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // IDs de los 4 usuarios que queremos usar (asumiendo que existen del 1 al 4)
        $userIds = [1, 2, 3, 4]; 
        $records = [];
        $userIdIndex = 0;
        
        // Usaremos 6 días de trabajo para simular 30 registros (6 días * 5 registros/día, o 30 registros en total)
        for ($i = 0; $i < 30; $i++) {
            // Asigna un usuario cíclicamente (1, 2, 3, 4, 1, 2, 3, 4...)
            $userId = $userIds[$userIdIndex % count($userIds)];
            
            // Retrocede la fecha para simular registros en el pasado (hasta 6 días)
            $currentDate = Carbon::today()->subDays(floor($i / count($userIds)));

            $status = 'Presente';
            $checkIn = null;
            $checkOut = null;
            $minutesWorked = null;

            // --- Lógica de Variación de Registros ---

            if ($i % 6 === 0) {
                // Caso 1: Registro de Permiso (Sin check_in/out, con minutos trabajados)
                $status = 'Permiso';
                $minutesWorked = 480; // 8 horas
            } elseif ($i % 5 === 0) {
                // Caso 2: Entrada Tarde, Salida Completa (9 horas trabajadas)
                $status = 'Tarde';
                $checkIn = Carbon::parse($currentDate->format('Y-m-d') . ' 09:30:00');
                $checkOut = Carbon::parse($currentDate->format('Y-m-d') . ' 18:30:00');
                $minutesWorked = ($checkOut->diffInMinutes($checkIn)*-1);
            } elseif ($i % 7 === 0) {
                // Caso 3: Entrada normal, pero sin Salida (Registro Abierto)
                $status = 'Presente';
                $checkIn = Carbon::parse($currentDate->format('Y-m-d') . ' 08:00:00');
                $minutesWorked = 0;
                $checkOut = null;
            } else {
                // Caso 4: Entrada y Salida normal (8 horas exactas)
                $status = 'Presente';
                $checkIn = Carbon::parse($currentDate->format('Y-m-d') . ' 08:00:00');
                $checkOut = Carbon::parse($currentDate->format('Y-m-d') . ' 16:00:00');
                $minutesWorked = ($checkOut->diffInMinutes($checkIn)*-1);
            }
            
            // --- Inserción en el array ---
            $records[] = [
                'user_id' => $userId,
                'attendance_status' => $status,
                'attendance_date' => $currentDate->format('Y-m-d'),
                // Formateamos las horas a string para la DB
                'check_in_at' => $checkIn ? $checkIn->format('H:i:s') : null,
                'check_out_at' => $checkOut ? $checkOut->format('H:i:s') : null,
                'minutes_worked' => $minutesWorked,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $userIdIndex++;
        }

        // 4. Insertar todos los registros
        Attendance_record::insert($records);
    }
}


