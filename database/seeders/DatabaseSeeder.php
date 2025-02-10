<?php

namespace Database\Seeders;

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
        $users = User::factory(20)->create();

        \App\Models\Category::factory(5)
            ->has(
                \App\Models\Task::factory()
                    ->withRandomUser($users)
                    ->count(50)
            )
            ->create();
    }
}
