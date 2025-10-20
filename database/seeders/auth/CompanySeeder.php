<?php

namespace Database\Seeders\Auth;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Auth\Company;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Company::firstOrCreate(
            ['id' => 1],
            [
                'name' => 'DU GROW',
                'cuit' => '20304050607',
                'email' => 'info@dugrow.com',
                'phone' => '123456789',
                'address' => 'Dirección central DU GROW',
            ]
        );
    }
}
