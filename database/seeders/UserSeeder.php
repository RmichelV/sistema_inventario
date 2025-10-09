<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\User;
class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::insert([[
            'name'=>'Usuario1',
            'address'=>'Calle 123 #456',
            'phone'=>"78827517",
            'role_id'=>1,
            'base_salary'=>2000,
            'hire_date'=>'2025.08.08',
            'email'=>'usuario1@ewtto.com',
            'password'=>bcrypt('usuario1')
        ]]
        );
    }
}
