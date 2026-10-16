<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ListTasksRequest;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use App\Services\TaskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TaskController extends Controller
{
    public function __construct(
        private readonly TaskService $service,
    ) {
    }

    /**
     * GET /api/tasks
     */
    public function index(ListTasksRequest $request): AnonymousResourceCollection
    {
        $tasks = $this->service->list($request->validated());

        return TaskResource::collection($tasks);
    }

    /**
     * POST /api/tasks
     */
    public function store(StoreTaskRequest $request): JsonResponse
    {
        $task = $this->service->create($request->validated());

        return response()->json([
            'id'        => $task->id,
            'message'   => 'Task created successfully',
        ], 201);
    }

    /**
     * GET /api/tasks/{task}
     */
    public function show(Task $task): TaskResource
    {
        return new TaskResource($task);
    }

    /**
     * PUT /api/tasks/{task}
     */
    public function update(UpdateTaskRequest $request, Task $task): JsonResponse
    {
        $this->service->update($task, $request->validated());

        return response()->json([
            'message' => 'Task updated successfully',
        ]);
    }

    /**
     * DELETE /api/tasks/{task}
     */
    public function destroy(Task $task): JsonResponse
    {
        $this->service->delete($task);

        return response()->json([
            'message' => 'Task deleted successfully',
        ]);
    }
}