<?php

namespace Database\Factories;

use App\Enums\Priority;
use App\Enums\Status;
use App\Models\Task;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Task>
 */
class TaskFactory extends Factory
{
    protected $model = Task::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title'         => $this->faker->sentence(3),
            'description'   => $this->faker->optional()->paragraph(),
            'due_date'      => $this->faker->dateTimeBetween('now', '+1 month'),
            'status'        => $this->faker->randomElement(Status::cases()),
            'priority'      => $this->faker->randomElement(Priority::cases()),
            'category'      => $this->faker->randomElement(['Работа', 'Дом', 'Личное']),
        ];
    }

    /**
     * Статус: задача не выполнена.
     */
    public function pending(): static
    {
        return $this->state(fn () => ['status' => Status::Pending]);
    }

    /**
     * Статус: задача выполнена.
     */
    public function done(): static
    {
        return $this->state(fn () => ['status' => Status::Done]);
    }

    /**
    * Состояние: низкий приоритет.
    */
    public function lowPriority(): static
    {
        return $this->state(fn () => ['priority' => Priority::Low]);
    }

    /**
        * Состояние: средний приоритет.
    */
    public function mediumPriority(): static
    {
        return $this->state(fn () => ['priority' => Priority::Medium]);
    }

    /**
     * Состояние: высокий приоритет.
     */
    public function highPriority(): static
    {
        return $this->state(fn () => ['priority' => Priority::High]);
    }
}