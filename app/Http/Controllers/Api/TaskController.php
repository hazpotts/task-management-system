<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\FiltersTasks;
use App\Http\Requests\StoreTaskRequest;
use App\Services\TaskService;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    use FiltersTasks;

    public function __construct(
        private TaskService $taskService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $query = $this->taskService->getQuery();

        $this->filterTasks($request, $query);

        $tasks = $query->orderBy('due_at')->paginate($perPage);

        return response()->json($tasks);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTaskRequest $request)
    {
        return response()->json($this->taskService->create($request->all()), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return response()->json($this->taskService->get($id));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreTaskRequest $request, string $id)
    {
        $this->taskService->set($id);
        $this->taskService->update($request->all());

        return response()->json($this->taskService->get($id));
    }

    /**
     * Soft delete the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $this->taskService->set($id);
        $this->taskService->delete();

        return response()->json(null, 204);
    }

    /**
     * Update the status of the specified resource.
     */
    public function updateStatus(string $id)
    {
        $this->taskService->set($id);
        $this->taskService->updateStatus();

        return response()->json($this->taskService->get($id));
    }

    public function getStats()
    {
        return response()->json([
            'status' => $this->taskService->getStatusStats(),
            'category' => $this->taskService->getCategoryStats(),
        ]);
    }
}
