<?php

namespace Database\Seeders;

use App\Models\Attendance_record; 
use App\Models\User; 
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // --- ORDEN DE EJECUCIÓN CRÍTICO ---
        
        // 1. Roles (Necesario para que el user_factory encuentre role_id)
        $this->call([
            RoleSeeder::class, 
        ]);
        
        // 2. Usuarios (Usarán los roles que acabamos de crear)
        // Creamos 5 usuarios, cada uno con un role_id válido.
        User::factory(5)->create(); 
        
        // 3. Registros de Asistencia (Usarán los user_id que acabamos de crear)
        // Genera 30 registros variados de entrada, salida y permisos.
        Attendance_record::factory(30)->create(); 
        
        // Ya no necesitas los comandos de fábrica aquí.
    }}