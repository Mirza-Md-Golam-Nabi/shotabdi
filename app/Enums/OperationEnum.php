<?php
namespace App\Enums;

enum OperationEnum: string {
    case Add      = 'add';
    case Subtract = 'subtract';

    public function reverse(): self
    {
        return $this === self::Add ? self::Subtract : self::Add;
    }
}
