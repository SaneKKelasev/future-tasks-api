<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListTasksRequest extends FormRequest
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
            'search'    => ['nullable', 'string', 'max:255'],
            'sort'      => ['nullable', 'string', Rule::in(['due_date', 'created_at'])],
            'page'      => ['nullable', 'integer', 'min:1'],
            'per_page'  => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'sort.in'       => 'Сортировка возможна только по due_date или created_at.',
            'per_page.max'  => 'Максимум 100 записей на страницу.',
            'per_page.min'  => 'Минимум 1 запись на страницу.',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function validated($key = null, $default = null): mixed
    {
        $data = parent::validated();

        return array_merge([
            'sort'      => 'due_date',
            'per_page'  => 15,
        ], $data);
    }
}