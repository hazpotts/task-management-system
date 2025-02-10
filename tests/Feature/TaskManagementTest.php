<?php

use App\Livewire\ListTasks;
use App\Models\Category;
use App\Models\Task;
use App\Models\User;
use App\Services\TaskService;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->category = Category::factory()->create();
    $this->taskService = new TaskService(new Task);
    actingAs($this->user);
});

test('dashboard shows tasks list', function () {
    $tasks = Task::factory(3)
        ->for($this->user)
        ->for($this->category)
        ->create();

    actingAs($this->user)
        ->get('/dashboard')
        ->assertOk()
        ->assertSeeLivewire(ListTasks::class);

    livewire(ListTasks::class)
        ->assertCanSeeTableRecords($tasks);
});

test('user can create task', function () {
    actingAs($this->user);

    $taskData = [
        'title' => fake()->sentence,
        'description' => fake()->paragraph,
        'category_id' => $this->category->id,
        'due_at' => now()->addDays(5)->toDateTimeString(),
    ];

    livewire(ListTasks::class)
        ->callTableAction(name: 'createTask', data: $taskData);

    expect(db()->table('tasks')->where('title', $taskData['title'])->exists())->toBeTrue();
});

test('user can edit task', function () {
    actingAs($this->user);

    $task = Task::factory()
        ->for($this->user)
        ->for($this->category)
        ->create();

    $updatedData = [
        'title' => 'Updated Title',
        'description' => 'Updated Description',
        'category_id' => $this->category->id,
        'due_at' => now()->addDays(10)->toDateTimeString(),
    ];

    livewire(ListTasks::class)
        ->callTableAction(name: 'editTask', data: $updatedData, record: $task);

    expect(db()->table('tasks')->where('id', $task->id)->where('title', 'Updated Title')->exists())->toBeTrue();
});

test('user can update task status', function () {
    actingAs($this->user);

    $task = Task::factory()
        ->for($this->user)
        ->for($this->category)
        ->create([
            'started_at' => null,
            'submitted_at' => null,
            'completed_at' => null,
        ]);

    livewire(ListTasks::class)
        ->callTableAction(name: 'updateStatus', record: $task);

    expect($task->fresh()->started_at)->not->toBeNull();
});

test('completed tasks status cannot be updated', function () {
    actingAs($this->user);

    $task = Task::factory()
        ->for($this->user)
        ->for($this->category)
        ->create([
            'started_at' => now()->subDays(3),
            'submitted_at' => now()->subDays(2),
            'completed_at' => now()->subDays(1),
        ]);

    livewire(ListTasks::class)
        ->assertTableActionHidden('updateStatus', $task);
});

test('tasks are filtered by user', function () {
    actingAs($this->user);

    // Create tasks for the authenticated user
    $userTasks = Task::factory(2)
        ->for($this->user)
        ->for($this->category)
        ->create();

    // Create tasks for another user
    $otherTasks = Task::factory(2)
        ->for($this->category)
        ->create();

    livewire(ListTasks::class)
        ->assertCanSeeTableRecords($userTasks)
        ->assertCanNotSeeTableRecords($otherTasks);
});

test('overdue tasks are highlighted', function () {
    actingAs($this->user);

    $overdueTask = Task::factory()
        ->for($this->user)
        ->for($this->category)
        ->create([
            'due_at' => now()->subDays(1),
            'completed_at' => null,
            'submitted_at' => null,
        ]);

    livewire(ListTasks::class)
        ->assertCanSeeTableRecords([$overdueTask])
        ->assertSeeHtml('bg-red-900');
});
