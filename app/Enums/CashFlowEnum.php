<?php
namespace App\Enums;

enum CashFlowEnum: int {
    case DEPOSIT = 1;
    case EXPENSE = 2;

    public function description(): string
    {
        return match ($this) {
            self::DEPOSIT => 'Deposit',
            self::EXPENSE => 'Expense',
        };
    }

    public function bangla(): string
    {
        return match ($this) {
            self::DEPOSIT => 'জমা',
            self::EXPENSE => 'খরচ',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(function ($case) {
                return [
                    $case->value => $case->bangla(),
                ];
            })
            ->toArray();
    }
}
