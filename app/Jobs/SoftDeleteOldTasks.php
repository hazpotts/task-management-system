<?php

namespace App\Jobs;

use App\Models\Task;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;

class SoftDeleteOldTasks implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private const ARCHIVE_AFTER_DAYS = 30;

    public function handle(): void
    {
        // Get all tasks that were completed more than ARCHIVE_AFTER_DAYS ago
        $tasks = Task::query()
            ->whereNotNull('completed_at')
            ->where('completed_at', '<=', now()->subDays(self::ARCHIVE_AFTER_DAYS))
            ->whereNull('deleted_at')
            ->get()
            ->groupBy('user_id');

        // Archive tasks and clear cache for each affected user
        foreach ($tasks as $userId => $userTasks) {
            foreach ($userTasks as $task) {
                $task->delete();
            }

            // Clear the stats cache for this user
            Cache::forget('user_id_'.$userId.'_task_status_stats');
            Cache::forget('user_id_'.$userId.'_task_category_stats');
        }
    }
}
