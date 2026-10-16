<?php

namespace App\Models;

use App\Enums\Priority;
use App\Enums\Status;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    /** @use HasFactory<\Database\Factories\TaskFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'description',
        'due_date',
        'status',
        'priority',
        'category',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'due_date'  => 'datetime',
            'status'    => Status::class,
            'priority'  => Priority::class,
        ];
    }
}