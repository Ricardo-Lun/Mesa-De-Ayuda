<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\User;
use Illuminate\Database\Seeder;


class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Carlos Mendoza',
            'email' => 'carlos.mendoza@example.com',
            'password' => 'password123',
            'active' => true,
        ]);

        User::create([
            'name' => 'Laura Torres',
            'email' => 'laura.torres@example.com',
            'password' => 'password123',
            'active' => true,
        ]);

        User::create([
            'name' => 'Miguel Herrera',
            'email' => 'miguel.herrera@example.com',
            'password' => 'password123',
            'active' => false,
        ]);
    }
}
