<?php
namespace App\Enums;

enum TransactionTypeEnum: int {
    case Feed    = 1;
    case Egg     = 2;
    case Deposit = 3;
    case Expense = 4;

    public function label(): string
    {
        return match ($this) {
            self::Feed    => 'Feed',
            self::Egg     => 'Egg',
            self::Deposit => 'Deposit',
            self::Expense => 'Expense',
        };
    }
}
