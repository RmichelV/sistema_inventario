<?php

namespace App\Http\Requests\Attendance_Record;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class Attendance_recordStoreRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Define las reglas de validación.
     */
    public function rules(): array
    {
        // 1. Validar campos obligatorios que vienen en todas las peticiones
        return [
            
            // user_id: Requerido, debe ser entero y existir en la tabla users.
            // IMPORTANTE: Se ELIMINA Rule::unique, delegando la unicidad al 'try...catch' del controlador
            'user_id' => [
                'required',
                'integer',
                'exists:users,id',
            ],
            
            // attendance_status: Requerido y solo permite los tres estados.
            'attendance_status' => ['required', 'string'],
            
            // attendance_date: Requerido, debe ser una fecha real y seguir el formato 'AAAA-MM-DD'.
            'attendance_date' => ['required', 'date', 'date_format:Y-m-d'],
            
            // 2. Validar campos opcionales / condicionales
            
            'check_in_at' => ['nullable', 'date_format:H:i:s'],
            
            // late_minutes: Enviado solo por "Permiso" y "Tarde".
            'late_minutes' => ['nullable', 'integer', 'min:0'], 
            
            // check_out_at y minutes_worked: Siempre null en el registro de entrada.
            'check_out_at' => ['nullable'],
            'minutes_worked' => ['nullable'],
        ];
    }
    
    /**
     * Define los mensajes de error personalizados.
     */
    public function messages(): array
    {
        // El mensaje 'user_id.unique' ya no es necesario aquí.
        return [
            'attendance_status.in' => 'El estado de asistencia debe ser "Presente", "Permiso" o "Tarde".',
            'attendance_date.date_format' => 'El formato de la fecha debe ser AAAA-MM-DD.',
            'check_in_at.date_format' => 'La hora de entrada debe tener el formato HH:MM:SS.',
        ];
    }
}
