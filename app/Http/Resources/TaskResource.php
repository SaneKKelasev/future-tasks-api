<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read \App\Models\Task $resource
 */
class TaskResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'title'         => $this->title,
            'description'   => $this->description,
            'due_date'      => $this->due_date->toIso8601String(),
            'create_date'   => $this->created_at->toIso8601String(),
            'status'        => $this->status->value,
            'priority'      => $this->priority->value,
            'category'      => $this->category,
        ];
    }
}