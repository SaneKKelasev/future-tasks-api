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
use OpenApi\Attributes as OA;

class TaskController extends Controller
{
    public function __construct(
        private readonly TaskService $service,
    ) {
    }

    #[OA\Get(
        path: '/api/tasks',
        summary: 'Список задач',
        description: 'Возвращает постраничный список задач с возможностью поиска и сортировки.',
        tags: ['Tasks'],
        parameters: [
            new OA\Parameter(name: 'search', in: 'query', description: 'Поиск по названию', schema: new OA\Schema(type: 'string', maxLength: 255)),
            new OA\Parameter(name: 'sort', in: 'query', description: 'Поле сортировки', schema: new OA\Schema(type: 'string', enum: ['due_date', 'created_at'])),
            new OA\Parameter(name: 'page', in: 'query', description: 'Номер страницы', schema: new OA\Schema(type: 'integer', minimum: 1)),
            new OA\Parameter(name: 'per_page', in: 'query', description: 'Записей на странице', schema: new OA\Schema(type: 'integer', minimum: 1, maximum: 100, default: 15)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Список задач', content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Task')),
                    new OA\Property(property: 'links', type: 'object'),
                    new OA\Property(property: 'meta', type: 'object'),
                ],
            )),
            new OA\Response(response: 422, description: 'Ошибка валидации query-параметров'),
        ],
    )]
    /**
     * GET /api/tasks
     */
    public function index(ListTasksRequest $request): AnonymousResourceCollection
    {
        $tasks = $this->service->list($request->validated());

        return TaskResource::collection($tasks);
    }

    #[OA\Post(
        path: '/api/tasks',
        summary: 'Создать задачу',
        tags: ['Tasks'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['title', 'due_date'],
                properties: [
                    new OA\Property(property: 'title', type: 'string', maxLength: 255, example: 'Задача1'),
                    new OA\Property(property: 'description', type: 'string', nullable: true, example: 'Описание задачи'),
                    new OA\Property(property: 'due_date', type: 'string', format: 'date-time', example: '2025-01-20T15:00:00'),
                    new OA\Property(property: 'status', type: 'string', enum: ['pending', 'done'], example: 'pending'),
                    new OA\Property(property: 'priority', type: 'string', enum: ['low', 'medium', 'high'], example: 'high'),
                    new OA\Property(property: 'category', type: 'string', nullable: true, maxLength: 100, example: 'Работа'),
                ],
            ),
        ),
        responses: [
            new OA\Response(response: 201, description: 'Задача создана', content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'id', type: 'integer', example: 1),
                    new OA\Property(property: 'message', type: 'string', example: 'Task created successfully'),
                ],
            )),
            new OA\Response(response: 422, description: 'Ошибка валидации'),
        ],
    )]
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

    #[OA\Get(
        path: '/api/tasks/{id}',
        summary: 'Получить задачу по ID',
        tags: ['Tasks'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID задачи', schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Задача найдена', content: new OA\JsonContent(ref: '#/components/schemas/Task')),
            new OA\Response(response: 404, description: 'Задача не найдена'),
        ],
    )]
    /**
     * GET /api/tasks/{task}
     */
    public function show(Task $task): TaskResource
    {
        return new TaskResource($task);
    }

    #[OA\Put(
        path: '/api/tasks/{id}',
        summary: 'Обновить задачу',
        tags: ['Tasks'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID задачи', schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'title', type: 'string', maxLength: 255, example: 'Задача2'),
                    new OA\Property(property: 'description', type: 'string', nullable: true),
                    new OA\Property(property: 'due_date', type: 'string', format: 'date-time'),
                    new OA\Property(property: 'status', type: 'string', enum: ['pending', 'done']),
                    new OA\Property(property: 'priority', type: 'string', enum: ['low', 'medium', 'high']),
                    new OA\Property(property: 'category', type: 'string', nullable: true, maxLength: 100),
                ],
            ),
        ),
        responses: [
            new OA\Response(response: 200, description: 'Задача обновлена', content: new OA\JsonContent(
                properties: [new OA\Property(property: 'message', type: 'string', example: 'Task updated successfully')],
            )),
            new OA\Response(response: 404, description: 'Задача не найдена'),
            new OA\Response(response: 422, description: 'Ошибка валидации'),
        ],
    )]
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

    #[OA\Delete(
        path: '/api/tasks/{id}',
        summary: 'Удалить задачу',
        tags: ['Tasks'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID задачи', schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Задача удалена', content: new OA\JsonContent(
                properties: [new OA\Property(property: 'message', type: 'string', example: 'Task deleted successfully')],
            )),
            new OA\Response(response: 404, description: 'Задача не найдена'),
        ],
    )]
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