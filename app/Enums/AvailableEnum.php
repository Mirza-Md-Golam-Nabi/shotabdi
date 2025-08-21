<?php
namespace App\Enums;

enum AvailableEnum: int {
    case INACTIVE = 0;
    case ACTIVE   = 1;
    case FINISHED = 2;

    public function description(): string
    {
        return match ($this) {
            self::INACTIVE => 'Inactive',
            self::ACTIVE => 'Active',
            self::FINISHED => 'Finished',
        };
    }
}
