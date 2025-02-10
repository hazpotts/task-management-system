<?php

namespace App\Livewire;

use App\Services\TaskService;
use Filament\Widgets\ChartWidget;

class TasksCategoryChart extends ChartWidget
{
    protected static ?string $heading = null;

    protected TaskService $taskService;

    public function __construct()
    {
        $this->taskService = app(TaskService::class);
    }

    public function getHeading(): string
    {
        return __('tasks-list.chart_category_heading');
    }

    protected function getData(): array
    {
        $stats = $this->taskService->getCategoryStats();

        return [
            'datasets' => [
                [
                    'data' => array_values($stats),
                    'backgroundColor' => [
                        '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0',
                        '#9966FF', '#FF9F40', '#FF6384', '#C9CBCF',
                    ],
                    'borderColor' => 'rgba(255, 255, 255, 0.5)',
                    'borderWidth' => 1,
                ],
            ],
            'labels' => array_keys($stats),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
            ],
        ];
    }
}
