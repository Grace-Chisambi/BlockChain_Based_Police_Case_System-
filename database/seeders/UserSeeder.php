<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\User; // If you have a User model

class UserSeeder extends Seeder
{
    public function run()
    {
        // Creating a default admin user
        DB::table('users')->insert([
            'fname' => 'Grace',
            'sname' => 'Chisambi',
            'email' => 'chisambigrace@gmail.com',
            'email_verified_at' => now(),
            'role' => 'admin',
            'password' => Hash::make('admin_password'),
            'remember_token' => Str::random(10),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
