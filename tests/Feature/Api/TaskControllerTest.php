<?php

namespace Tests\Feature\Api;

use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskControllerTest extends TestCase
{
    use RefreshDatabase;

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
            ->assertJsonStructure(['id', 'message'])
            ->assertJson(['message' => 'Task created successfully']);

        $this->assertDatabaseHas('tasks', [
            'title'         => 'Новая задача',
            'description'   => 'Описание задачи',
            'priority'      => 'high',
            'category'      => 'Работа',
            'status'        => 'pending', // дефолт из миграции
        ]);
    }
}