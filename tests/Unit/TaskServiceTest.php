<?php

use App\Models\Category;
use App\Models\Task;
use App\Models\User;
use App\Services\TaskService;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->category = Category::factory()->create();
    $this->taskService = new TaskService(new Task);
    actingAs($this->user);
});

test('get query returns user tasks', function () {
    // Create tasks for the authenticated user
    Task::factory(2)
        ->for($this->user)
        ->for($this->category)
        ->create();

    // Create tasks for another user
    Task::factory(2)
        ->for($this->category)
        ->create();

    $tasks = $this->taskService->getQuery()->get();

    expect($tasks)->toHaveCount(2)
        ->each(fn ($task) => $task->user_id->toBe($this->user->id));
});

test('create task', function () {
    $data = [
        'title' => 'Test Task',
        'description' => 'Test Description',
        'category_id' => $this->category->id,
        'due_at' => now()->addDays(5),
    ];

    $task = $this->taskService->create($data);

    expect(db()->table('tasks')->where('id', $task->id)->exists())->toBeTrue()
        ->and($task->title)->toBe('Test Task')
        ->and($task->user_id)->toBe($this->user->id);
});

test('update task', function () {
    $task = Task::factory()
        ->for($this->user)
        ->for($this->category)
        ->create();

    $this->taskService->set($task);

    $data = [
        'title' => 'Updated Title',
        'description' => 'Updated Description',
    ];

    $this->taskService->update($data);

    expect(db()->table('tasks')->where('id', $task->id)->where('title', 'Updated Title')->exists())->toBeTrue();
});

test('delete task', function () {
    $task = Task::factory()
        ->for($this->user)
        ->for($this->category)
        ->create();

    $this->taskService->set($task);
    $this->taskService->delete();

    expect(db()->table('tasks')->where('id', $task->id)->whereNotNull('deleted_at')->exists())->toBeTrue();
});

test('update status progression', function () {
    $task = Task::factory()
        ->for($this->user)
        ->for($this->category)
        ->create([
            'started_at' => null,
            'submitted_at' => null,
            'completed_at' => null,
        ]);

    $this->taskService->set($task);

    // First call should set started_at
    $this->taskService->updateStatus();
    expect($task->fresh()->started_at)->not->toBeNull()
        ->and($task->fresh()->submitted_at)->toBeNull()
        ->and($task->fresh()->completed_at)->toBeNull();

    // Second call should set submitted_at
    $this->taskService->updateStatus();
    expect($task->fresh()->submitted_at)->not->toBeNull()
        ->and($task->fresh()->completed_at)->toBeNull();

    // Third call should set completed_at
    $this->taskService->updateStatus();
    expect($task->fresh()->completed_at)->not->toBeNull();
});

test('cannot update others task', function () {
    $otherUser = User::factory()->create();
    $task = Task::factory()
        ->for($otherUser)
        ->for($this->category)
        ->create();

    $this->taskService->set($task);

    expect(fn () => $this->taskService->update(['title' => 'New Title']))
        ->toThrow(\Illuminate\Auth\Access\AuthorizationException::class);
});
