<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@textilecycle.com'],
            [
                'name'              => 'Administrateur TexTile',
                'password'          => Hash::make('Admin1234!'),
                'role'              => 'admin',
                'email_verified_at' => now(),
            ]
        );
    }
}

