<?php
namespace App\Enums;

enum ProductEnum: int {
    case Egg = 1;

    public function label(): string
    {
        return match ($this) {
            self::Egg => 'Egg',
        };
    }
}
