<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();
        Role::create([
            'code' => 'admin',
            'name' => 'Admin',
        ]);
        Role::create([
            'code' => 'user',
            'name' => 'User',
        ]);

        User::factory()->create([
            'name' => 'Test User',
            'phone' => '0123456789',
            'email' => 'test@example.com',
            'role_id' => 1,
        ]);
        User::factory()->create([
            'name' => 'Test User 2',
            'phone' => '01234567899',
            'email' => 'test2@example.com',
            'role_id' => 1,
        ]);
    }
}
