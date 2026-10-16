<?php

namespace App\Http\Requests;

use App\Enums\Priority;
use App\Enums\Status;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTaskRequest extends FormRequest
{
    /**
     * Аутентификации в проекте нет, поэтому разрешаем всем.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title'         => ['required', 'string', 'max:255'],
            'description'   => ['nullable', 'string'],
            'due_date'      => ['required', 'date'],
            'status'        => ['sometimes', 'string', Rule::enum(Status::class)],
            'priority'      => ['sometimes', 'string', Rule::enum(Priority::class)],
            'category'      => ['nullable', 'string', 'max:100'],
        ];
    }

    /**
     * Кастомные сообщения об ошибках.
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required'    => 'Название задачи обязательно.',
            'title.max'         => 'Название не должно превышать 255 символов.',
            'due_date.required' => 'Срок выполнения обязателен.',
            'due_date.date'     => 'Срок выполнения должен быть корректной датой.',
            'status.*'          => 'Недопустимый статус задачи.',
            'priority.*'        => 'Недопустимый приоритет задачи.',
            'category.max'      => 'Категория не должна превышать 100 символов.',
        ];
    }
}