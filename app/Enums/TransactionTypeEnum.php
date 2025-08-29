<?php
namespace App\Enums;

enum TransactionTypeEnum: int {
    case FEED    = 1;
    case EGG     = 2;
    case DEPOSIT = 3;
    case EXPENSE = 4;

    public function label(): string
    {
        return match ($this) {
            self::FEED => 'Feed',
            self::EGG => 'Egg',
            self::DEPOSIT => 'Deposit',
            self::EXPENSE => 'Expense',
        };
    }
}
