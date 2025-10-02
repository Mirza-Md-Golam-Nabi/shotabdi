<?php
namespace App\Enums;

enum BankTransactionEnum: int {
    case BankToCustomer = 1;
    case CustomerToBank = 2;
    case Self           = 3;

    public function label(): string
    {
        return match ($this) {
            self::BankToCustomer => 'Bank To Customer',
            self::CustomerToBank => 'Customer To Bank',
            self::Self           => 'Self',
        };
    }

    public function bangla(): string
    {
        return match ($this) {
            self::BankToCustomer => 'ব্যাংক টু কাস্টমার',
            self::CustomerToBank => 'কাস্টমার টু ব্যাংক',
            self::Self           => 'নিজ',
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
