<?php

namespace App\Http\Requests;

use App\Enums\Priority;
use App\Enums\Status;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTaskRequest extends FormRequest
{
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
            'title'         => ['sometimes', 'required', 'string', 'max:255'],
            'description'   => ['nullable', 'string'],
            'due_date'      => ['sometimes', 'required', 'date'],
            'status'        => ['sometimes', 'string', Rule::enum(Status::class)],
            'priority'      => ['sometimes', 'string', Rule::enum(Priority::class)],
            'category'      => ['nullable', 'string', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required'    => 'Название задачи не может быть пустым.',
            'title.max'         => 'Название не должно превышать 255 символов.',
            'due_date.required' => 'Срок выполнения не может быть пустым.',
            'due_date.date'     => 'Срок выполнения должен быть корректной датой.',
            'status.*'          => 'Недопустимый статус задачи.',
            'priority.*'        => 'Недопустимый приоритет задачи.',
        ];
    }
}