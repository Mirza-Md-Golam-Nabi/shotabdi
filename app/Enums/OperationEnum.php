<?php
namespace App\Enums;

enum OperationEnum: string {
    case ADD      = 'add';
    case SUBTRACT = 'subtract';

    public function reverse(): self
    {
        return $this === self::ADD ? self::SUBTRACT : self::ADD;
    }
}
