<?php

namespace App\Http\Requests\Employees;

use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // CAMBIA a 'true' para permitir que el request sea autorizado
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
            // El nombre debe ser requerido, string, con un máximo de 255 caracteres
            // y solo permite letras, espacios, comas, puntos y guiones.
            'name' => ['required', 'string', 'max:255', 'regex:/^[\pL\s.,-]+$/u'],
            
            // La dirección debe ser requerida, string y permite letras, números, espacios, etc.
            'address' => ['required', 'string', 'max:255', 'regex:/^[\pL\s\d.,#-]+$/u'],
            
            // // El teléfono es requerido, debe ser numérico y tener entre 8 y 15 dígitos.
            // 'phone' => ['required', 'numeric', 'digits_between:8,15'],
            
            // // role_id debe ser un entero y existir en la tabla 'roles'.
            'role_id' => ['required', 'integer', 'exists:roles,id'],
            
            // // El salario base es requerido, debe ser numérico y puede ser un decimal.
            'base_salary' => ['required', 'numeric','min:500'],
            
            // // La fecha de contratación es requerida y debe ser un formato de fecha válido.
            'hire_date' => ['required', 'date'],
            
            // // MODIFICACIÓN: Se añade la regla regex para el dominio @ewtto.com
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email', 'regex:/@ewtto\.com$/i'],
            
            // // La contraseña es requerida, debe ser confirmada (campo password_confirmation) y tener un mínimo de 8 caracteres.
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'El nombre es obligatorio.',
            'name.max' => 'El nombre no puede exceder los 255 caracteres.',
            'name.regex' => 'El nombre solo puede contener letras, espacios y signos básicos de puntuación.',

            'address.required' => 'La dirección es obligatoria.',
            'address.max' => 'La dirección no puede exceder los 255 caracteres.',
            'address.regex' => 'La dirección contiene caracteres inválidos.',

            'phone.required' => 'El número de teléfono es obligatorio.',
            'phone.numeric' => 'El número de teléfono debe contener solo dígitos.',
            'phone.digits_between' => 'El teléfono debe tener entre 8 y 15 dígitos.',
            
            'role_id.required' => 'El rol del usuario es obligatorio.',
            'role_id.integer' => 'El ID del rol debe ser un número entero.',
            'role_id.exists' => 'El rol seleccionado no es válido.',

            'base_salary.required' => 'El salario base es obligatorio.',
            'base_salary.numeric' => 'El salario base debe ser un valor numérico.',
            'base_salary.min' => 'El salario base debe ser al menos 500.',

            'hire_date.required' => 'La fecha de contratación es obligatoria.',
            'hire_date.date' => 'El formato de la fecha de contratación no es válido.',

            'email.required' => 'El email es obligatorio.',
            'email.email' => 'El email debe tener un formato válido (ej. correo@ewtto.com).',
            'email.max' => 'El email no puede exceder los 255 caracteres.',
            'email.unique' => 'Este email ya se encuentra registrado en el sistema.',
            // NUEVO MENSAJE: Mensaje específico para la regla del dominio
            'email.regex' => 'El email debe pertenecer al dominio @ewtto.com.',

            'password.required' => 'La contraseña es obligatoria.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'La confirmación de la contraseña no coincide.',
        ];
    }
}
