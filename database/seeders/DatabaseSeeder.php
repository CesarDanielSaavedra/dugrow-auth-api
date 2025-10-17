<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
    // Ejecuta el seeder de roles de Auth
    $this->call(\Database\Seeders\Auth\RoleSeeder::class);
    // Ejecuta el seeder del usuario admin inicial
    $this->call(\Database\Seeders\Auth\AdminUserSeeder::class);
    }
}
