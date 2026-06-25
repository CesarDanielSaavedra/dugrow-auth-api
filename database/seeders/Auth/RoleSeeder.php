<?php

namespace Database\Seeders\Auth;

use Illuminate\Database\Seeder;
use App\Models\Auth\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        Role::firstOrCreate([
            'name' => 'admin',
        ], [
            'description' => 'Administrador del sistema',
        ]);

        Role::firstOrCreate([
            'name' => 'user',
        ], [
            'description' => 'Usuario común',
        ]);
    }
}
