<?php

namespace Database\Seeders;

use App\Enums\Priority;
use App\Enums\Status;
use App\Models\Task;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    public function run(): void
    {
        $demos = [
            [
                'title'         => 'Подготовить отчёт за квартал',
                'description'   => 'Собрать данные из CRM и оформить презентацию.',
                'due_date'      => now()->addDays(3),
                'status'        => Status::Pending,
                'priority'      => Priority::High,
                'category'      => 'Работа',
            ],
            [
                'title'         => 'Купить продукты на неделю',
                'description'   => null,
                'due_date'      => now()->addDay(),
                'status'        => Status::Pending,
                'priority'      => Priority::Medium,
                'category'      => 'Дом',
            ],
            [
                'title'         => 'Записаться к стоматологу',
                'description'   => 'Плановый осмотр раз в полгода.',
                'due_date'      => now()->addWeek(),
                'status'        => Status::Pending,
                'priority'      => Priority::Low,
                'category'      => 'Личное',
            ],
            [
                'title'         => 'Сделать code review для коллеги',
                'description'   => 'Пул-реквест №1284, модуль оплаты.',
                'due_date'      => now()->subDay(),
                'status'        => Status::Done,
                'priority'      => Priority::Medium,
                'category'      => 'Работа',
            ],
        ];

        foreach ($demos as $task) {
            Task::create($task);
        }

        Task::factory()->count(26)->create();
    }
}