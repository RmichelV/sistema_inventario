<?php

namespace App\Http\Requests\Attendance_Record;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class Attendance_recordStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Si tienes autenticación con roles, puedes poner aquí las condiciones.
        // Por ahora, asumimos que el usuario autenticado está autorizado.
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [

            'user_id' => [
                'required',
                'integer',
                'exists:users,id',
                
                Rule::unique('attendance_records')->where(fn ($query) => 
                    $query->where('attendance_date', $this->attendance_date)
                ),
            ],
            
            // 2. attendance_status: Requerido y debe ser 'Presente' para esta acción 'store'.
            'attendance_status' => ['required', 'string', 'in:Presente'],
            
            // 3. attendance_date: Requerido, debe ser una fecha real y seguir el formato 'AAAA-MM-DD'.
            'attendance_date' => ['required', 'date', 'date_format:Y-m-d'],
            
            // 4. check_in_at: Requerido (para la entrada) y debe seguir el formato de hora 'HH:MM:SS'.
            'check_in_at' => ['nullable', 'date_format:H:i:s'],
            'check_out_at' => ['nullable', 'date_format:H:i:s'],
            'minutes_worked' => ['nullable', 'integer', 'min:0'],
            
            // 5. minutes_worked y check_out_at no se validan aquí porque son nulos/se calculan en la entrada.
        ];
    }
    
    /**
     * Define los mensajes de error personalizados.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'user_id.unique' => 'El empleado ya tiene un registro de asistencia (Entrada) para el día de hoy.',
            'attendance_status.in' => 'El estado de asistencia solo puede ser "Presente" para esta operación.',
            'attendance_date.date_format' => 'El formato de la fecha debe ser AAAA-MM-DD.',
            'check_in_at.date_format' => 'El formato de la hora de entrada debe ser HH:MM:SS.',
        ];
    }
}
