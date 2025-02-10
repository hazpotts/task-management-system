<?php

namespace App\Enums;

use App\Enums\Traits\IsEnum;

enum TaskStatusEnum: string
{
    use IsEnum;

    case NEW = 'new';
    case IN_PROGRESS = 'in_progress';
    case IN_REVIEW = 'in_review';
    case COMPLETED = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::NEW => __('tasks-list.status_new'),
            self::IN_PROGRESS => __('tasks-list.status_in_progress'),
            self::IN_REVIEW => __('tasks-list.status_in_review'),
            self::COMPLETED => __('tasks-list.status_completed'),
        };
    }

    public static function valuesAndLabels(): array
    {
        return [
            self::NEW->value => __('tasks-list.status_new'),
            self::IN_PROGRESS->value => __('tasks-list.status_in_progress'),
            self::IN_REVIEW->value => __('tasks-list.status_in_review'),
            self::COMPLETED->value => __('tasks-list.status_completed'),
        ];
    }
}
