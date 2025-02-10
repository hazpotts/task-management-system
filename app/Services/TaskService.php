<?php

namespace App\Services;

use App\Models\Task;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;

class TaskService
{
    public function __construct(
        protected Task $task
    ) {}

    public function getQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return $this->task->where('user_id', auth()->user()->id);
    }

    public function getTasks(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->getQuery()->get();
    }

    public function set(Task|int $task): void
    {
        if (is_int($task)) {
            $this->task = $this->task->findOrFail($task);
        } else {
            $this->task = $task;
        }
    }

    public function get(?int $id = null): ?Task
    {
        $this->task = $this->task->findOrFail($id);
        Gate::authorize('view', $this->task);

        return $this->task;
    }

    public function create(array $data): Task
    {
        Gate::authorize('create', Task::class);
        $data['user_id'] = auth()->user()->id;

        return $this->task->forceCreate($data);
    }

    public function update(array $data, ?int $id = null): bool
    {
        if (! $this->task) {
            $this->task = $this->task->findOrFail($id);
        }
        Gate::authorize('update', $this->task);

        return $this->task->update($data);
    }

    public function delete(?int $id = null): bool
    {
        if (! $this->task) {
            $this->task = $this->task->findOrFail($id);
        }
        Gate::authorize('delete', $this->task);

        return $this->task->delete();
    }

    public function updateStatus(?int $id = null): bool
    {
        if (! $this->task) {
            $this->task = $this->task->findOrFail($id);
        }
        Gate::authorize('update', $this->task);
        if (! $this->task->started_at) {
            $this->task->started_at = now();
        } elseif (! $this->task->submitted_at) {
            $this->task->submitted_at = now();
        } elseif (! $this->task->completed_at) {
            $this->task->completed_at = now();
        }

        return $this->task->save();
    }

    protected function getCacheKey(string $type): string
    {
        return 'user_id_'.auth()->id().'_'.$type;
    }

    public function getStatusStats(): array
    {
        return Cache::remember($this->getCacheKey('task_status_stats'), 300, function () {
            return [
                'overdue' => $this->getQuery()
                    ->overdue()
                    ->count(),
                'due_soon' => $this->getQuery()
                    ->dueSoon()
                    ->count(),
                'new' => $this->getQuery()
                    ->new()
                    ->count(),
                'in_progress' => $this->getQuery()
                    ->inProgress()
                    ->count(),
                'in_review' => $this->getQuery()
                    ->inReview()
                    ->count(),
                'completed' => $this->getQuery()
                    ->completed()
                    ->count(),
            ];
        });
    }

    public function getCategoryStats(): array
    {
        return Cache::remember($this->getCacheKey('task_category_stats'), 300, function () {
            return $this->getQuery()
                ->with('category')
                ->get()
                ->groupBy('category.name')
                ->map(fn ($tasks) => $tasks->count())
                ->toArray();
        });
    }
}
