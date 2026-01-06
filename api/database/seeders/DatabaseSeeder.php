<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $superAdminRole = Role::firstOrCreate(['name' => 'superadmin']);
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $userRole = Role::firstOrCreate(['name' => 'user']);

        User::firstOrCreate(
            ['email' => 'alvaro.ku.dev@gmail.com'],
            [
                'name' => 'Alvaro Super Admin',
                'password' => Hash::make('12345678'),
                'role_id' => $superAdminRole->id,
            ]
        );

        User::firstOrCreate(
            ['email' => 'alvaroku123@gmail.com'],
            [
                'name' => 'Alvaro Admin',
                'password' => Hash::make('12345678'),
                'role_id' => $adminRole->id,
            ]
        );

        User::firstOrCreate(
            ['email' => 'alvaro.18070038@itsmotul.edu.mx'],
            [
                'name' => 'Alvaro User',
                'password' => Hash::make('12345678'),
                'role_id' => $userRole->id,
            ]
        );
    }
}
