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
                'teacher_id' => null,
                'user_role' => 'admin',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'email' => 'teacher@example.com',
                'password' => Hash::make('password123'),
                'teacher_id' => null,
                'first_name' => 'Teacher 1',
                'middle_name' => '',
                'last_name' => 'User 1',
                'user_role' => 'teacher',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'email' => 'teacher2@example.com',
                'password' => Hash::make('password123'),
                'teacher_id' => null,
                'first_name' => 'Teacher 2',
                'middle_name' => '',
                'last_name' => 'User 2',
                'user_role' => 'teacher',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'email' => 'student@example.com',
                'password' => Hash::make('password123'),
                'teacher_id' => 2,
                'first_name' => 'student 0',
                'middle_name' => '',
                'last_name' => 'User 0',
                'user_role' => 'student',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'email' => 'student1@example.com',
                'password' => Hash::make('password123'),
                'teacher_id' => 2,
                'first_name' => 'student 1',
                'middle_name' => '',
                'last_name' => 'User 1',
                'user_role' => 'student',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'email' => 'student2@example.com',
                'password' => Hash::make('password123'),
                'teacher_id' => 2,
                'first_name' => 'student 2',
                'middle_name' => '',
                'last_name' => 'User 2',
                'user_role' => 'student',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'email' => 'student3@example.com',
                'password' => Hash::make('password123'),
                'teacher_id' => 3,
                'first_name' => 'student3',
                'middle_name' => '',
                'last_name' => 'User 3',
                'user_role' => 'student',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
