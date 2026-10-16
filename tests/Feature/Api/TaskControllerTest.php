<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Enums\Priority;
use App\Enums\Status;
use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class TaskControllerTest extends TestCase
{
    use RefreshDatabase;

    /*
    * POST /api/tasks — создание
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
            ->assertJsonStructure(['id', 'message'])
            ->assertJson(['message' => 'Task created successfully']);

        $this->assertDatabaseHas('tasks', [
            'title'         => 'Новая задача',
            'description'   => 'Описание задачи',
            'priority'      => 'high',
            'category'      => 'Работа',
            'status'        => 'pending',
        ]);
    }

    public function test_task_created_with_defaults_when_optional_fields_missing(): void
    {
        $payload = [
            'title'     => 'Минимальная задача',
            'due_date'  => '2026-12-01T10:00:00',
        ];

        $this->postJson('/api/tasks', $payload)->assertCreated();

        $this->assertDatabaseHas('tasks', [
            'title'         => 'Минимальная задача',
            'description'   => null,
            'category'      => null,
            'status'        => 'pending',
            'priority'      => 'medium',
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
        $validBase = [
            'title'     => 'Валидная задача',
            'due_date'  => '2026-12-01T10:00:00',
        ];

        return [
            'title missing' => [
                ['due_date' => '2026-12-01T10:00:00'],
                'title',
            ],
            'title empty' => [
                array_merge($validBase, ['title' => '']),
                'title',
            ],
            'title too long' => [
                array_merge($validBase, ['title' => str_repeat('a', 256)]),
                'title',
            ],
            'title wrong type' => [
                array_merge($validBase, ['title' => ['array']]),
                'title',
            ],
            'due_date missing' => [
                ['title' => 'Задача'],
                'due_date',
            ],
            'due_date invalid' => [
                array_merge($validBase, ['due_date' => 'not-a-date']),
                'due_date',
            ],
            'priority invalid' => [
                array_merge($validBase, ['priority' => 'urgent']),
                'priority',
            ],
            'status invalid' => [
                array_merge($validBase, ['status' => 'archived']),
                'status',
            ],
            'category too long' => [
                array_merge($validBase, ['category' => str_repeat('a', 101)]),
                'category',
            ],
        ];
    }
}