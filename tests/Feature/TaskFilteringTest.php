<?php

use App\Enums\TaskStatusEnum;
use App\Livewire\ListTasks;
use App\Models\Category;
use App\Models\Task;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->category = Category::factory()->create();
    $this->token = $this->user->createToken('test-device')->plainTextToken;
    actingAs($this->user);
});

test('tasks can be filtered by overdue status', function () {
    // Create overdue task (not completed or submitted)
    $overdue = Task::factory()
        ->for($this->user)
        ->for($this->category)
        ->create([
            'due_at' => now()->subDays(1),
            'completed_at' => null,
            'submitted_at' => null,
            'started_at' => null,
        ]);

    // Create non-overdue task
    $upcoming = Task::factory()
        ->for($this->user)
        ->for($this->category)
        ->create([
            'due_at' => now()->addDays(5),
            'completed_at' => null,
            'submitted_at' => null,
            'started_at' => null,
        ]);

    livewire(ListTasks::class)
        ->filterTable('overdue')
        ->assertCanSeeTableRecords([$overdue])
        ->assertCanNotSeeTableRecords([$upcoming]);
});

test('tasks can be filtered by due soon status', function () {
    // Create due soon task (within next 24 hours, not completed or submitted)
    $dueSoon = Task::factory()
        ->for($this->user)
        ->for($this->category)
        ->create([
            'due_at' => now()->addHours(23),
            'completed_at' => null,
            'submitted_at' => null,
            'started_at' => null,
        ]);

    // Create task not due soon
    $notDueSoon = Task::factory()
        ->for($this->user)
        ->for($this->category)
        ->create([
            'due_at' => now()->addDays(10),
            'completed_at' => null,
            'submitted_at' => null,
            'started_at' => null,
        ]);

    livewire(ListTasks::class)
        ->filterTable('due_soon')
        ->assertCanSeeTableRecords([$dueSoon])
        ->assertCanNotSeeTableRecords([$notDueSoon]);
});

test('tasks can be filtered by status', function () {
    $newTask = Task::factory()->create([
        'user_id' => $this->user->id,
        'completed_at' => null,
        'submitted_at' => null,
        'started_at' => null,
    ]);

    $inProgressTask = Task::factory()->create([
        'user_id' => $this->user->id,
        'started_at' => now(),
        'completed_at' => null,
        'submitted_at' => null,
    ]);

    livewire(ListTasks::class)
        ->assertCanSeeTableRecords([$newTask, $inProgressTask])
        ->filterTable('status', TaskStatusEnum::NEW->value)
        ->assertCanSeeTableRecords([$newTask])
        ->assertCanNotSeeTableRecords([$inProgressTask]);
});

test('tasks can be filtered by category', function () {
    $category1 = Category::factory()->create();
    $category2 = Category::factory()->create();

    $task1 = Task::factory()->create([
        'user_id' => $this->user->id,
        'category_id' => $category1->id,
        'completed_at' => null,
        'submitted_at' => null,
        'started_at' => null,
    ]);

    $task2 = Task::factory()->create([
        'user_id' => $this->user->id,
        'category_id' => $category2->id,
        'completed_at' => null,
        'submitted_at' => null,
        'started_at' => null,
    ]);

    livewire(ListTasks::class)
        ->assertCanSeeTableRecords([$task1, $task2])
        ->filterTable('category_id', $category1->id)
        ->assertCanSeeTableRecords([$task1])
        ->assertCanNotSeeTableRecords([$task2]);
});
