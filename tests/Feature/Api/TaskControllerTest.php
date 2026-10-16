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

    /*
    * GET /api/tasks — список
    */
    public function test_tasks_list_is_paginated_with_default_per_page(): void
    {
        Task::factory()->count(20)->create();

        $response = $this->getJson('/api/tasks');

        $response->assertOk()
            ->assertJsonStructure([
                'data'  => [['id', 'title', 'description', 'due_date', 'create_date', 'status', 'priority', 'category']],
                'links' => ['first', 'last', 'prev', 'next'],
                'meta'  => ['current_page', 'per_page', 'total', 'last_page'],
            ])
            ->assertJsonPath('meta.total', 20)
            ->assertJsonPath('meta.per_page', 15)
            ->assertJsonPath('meta.current_page', 1)
            ->assertJsonPath('meta.last_page', 2)
            ->assertJsonCount(15, 'data');
    }

    public function test_tasks_list_respects_per_page_parameter(): void
    {
        Task::factory()->count(20)->create();

        $response = $this->getJson('/api/tasks?per_page=5');

        $response->assertOk()
            ->assertJsonPath('meta.per_page', 5)
            ->assertJsonPath('meta.last_page', 4)
            ->assertJsonCount(5, 'data');
    }

    public function test_tasks_list_can_search_by_title_substring(): void
    {
        Task::factory()->create(['title' => 'Подготовить отчёт за квартал']);
        Task::factory()->create(['title' => 'Купить продукты']);
        Task::factory()->create(['title' => 'Позвонить маме']);

        $response = $this->getJson('/api/tasks?search=отчёт');

        $response->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Подготовить отчёт за квартал');
    }

    public function test_tasks_list_returns_empty_when_search_matches_nothing(): void
    {
        Task::factory()->count(3)->create();

        $response = $this->getJson('/api/tasks?search=несуществующийтекст');

        $response->assertOk()
            ->assertJsonPath('meta.total', 0)
            ->assertJsonCount(0, 'data');
    }

    public function test_tasks_list_is_sorted_by_due_date_ascending_by_default(): void
    {
        Task::factory()->create(['title' => 'Позже', 'due_date' => '2026-12-10 12:00:00']);
        Task::factory()->create(['title' => 'Раньше', 'due_date' => '2026-12-01 12:00:00']);
        Task::factory()->create(['title' => 'Середина', 'due_date' => '2026-12-05 12:00:00']);

        $response = $this->getJson('/api/tasks');

        $response->assertOk()
            ->assertJsonPath('data.0.title', 'Раньше')
            ->assertJsonPath('data.1.title', 'Середина')
            ->assertJsonPath('data.2.title', 'Позже');
    }

    public function test_tasks_list_can_be_sorted_by_created_at(): void
    {
        Task::factory()->create(['title' => 'Старая', 'created_at' => '2026-10-01 10:00:00']);
        Task::factory()->create(['title' => 'Новая', 'created_at' => '2026-10-15 10:00:00']);

        $response = $this->getJson('/api/tasks?sort=created_at');

        $response->assertOk()
            ->assertJsonPath('data.0.title', 'Старая')
            ->assertJsonPath('data.1.title', 'Новая');
    }

    #[DataProvider('invalidListQueryProvider')]
    public function test_list_validates_query_params(array $query, string $expectedErrorField): void
    {
        $this->getJson('/api/tasks?' . http_build_query($query))
            ->assertUnprocessable()
            ->assertJsonValidationErrors($expectedErrorField);
    }

    /**
     * @return array<string, array{array<string, mixed>, string}>
     */
    public static function invalidListQueryProvider(): array
    {
        return [
            'sort not allowed' => [
                ['sort' => 'title'],
                'sort',
            ],
            'per_page too big' => [
                ['per_page' => 999],
                'per_page',
            ],
            'per_page not integer' => [
                ['per_page' => 'abc'],
                'per_page',
            ],
            'per_page zero' => [
                ['per_page' => 0],
                'per_page',
            ],
            'search too long' => [
                ['search' => str_repeat('a', 256)],
                'search',
            ],
        ];
    }
}