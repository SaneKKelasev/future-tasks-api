<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class TaskControllerTest extends TestCase
{
    use RefreshDatabase;

    /*
    * POST /api/tasks
    */
    public function test_task_can_be_created(): void
    {
        $payload = [
            'title'         => 'Новая задача',
            'description'   => 'Описание задачи',
            'due_date'      => '2026-12-01T10:00:00',
            'priority'      => 'high',
            'category'      => 'Работа',
        ];

        $response = $this->postJson('/api/tasks', $payload);

        $response->assertCreated()
            ->assertJson(['message' => 'Task created successfully']);

        $this->assertDatabaseHas('tasks', [
            'title'     => 'Новая задача',
            'priority'  => 'high',
            'status'    => 'pending',
        ]);
    }

    #[DataProvider('invalidCreatePayloadsProvider')]
    public function test_create_validates_input(array $payload, string $expectedErrorField): void
    {
        $this->postJson('/api/tasks', $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors($expectedErrorField);
    }

    /**
     * @return array<string, array{array<string, mixed>, string}>
     */
    public static function invalidCreatePayloadsProvider(): array
    {
        $valid = ['title' => 'Валидная задача', 'due_date' => '2026-12-01T10:00:00'];

        return [
            'title missing' => [['due_date' => '2026-12-01T10:00:00'], 'title'],
            'title too long' => [array_merge($valid, ['title' => str_repeat('a', 256)]), 'title'],
            'due_date invalid' => [array_merge($valid, ['due_date' => 'not-a-date']), 'due_date'],
            'priority invalid' => [array_merge($valid, ['priority' => 'urgent']), 'priority'],
        ];
    }

    /*
    * GET /api/tasks
    */

    public function test_tasks_list_is_paginated(): void
    {
        Task::factory()->count(20)->create();

        $response = $this->getJson('/api/tasks');

        $response->assertOk()
            ->assertJsonPath('meta.total', 20)
            ->assertJsonPath('meta.per_page', 15)
            ->assertJsonCount(15, 'data');
    }

    public function test_tasks_list_can_search_by_title(): void
    {
        Task::factory()->create(['title' => 'Подготовить отчёт за квартал']);
        Task::factory()->create(['title' => 'Купить продукты']);

        $response = $this->getJson('/api/tasks?search=отчёт');

        $response->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.title', 'Подготовить отчёт за квартал');
    }

    public function test_tasks_list_is_sorted_by_due_date(): void
    {
        Task::factory()->create(['title' => 'Позже', 'due_date' => '2026-12-10 12:00:00']);
        Task::factory()->create(['title' => 'Раньше', 'due_date' => '2026-12-01 12:00:00']);

        $response = $this->getJson('/api/tasks');

        $response->assertOk()
            ->assertJsonPath('data.0.title', 'Раньше')
            ->assertJsonPath('data.1.title', 'Позже');
    }

    /*
    * GET /api/tasks/{id}
    */

    public function test_task_can_be_shown(): void
    {
        $task = Task::factory()->create(['title' => 'Конкретная задача']);

        $this->getJson("/api/tasks/{$task->id}")
            ->assertOk()
            ->assertJsonPath('id', $task->id)
            ->assertJsonPath('title', 'Конкретная задача');
    }

    public function test_show_returns_404_for_unknown_task(): void
    {
        $this->getJson('/api/tasks/999')->assertNotFound();
    }

    /*
    * PUT /api/tasks/{id}
    */

    public function test_task_can_be_updated(): void
    {
        $task = Task::factory()->create(['title' => 'Старое название', 'priority' => 'low']);

        $response = $this->putJson("/api/tasks/{$task->id}", [
            'title'     => 'Новое название',
            'priority'  => 'high',
            'status'    => 'done',
        ]);

        $response->assertOk()
            ->assertJson(['message' => 'Task updated successfully']);

        $this->assertDatabaseHas('tasks', [
            'id'        => $task->id,
            'title'     => 'Новое название',
            'priority'  => 'high',
            'status'    => 'done',
        ]);
    }

    public function test_update_returns_404_for_unknown_task(): void
    {
        $this->putJson('/api/tasks/999', ['title' => 'Любое'])->assertNotFound();
    }

    /*
    * DELETE /api/tasks/{id}
    */

    public function test_task_can_be_deleted(): void
    {
        $task = Task::factory()->create();

        $this->deleteJson("/api/tasks/{$task->id}")
            ->assertOk()
            ->assertJson(['message' => 'Task deleted successfully']);

        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }

    public function test_delete_returns_404_for_unknown_task(): void
    {
        $this->deleteJson('/api/tasks/999')->assertNotFound();
    }
}