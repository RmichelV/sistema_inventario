<?php

// database/seeders/DatabaseSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
// No olvides importar las clases de Seeder que usarás
use Database\Seeders\RoleSeeder;
use Database\Seeders\BranchSeeder;
use Database\Seeders\UsdExchangeRateSeeder;
use Database\Seeders\UserSeeder; // Asumiremos que creaste un UserSeeder

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. **Define el orden de ejecución aquí** usando el método call().

        $this->call([
            RoleSeeder::class,             // Primero
            BranchSeeder::class,           // Segundo
            UsdExchangeRateSeeder::class,  // Tercero
            UserSeeder::class,             // Cuarto (Debe ir después de Roles y Branches si el usuario tiene claves foráneas a ellos)
            // Aquí puedes llamar otros seeders, como AttendanceRecordSeeder si lo creas.
        ]);

        // Opcional: Si necesitas crear el usuario de prueba y los registros de asistencia *después* de los seeders principales:
        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
        // Attendance_record::factory(30)->create(); 
    }

}
