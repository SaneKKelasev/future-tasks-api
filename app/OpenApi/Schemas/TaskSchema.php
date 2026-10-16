<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Task',
    required: ['id', 'title', 'due_date', 'create_date', 'status', 'priority'],
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'title', type: 'string', maxLength: 255, example: 'Задача1'),
        new OA\Property(property: 'description', type: 'string', nullable: true, example: 'Описание задачи'),
        new OA\Property(property: 'due_date', type: 'string', format: 'date-time', example: '2025-01-20T15:00:00+00:00'),
        new OA\Property(property: 'create_date', type: 'string', format: 'date-time', example: '2025-01-20T15:00:00+00:00'),
        new OA\Property(property: 'status', type: 'string', enum: ['pending', 'done'], example: 'pending'),
        new OA\Property(property: 'priority', type: 'string', enum: ['low', 'medium', 'high'], example: 'high'),
        new OA\Property(property: 'category', type: 'string', nullable: true, example: 'Работа'),
    ],
)]
class TaskSchema
{
}