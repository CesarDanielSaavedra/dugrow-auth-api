<?php

namespace Database\Seeders\Auth;

use Illuminate\Database\Seeder;
use App\Models\Auth\User;
use App\Models\Auth\Role;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = Role::where('name', 'admin')->first();

        User::firstOrCreate(
            [
                'email' => 'admin@dugrow.com',
            ],
            [
                'name' => 'Muke Admin',
                'password' => Hash::make('dugrow123'),
                'role_id' => $adminRole ? $adminRole->id : null,
                'company_id' => 1,
                'email_verified_at' => now(),
            ]
        );
    }
}
