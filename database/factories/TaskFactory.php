<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task>
 */
class TaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startedAt = fake()->boolean(70) ? fake()->dateTimeBetween('-60 days', '-15 days') : null;
        $submittedAt = $startedAt && fake()->boolean(60) ? fake()->dateTimeBetween($startedAt, '-10 days') : null;
        $completedAt = $submittedAt && fake()->boolean(50) ? fake()->dateTimeBetween($submittedAt, '-1 days') : null;

        return [
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'started_at' => $startedAt,
            'submitted_at' => $submittedAt,
            'completed_at' => $completedAt,
            'due_at' => fake()->dateTimeBetween('-15 days', '+30 days'),
            'category_id' => \App\Models\Category::factory(),
            'user_id' => \App\Models\User::factory(),
        ];
    }

    public function withRandomUser($users)
    {
        return $this->state(function (array $attributes) use ($users) {
            return [
                'user_id' => $users->random()->id,
            ];
        });
    }
}
