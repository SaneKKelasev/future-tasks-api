<?php

namespace App\Enums;

enum Status: string
{
    case Pending    = 'pending';
    case Done       = 'done';

    public function label(): string
    {
        return match ($this) {
            self::Pending   => 'Не выполнена',
            self::Done      => 'Выполнена',
        };
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}