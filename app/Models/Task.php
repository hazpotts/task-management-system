<?php

namespace App\Models;

use App\Enums\TaskStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use HasFactory,
        SoftDeletes;

    private const DUE_SOON_DAYS = 5;

    protected $fillable = [
        'title',
        'description',
        'started_at',
        'submitted_at',
        'completed_at',
        'due_at',
        'category_id',
    ];

    protected $casts = [
        'due_at' => 'datetime',
        'started_at' => 'datetime',
        'submitted_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    protected $appends = [
        'status',
        'overdue',
        'due_soon',
        'category_name',
    ];

    public function category(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getStatusAttribute(): TaskStatusEnum
    {
        if ($this->completed_at) {
            return TaskStatusEnum::COMPLETED;
        } elseif ($this->submitted_at) {
            return TaskStatusEnum::IN_REVIEW;
        } elseif ($this->started_at) {
            return TaskStatusEnum::IN_PROGRESS;
        } else {
            return TaskStatusEnum::NEW;
        }
    }

    public function getOverdueAttribute(): bool
    {
        return ! $this->completed_at && ! $this->submitted_at && $this->due_at && $this->due_at->isPast();
    }

    public function getDueSoonAttribute(): bool
    {
        return (! $this->completed_at) && (! $this->submitted_at) && $this->due_at && $this->due_at->isFuture() && $this->due_at <= now()->addDays(self::DUE_SOON_DAYS);
    }

    public function getCategoryNameAttribute(): ?string
    {
        return $this->category?->name;
    }

    public function scopeOverdue($query)
    {
        return $query->whereNull('completed_at')
            ->whereNull('submitted_at')
            ->whereNotNull('due_at')
            ->where('due_at', '<', now());
    }

    public function scopeDueSoon($query)
    {
        return $query->whereNull('completed_at')
            ->whereNull('submitted_at')
            ->whereNotNull('due_at')
            ->where('due_at', '>', now())
            ->where('due_at', '<=', now()->addDays(self::DUE_SOON_DAYS));
    }

    public function scopeNew($query)
    {
        return $query->whereNull('started_at')
            ->whereNull('submitted_at')
            ->whereNull('completed_at');
    }

    public function scopeInProgress($query)
    {
        return $query->whereNotNull('started_at')
            ->whereNull('submitted_at')
            ->whereNull('completed_at');
    }

    public function scopeInReview($query)
    {
        return $query->whereNotNull('submitted_at')
            ->whereNull('completed_at');
    }

    public function scopeCompleted($query)
    {
        return $query->whereNotNull('completed_at');
    }

    public function scopeStatus($query, string $status)
    {
        return match ($status) {
            TaskStatusEnum::NEW->value => $query->new(),
            TaskStatusEnum::IN_PROGRESS->value => $query->inProgress(),
            TaskStatusEnum::IN_REVIEW->value => $query->inReview(),
            TaskStatusEnum::COMPLETED->value => $query->completed(),
            default => $query,
        };
    }
}
