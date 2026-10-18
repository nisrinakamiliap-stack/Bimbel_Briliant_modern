<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        User::factory()->admin()->create([
            'name' => 'Admin Bimbel',
            'email' => 'admin@example.com',
        ]);

        User::factory()->teacher()->create([
            'name' => 'Guru Bimbel',
            'email' => 'guru@example.com',
        ]);

        User::factory()->student()->create([
            'name' => 'Murid Bimbel',
            'email' => 'murid@example.com',
        ]);

        $this->call(ProgramSeeder::class);
    }
}
