<?php
namespace App\Enums;

enum ProductEnum: int {
    case EGG = 1;

    public function label(): string
    {
        return match ($this) {
            self::EGG => 'Egg',
        };
    }
}
