<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'is_admin' => true,
            'password' => Hash::make('password'),
        ]);

        User::factory()->create([
            'name' => 'General User',
            'email' => 'general@example.com',
            'is_admin' => false,
            'password' => Hash::make('password'),
        ]);

        User::factory()->count(6)->create();
    }
}
