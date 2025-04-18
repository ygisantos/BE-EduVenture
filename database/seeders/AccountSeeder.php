<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AccountSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('accounts')->insert([
            [
                'email' => 'admin@example.com',
                'password' => Hash::make('password123'),
                'first_name' => 'admin',
                'middle_name' => '',
                'last_name' => 'User',
                'user_role' => 'admin',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'email' => 'teacher@example.com',
                'password' => Hash::make('password123'),
                'first_name' => 'Teacher',
                'middle_name' => '',
                'last_name' => 'User',
                'user_role' => 'teacher',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'email' => 'student@example.com',
                'password' => Hash::make('password123'),
                'first_name' => 'student',
                'middle_name' => '',
                'last_name' => 'User',
                'user_role' => 'student',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
