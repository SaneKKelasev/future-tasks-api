<?php

namespace App\Services;

use App\Models\Task;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TaskService
{
    /**
     * Список задач с поиском, сортировкой и пагинацией.
     *
     * @param array{search?: string|null, sort?: string, per_page?: int} $filters
     */
    public function list(array $filters): LengthAwarePaginator
    {
        $query = Task::query();

        // Поиск по названию
        if (! empty($filters['search'])) {
            $query->where('title', 'like', '%' . $filters['search'] . '%');
        }

        // Сортировка по дате выполнения
        // Дефолт sort=due_date задан в ListTasksRequest.
        $query->orderBy($filters['sort'] ?? 'due_date');

        // Пагинация. per_page приходит валидированным (1..100),
        // дефолт 15 — задан в ListTasksRequest.
        return $query->paginate($filters['per_page'] ?? 15);
    }

    /**
     * Создание задачи.
     *
     * @param array<string, mixed> $data
     */
    public function create(array $data): Task
    {
        return Task::create($data);
    }

    /**
     * Обновление задачи.
     *
     * @param array<string, mixed> $data
     */
    public function update(Task $task, array $data): Task
    {
        $task->update($data);

        return $task->refresh();
    }

    /**
     * Удаление задачи.
     */
    public function delete(Task $task): void
    {
        $task->delete();
    }
}