<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'Future Tasks API',
    description: 'REST API для управления списком задач. Тестовое задание для Future',
)]
#[OA\Server(
    url: 'http://localhost:8080',
    description: 'Локальный сервер разработки',
)]
#[OA\Tag(
    name: 'Tasks',
    description: 'Операции с задачами',
)]
class OpenApi
{
}