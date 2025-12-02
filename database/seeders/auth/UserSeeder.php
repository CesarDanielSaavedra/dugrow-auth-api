<?php

namespace Database\Seeders\Auth;

use Illuminate\Database\Seeder;
use App\Models\Auth\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Usuario admin
        User::firstOrCreate([
            'email' => 'admin@dugrow.com',
        ], [
            'name' => 'Admin Dugrow',
            'password' => Hash::make('Password123!'),
            'company_id' => 1,
            'role_id' => 1, // admin
        ]);

        // Usuario común
        User::firstOrCreate([
            'email' => 'usuario@dugrow.com',
        ], [
            'name' => 'Usuario Comun',
            'password' => Hash::make('Password123!'),
            'company_id' => 1,
            'role_id' => 2, // user
        ]);
    }
}
