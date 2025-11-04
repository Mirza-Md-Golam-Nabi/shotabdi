<?php
namespace App\Enums;

enum CustomerEnum: int {
    case Normal    = 1;
    case Farmer    = 2;
    case Company   = 3;
    case EggSeller = 4;
    case Others    = 5;
    case Bank      = 6;

    public function label(): string
    {
        return match ($this) {
            self::Normal    => 'Normal',
            self::Farmer    => 'Farmer',
            self::Company   => 'Company',
            self::EggSeller => 'Egg Seller',
            self::Others    => 'Others',
            self::Bank      => 'Bank',
        };
    }

    public function bangla(): string
    {
        return match ($this) {
            self::Normal    => 'সাধারণ',
            self::Farmer    => 'খামারি',
            self::Company   => 'কোম্পানি',
            self::EggSeller => 'ডিম বিক্রেতা',
            self::Others    => 'অন্যান্য',
            self::Bank      => 'ব্যাংক',
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

    public static function stockOutType(): array
    {
        return [
            self::Normal->value,
            self::Farmer->value,
            self::EggSeller->value,
        ];
    }

    public function color(): string
    {
        return match ($this) {
            self::Normal    => 'primary',
            self::Farmer    => 'success',
            self::Company   => 'danger',
            self::EggSeller => 'info',
            self::Others    => 'gray',
            self::Bank      => 'danger',
        };
    }

    public function filamentColor(): string
    {
        return match ($this) {
            self::Normal    => 'text-primary-600 dark:text-primary-400',
            self::Farmer    => 'text-success-600 dark:text-success-400',
            self::Company   => 'text-danger-600 dark:text-danger-400',
            self::EggSeller => 'text-info-600 dark:text-info-400',
            self::Others    => 'text-gray-600 dark:text-gray-400',
            self::Bank      => 'text-danger-600 dark:text-danger-400',
        };
    }

    public function filamentBgColor(): string
    {
        return match ($this) {
            self::Normal    => 'bg-primary-50 dark:bg-primary-900/20',
            self::Farmer    => 'bg-success-50 dark:bg-success-900/20',
            self::Company   => 'bg-danger-50 dark:bg-danger-900/20',
            self::EggSeller => 'bg-info-50 dark:bg-info-900/20',
            self::Others    => 'bg-gray-50 dark:bg-gray-900/20',
            self::Bank      => 'bg-danger-50 dark:bg-danger-900/20',
        };
    }

    public function filamentBorderColor(): string
    {
        return match ($this) {
            self::Normal    => 'border-primary-200 dark:border-primary-700',
            self::Farmer    => 'border-success-200 dark:border-success-700',
            self::Company   => 'border-danger-200 dark:border-danger-700',
            self::EggSeller => 'border-info-200 dark:border-info-700',
            self::Others    => 'border-gray-200 dark:border-gray-700',
            self::Bank      => 'border-danger-200 dark:border-danger-700',
        };
    }
}
