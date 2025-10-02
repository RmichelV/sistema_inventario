<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\Role; // Importar el modelo Role

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // NOTA: Asumimos que el RoleSeeder ya creó roles en la base de datos
        $roleId = Role::inRandomOrder()->first()->id ?? 1;

        return [
            'name' => fake()->name(),
            'address' => fake()->address(), // Campo agregado
            'phone' => fake()->phoneNumber(), // Campo agregado
            'role_id' => $roleId, // Campo agregado, asume que existe el modelo Role
            'base_salary' => fake()->numberBetween(1000, 5000), // Campo agregado
            'hire_date' => fake()->dateTimeBetween('-5 years', 'now')->format('Y-m-d'), // Campo agregado
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
