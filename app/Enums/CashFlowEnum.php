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

    public function bank(): string
    {
        return match ($this) {
            self::Deposit => 'জমা',
            self::Expense => 'উত্তোলন',
        };
    }

    public static function options(string $type = 'bangla'): array
    {
        return collect(self::cases())
            ->mapWithKeys(function ($case) use ($type) {
                return [
                    $case->value => $type == 'bangla' ? $case->bangla() : $case->bank(),
                ];
            })
            ->toArray();
    }

    public function reverse(): self
    {
        return match ($this) {
            self::Deposit => self::Expense,
            self::Expense => self::Deposit,
        };
    }
}
