<?php

namespace App\Livewire;

use App\Enums\TaskStatusEnum;
use App\Http\Requests\StoreTaskRequest;
use App\Models\Category;
use App\Models\Task;
use App\Services\TaskService;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Livewire\Component;

class ListTasks extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        $taskService = app(TaskService::class);

        return $table
            ->query($taskService->getQuery())
            ->columns([
                TextColumn::make('title')
                    ->label(__('tasks-list.title')),
                TextColumn::make('due_at')
                    ->label(__('tasks-list.due_at'))
                    ->dateTime('H:i - jS F Y'),
                TextColumn::make('category.name')
                    ->label(__('tasks-list.category')),
                TextColumn::make('status')
                    ->label(__('tasks-list.status'))
                    ->getStateUsing(function (Task $record): string {
                        $status = $record->status->label();
                        if ($record->overdue) {
                            $status .= ' ('.__('tasks-list.overdue').')';
                        } elseif ($record->due_soon) {
                            $status .= ' ('.__('tasks-list.due_soon').')';
                        }

                        return $status;
                    }),
            ])
            ->defaultSort('due_at', 'asc')
            ->filters([
                SelectFilter::make('status')
                    ->label(__('tasks-list.status'))
                    ->options(TaskStatusEnum::valuesAndLabels())
                    ->query(function ($query, $data) {
                        if (! $data['value']) {
                            return $query;
                        }

                        return $query->status($data['value']);
                    }),
                SelectFilter::make('category_id')
                    ->label(__('tasks-list.category'))
                    ->options(Category::all()->pluck('name', 'id'))
                    ->query(function ($query, $data) {
                        if (! $data['value']) {
                            return $query;
                        }

                        return $query->where('category_id', $data['value']);
                    }),
                Filter::make('overdue')
                    ->label(__('tasks-list.overdue'))
                    ->query(fn ($query) => $query->overdue()),
                Filter::make('due_soon')
                    ->label(__('tasks-list.due_soon'))
                    ->query(fn ($query) => $query->dueSoon()),
            ])
            ->actions([
                Action::make('updateStatus')
                    ->label(fn (Task $record): string => match ($record->status) {
                        TaskStatusEnum::NEW => __('tasks-list.mark_as_status', ['status' => __('tasks-list.status_in_progress')]),
                        TaskStatusEnum::IN_PROGRESS => __('tasks-list.mark_as_status', ['status' => __('tasks-list.status_in_review')]),
                        TaskStatusEnum::IN_REVIEW => __('tasks-list.mark_as_status', ['status' => __('tasks-list.status_completed')]),
                        default => __('tasks-list.update_status'),
                    })
                    ->action(function (Task $record, TaskService $taskService): void {
                        $taskService->set($record);
                        $taskService->updateStatus();
                    })
                    ->button()
                    ->extraAttributes([
                        'class' => 'bg-gray-50 dark:bg-white/5 text-gray-700 hover:text-gray-700 hover:bg-gray-100',
                    ])
                    ->hidden(fn (Task $record): bool => $record->status === TaskStatusEnum::COMPLETED),
                Action::make('editTask')
                    ->label(__('tasks-list.edit_task'))
                    ->form(fn (): array => $this->taskForm())
                    ->action(function (array $data, Task $record, TaskService $taskService): void {
                        $taskService->set($record);
                        $taskService->update($data);
                    })
                    ->fillForm(fn (Task $record): array => $record->toArray())
                    ->button()
                    ->extraAttributes([
                        'class' => 'bg-gray-50 dark:bg-white/5 text-gray-700 hover:text-gray-700 hover:bg-gray-100',
                    ]),
            ])
            ->bulkActions([
                // ...
            ])
            ->headerActions([
                Action::make('createTask')
                    ->label(__('tasks-list.create_task'))
                    ->form(fn (): array => $this->taskForm())
                    ->action(function (array $data, TaskService $taskService): void {
                        $taskService->create($data);
                    })
                    ->button(),
            ])
            ->recordClasses(function (Task $record): string {
                $classes = $record->overdue ? 'bg-red-900' : '';
                if (empty($classes)) {
                    $classes = $record->due_soon ? 'bg-yellow-900' : '';
                }
                if (empty($classes)) {
                    $classes = $record->status === TaskStatusEnum::COMPLETED ? 'bg-green-900' : '';
                }

                return $classes;
            });
    }

    private function taskForm(): array
    {
        return [
            TextInput::make('title')
                ->label(__('tasks-list.title'))
                ->required(in_array('required', StoreTaskRequest::staticRules()['title']))
                ->maxLength(StoreTaskRequest::MAX_TITLE)
                ->rules(StoreTaskRequest::staticRules()['title']),
            TextInput::make('description')
                ->label(__('tasks-list.description'))
                ->required(in_array('required', StoreTaskRequest::staticRules()['description']))
                ->maxLength(StoreTaskRequest::MAX_DESCRIPTION)
                ->rules(StoreTaskRequest::staticRules()['description']),
            Select::make('category_id')
                ->label(__('tasks-list.category'))
                ->relationship('category', 'name')
                ->required(in_array('required', StoreTaskRequest::staticRules()['category_id']))
                ->rules(StoreTaskRequest::staticRules()['category_id']),
            DateTimePicker::make('due_at')
                ->label(__('tasks-list.due_at'))
                ->required(in_array('required', StoreTaskRequest::staticRules()['due_at']))
                ->rules(StoreTaskRequest::staticRules()['due_at']),
        ];
    }

    public function render()
    {
        return view('livewire.list-tasks');
    }
}
