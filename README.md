# Future Tasks API

REST API для управления списком задач. Тестовое задание для компании Future.

## Стек

- PHP 8.3
- Laravel 13
- MySQL 8.0
- Nginx 1.27 + PHP-FPM 8.3
- Docker Compose v2+

## Требования

- Docker 24+
- Docker Compose v2+

## Быстрый старт

```bash
# 1. Клонировать репозиторий
git clone https://github.com/SaneKKelasev/future-tasks-api.git
cd future-tasks-api

# 2. Создать .env из шаблона и заполнить пароли
cp .env.example .env

# 3. Собрать и запустить контейнеры
docker compose up -d --build

# 4. Сгенерировать ключ приложения
docker compose exec app php artisan key:generate

# 5. Применить миграции и заполнить демо-данными
docker compose exec app php artisan migrate:fresh --seed
```

После этого API доступен по адресу **http://localhost:8080/api/tasks**.

## Документация API

Swagger UI: **http://localhost:8081**

OpenAPI-спецификация: [`openapi.yaml`](./openapi.yaml)

### Эндпоинты

| Метод | URL | Описание |
|---|---|---|
| POST | `/api/tasks` | Создание задачи |
| GET | `/api/tasks` | Список задач с поиском, сортировкой и пагинацией |
| GET | `/api/tasks/{id}` | Получение задачи по ID |
| PUT | `/api/tasks/{id}` | Обновление задачи |
| DELETE | `/api/tasks/{id}` | Удаление задачи |

### Параметры списка задач

| Параметр | Тип | Описание |
|---|---|---|
| `search` | string (≤255) | Поиск по подстроке в названии |
| `sort` | `due_date` \| `created_at` | Поле сортировки (по умолчанию `due_date`) |
| `page` | integer ≥1 | Номер страницы |
| `per_page` | integer 1..100 | Записей на странице (по умолчанию 15) |

### Примеры запросов

**Создание задачи:**

```bash
curl -X POST http://localhost:8080/api/tasks \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Задача1",
    "description": "Описание задачи",
    "due_date": "2025-01-20T15:00:00",
    "priority": "high",
    "category": "Работа"
  }'
```

**Список с поиском и сортировкой:**

```bash
curl "http://localhost:8080/api/tasks?search=отчёт&sort=due_date&per_page=5"
```

## Тестирование

### Как я тестировал

Использовал **PHPUnit.
Тесты находятся в `tests/Feature/Api/TaskControllerTest.php`.

Проверяется:
- Создание задачи (happy path + 4 кейса валидации через `DataProvider`).
- Пагинация списка/
- Поиск по подстроке в названии.
- Сортировка по `due_date` по умолчанию.
- Получение задачи по ID + 404 для несуществующей.
- Обновление задачи + 404.
- Удаление задачи + 404 + проверка отсутствия в БД.

### Запуск тестов

```bash
docker compose exec app php artisan test
```

Тесты используют **отдельную БД** `tasks_test` (настраивается в `phpunit.xml`),
которая изолирована от дев-базы `tasks`. Это защищает данные разработки
от стирания при `RefreshDatabase`.

### Ручное тестирование

- **Swagger UI** (`http://localhost:8081`) — интерактивная проверка эндпоинтов кнопкой `Try it out`.
- **curl** — быстрая проверка из терминала 
- **Postman / Insomnia** — если нужен продвинутый сценарий (цепочки запросов, переменные).

## Структура проекта

```
app/
├── Enums/                    # PHP enum'ы Status и Priority
├── Http/
│   ├── Controllers/Api/      # TaskController — тонкий HTTP-слой
│   ├── Middleware/           # ForceJsonUnicode — русский текст без \u-escape
│   ├── Requests/             # FormRequest'ы для валидации (Store/Update/List)
│   └── Resources/            # TaskResource — форматирование JSON-ответа
├── Models/Task.php           # Модель Eloquent с enum-кастами
├── OpenApi/                  # Атрибуты OpenAPI (Info, Schemas)
└── Services/TaskService.php  # Бизнес-логика (list/create/update/delete)

database/
├── factories/                # TaskFactory со state-методами
├── migrations/               # Миграция tasks
└── seeders/                  # TaskSeeder — демо-данные

docker/
├── nginx/default.conf        # Конфиг nginx
└── php/Dockerfile            # Multi-stage сборка PHP-FPM

tests/Feature/Api/            # Feature-тесты TaskController
openapi.yaml                  # Сгенерированная OpenAPI-спецификация
```

## Архитектурные решения

- **FormRequest** — валидация вынесена из контроллера. Контроллер не знает о правилах.
- **API Resource** — внутренняя схема БД не «протекает» наружу. `created_at` в модели отдаётся как `create_date` в API.
- **Service Layer** — бизнес-логика изолирована от HTTP. Тот же сервис можно переиспользовать из консольной команды или очереди.
- **PHP enums** — типизация значений `status` и `priority`. В БД строки, в коде — enum. Легко расширять без `ALTER TABLE`.
- **Multi-stage Dockerfile** — финальный образ не содержит dev-зависимостей.
- **Отдельная БД для тестов** — `RefreshDatabase` не трогает данные разработки.
