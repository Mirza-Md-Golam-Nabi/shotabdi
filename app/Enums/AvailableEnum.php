<?php
namespace App\Enums;

enum AvailableEnum: int {
    case Inactive = 0;
    case Active   = 1;
    case Finished = 2;

    public function description(): string
    {
        return match ($this) {
            self::Inactive => 'Inactive',
            self::Active => 'Active',
            self::Finished => 'Finished',
        };
    }
}
