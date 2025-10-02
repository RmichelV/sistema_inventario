<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Attendance_record;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Attendance_record>
 */
class AttendanceRecordFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Obtener un User ID existente de forma aleatoria.
        $userId = User::inRandomOrder()->first()->id;
        
        // Generar una fecha de asistencia aleatoria en los últimos 30 días.
        $attendanceDate = $this->faker->dateTimeBetween('-30 days', 'now')->format('Y-m-d');

        // Elegir aleatoriamente el estado (Presente, Tarde, Permiso)
        $status = $this->faker->randomElement(['Presente', 'Tarde', 'Permiso', 'Presente', 'Tarde']); // Ponderamos más Presente/Tarde

        $checkInAt = null;
        $checkOutAt = null;
        $minutesWorked = null;
        
        // Asumimos la hora de entrada de referencia (10:00:00)
        $expectedCheckIn = Carbon::parse($attendanceDate . ' 10:00:00');
        
        if ($status === 'Permiso') {
            // El permiso no tiene hora de entrada/salida
            $minutesWorked = $this->faker->numberBetween(60, 480); // 1 a 8 horas de permiso
            
        } else {
            // Es Presente o Tarde, por lo que debe tener check_in
            
            if ($status === 'Presente') {
                // Genera una hora de entrada entre 9:30:00 y 10:00:00
                $checkInTime = $expectedCheckIn->copy()->subMinutes($this->faker->numberBetween(0, 30));
                
            } else { // Tarde
                // Genera una hora de entrada entre 10:01:00 y 11:30:00
                $checkInTime = $expectedCheckIn->copy()->addMinutes($this->faker->numberBetween(1, 90));
            }

            $checkInAt = $checkInTime->format('H:i:s');
            
            // Opcional: El 70% de las veces generamos una hora de salida para registros cerrados
            if ($this->faker->boolean(70)) {
                // Genera una hora de salida 5 a 9 horas después de la entrada
                $checkOutTime = $checkInTime->copy()->addHours($this->faker->numberBetween(5, 9));
                
                $checkOutAt = $checkOutTime->format('H:i:s');
                
                // Calcula los minutos trabajados para el registro cerrado
                $minutesWorked = $checkOutTime->diffInMinutes($checkInTime);
            }
        }

        return [
            'user_id' => $userId,
            'attendance_status' => $status,
            'attendance_date' => $attendanceDate,
            'check_in_at' => $checkInAt,
            'check_out_at' => $checkOutAt,
            'minutes_worked' => $minutesWorked,
        ];
    }
    
    /**
     * Configuración para asegurar que no se creen duplicados (por la restricción UNIQUE).
     * @return $this
     */
    public function configure()
    {
        return $this->afterMaking(function (Attendance_record $record) {
            // Lógica para asegurar unicidad: si ya existe un registro con user_id y attendance_date, regenerar
            while (Attendance_record::where('user_id', $record->user_id)
                                    ->where('attendance_date', $record->attendance_date)
                                    ->exists()) {
                // Regenera la fecha si ya existe una entrada para ese usuario en ese día.
                $record->attendance_date = $this->faker->dateTimeBetween('-30 days', 'now')->format('Y-m-d');
            }
        });
    }
}
