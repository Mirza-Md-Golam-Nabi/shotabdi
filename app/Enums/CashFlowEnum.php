<?php
namespace App\Enums;

enum CashFlowEnum: int {
    case Deposit = 1;
    case Expense = 2;

    public function description(): string
    {
        return match ($this) {
            self::Deposit => 'Deposit',
            self::Expense => 'Expense',
        };
    }

    public function bangla(): string
    {
        return match ($this) {
            self::Deposit => 'জমা',
            self::Expense => 'খরচ',
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
