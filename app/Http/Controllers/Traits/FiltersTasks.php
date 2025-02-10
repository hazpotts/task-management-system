<?php

namespace App\Http\Controllers\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

trait FiltersTasks
{
    protected function filterTasks(Request $request, Builder $query): void
    {
        if ($request->has('status')) {
            $query->status($request->input('status'));
        }

        if ($request->has('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        if ($request->boolean('overdue')) {
            $query->overdue();
        }

        if ($request->boolean('due_soon')) {
            $query->dueSoon();
        }
    }
}
