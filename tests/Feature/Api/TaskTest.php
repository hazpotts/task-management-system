<?php

namespace Tests\Feature\Api;

use App\Enums\TaskStatusEnum;
use App\Models\Category;
use App\Models\Task;
use App\Models\User;

use function Pest\Laravel\deleteJson;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;
use function Pest\Laravel\putJson;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->category = Category::factory()->create();
    $this->token = $this->user->createToken('test-device')->plainTextToken;
});

test('user can create task', function () {
    $data = [
        'title' => fake()->sentence,
        'description' => fake()->paragraph,
        'category_id' => $this->category->id,
        'due_at' => now()->addDays(5)->toDateTimeString(),
    ];

    postJson('/api/tasks', $data, [
        'Authorization' => "Bearer {$this->token}",
    ])
        ->assertStatus(201)
        ->assertJsonStructure([
            'id',
            'title',
            'description',
            'category_id',
            'due_at',
            'created_at',
            'updated_at',
        ]);

    expect(db()->table('tasks')->where('title', $data['title'])->exists())->toBeTrue();
});

test('user can list their tasks', function () {
    Task::factory(3)
        ->for($this->user)
        ->for($this->category)
        ->create();

    // Create tasks for another user that shouldn't be visible
    Task::factory(2)
        ->for($this->category)
        ->create();

    getJson('/api/tasks', [
        'Authorization' => "Bearer {$this->token}",
    ])
        ->assertOk()
        ->assertJsonCount(3, 'data')
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'title',
                    'description',
                    'status',
                    'due_at',
                    'category_id',
                    'category_name',
                ],
            ],
        ]);
});

test('user can view their task', function () {
    $task = Task::factory()
        ->for($this->user)
        ->for($this->category)
        ->create();

    getJson("/api/tasks/{$task->id}", [
        'Authorization' => "Bearer {$this->token}",
    ])
        ->assertOk()
        ->assertJsonStructure([
            'id',
            'title',
            'description',
            'status',
            'due_at',
            'category_id',
            'category_name',
        ]);
});

test('user cannot view others task', function () {
    $task = Task::factory()
        ->for($this->category)
        ->create();

    getJson("/api/tasks/{$task->id}", [
        'Authorization' => "Bearer {$this->token}",
    ])->assertForbidden();
});

test('user can update their task', function () {
    $task = Task::factory()
        ->for($this->user)
        ->for($this->category)
        ->create();

    $data = [
        'title' => 'Updated Title',
        'description' => 'Updated Description',
        'category_id' => $this->category->id,
        'due_at' => now()->addDays(10)->toDateTimeString(),
    ];

    putJson("/api/tasks/{$task->id}", $data, [
        'Authorization' => "Bearer {$this->token}",
    ])->assertOk();

    expect(db()->table('tasks')->where('id', $task->id)->where('title', 'Updated Title')->exists())->toBeTrue();
});

test('user can delete their task', function () {
    $task = Task::factory()
        ->for($this->user)
        ->for($this->category)
        ->create();

    deleteJson("/api/tasks/{$task->id}", [], [
        'Authorization' => "Bearer {$this->token}",
    ])->assertStatus(204);

    expect(db()->table('tasks')->where('id', $task->id)->whereNotNull('deleted_at')->exists())->toBeTrue();
});

test('user can update task status', function () {
    $task = Task::factory()
        ->for($this->user)
        ->for($this->category)
        ->create();

    postJson("/api/tasks/{$task->id}/update-status", [], [
        'Authorization' => "Bearer {$this->token}",
    ])->assertOk();

    expect($task->fresh()->started_at)->not->toBeNull();
});

test('tasks can be filtered by status via api', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-token')->plainTextToken;

    $newTask = Task::factory()->create([
        'user_id' => $user->id,
        'completed_at' => null,
        'submitted_at' => null,
        'started_at' => null,
    ]);

    $inProgressTask = Task::factory()->create([
        'user_id' => $user->id,
        'started_at' => now(),
        'completed_at' => null,
        'submitted_at' => null,
    ]);

    $response = $this->withHeaders([
        'Authorization' => 'Bearer '.$token,
    ])->getJson('/api/tasks?status='.TaskStatusEnum::NEW->value);

    $response->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $newTask->id);
});

test('tasks can be filtered by category via api', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-token')->plainTextToken;

    $category1 = Category::factory()->create();
    $category2 = Category::factory()->create();

    $task1 = Task::factory()->create([
        'user_id' => $user->id,
        'category_id' => $category1->id,
        'completed_at' => null,
        'submitted_at' => null,
        'started_at' => null,
    ]);

    $task2 = Task::factory()->create([
        'user_id' => $user->id,
        'category_id' => $category2->id,
        'completed_at' => null,
        'submitted_at' => null,
        'started_at' => null,
    ]);

    $response = $this->withHeaders([
        'Authorization' => 'Bearer '.$token,
    ])->getJson('/api/tasks?category_id='.$category1->id);

    $response->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $task1->id);
});

test('tasks can be filtered by overdue via api', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-token')->plainTextToken;

    $overdueTask = Task::factory()->create([
        'user_id' => $user->id,
        'completed_at' => null,
        'submitted_at' => null,
        'started_at' => null,
        'due_at' => now()->subDay(),
    ]);

    $upcomingTask = Task::factory()->create([
        'user_id' => $user->id,
        'completed_at' => null,
        'submitted_at' => null,
        'started_at' => null,
        'due_at' => now()->addDay(),
    ]);

    $response = $this->withHeaders([
        'Authorization' => 'Bearer '.$token,
    ])->getJson('/api/tasks?overdue=1');

    $response->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $overdueTask->id);
});
