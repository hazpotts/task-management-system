<?php

use App\Models\Category;
use App\Models\Task;
use App\Models\User;
use App\Services\TaskService;
use Illuminate\Support\Facades\Cache;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->category = Category::factory()->create();
    $this->taskService = new TaskService(new Task);
    actingAs($this->user);
});

test('task status stats are cached', function () {
    // Clear any existing cache
    Cache::forget('user_id_'.$this->user->id.'_task_status_stats');

    // Create some tasks
    Task::factory()
        ->for($this->user)
        ->for($this->category)
        ->create(['due_at' => now()->subDay()]);  // Overdue

    Task::factory()
        ->for($this->user)
        ->for($this->category)
        ->create(['due_at' => now()->addHours(2)]); // Due Soon

    // First call should cache the results
    $firstCall = $this->taskService->getStatusStats();

    // Verify cache exists
    expect(Cache::has('user_id_'.$this->user->id.'_task_status_stats'))->toBeTrue();

    // Create a new task (shouldn't affect cached results)
    Task::factory()
        ->for($this->user)
        ->for($this->category)
        ->create();

    // Second call should return cached results
    $secondCall = $this->taskService->getStatusStats();

    // Both calls should return the same data
    expect($secondCall)->toBe($firstCall);

    // Clear cache
    Cache::forget('user_id_'.$this->user->id.'_task_status_stats');

    // Third call should return fresh data including the new task
    $thirdCall = $this->taskService->getStatusStats();
    expect($thirdCall)->not->toBe($secondCall);
});

test('task category stats are cached', function () {
    // Clear any existing cache
    Cache::forget('user_id_'.$this->user->id.'_task_category_stats');

    // Create some tasks
    Task::factory(2)
        ->for($this->user)
        ->for($this->category)
        ->create();

    // First call should cache the results
    $firstCall = $this->taskService->getCategoryStats();

    // Verify cache exists
    expect(Cache::has('user_id_'.$this->user->id.'_task_category_stats'))->toBeTrue();

    // Create a new task (shouldn't affect cached results)
    Task::factory()
        ->for($this->user)
        ->for($this->category)
        ->create();

    // Second call should return cached results
    $secondCall = $this->taskService->getCategoryStats();

    // Both calls should return the same data
    expect($secondCall)->toBe($firstCall);

    // Clear cache
    Cache::forget('user_id_'.$this->user->id.'_task_category_stats');

    // Third call should return fresh data including the new task
    $thirdCall = $this->taskService->getCategoryStats();
    expect($thirdCall)->not->toBe($secondCall);
});
