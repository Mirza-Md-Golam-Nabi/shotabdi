<?php
namespace App\Enums;

enum CustomerEnum: int {
    case Normal    = 1;
    case Farmer    = 2;
    case Company   = 3;
    case EggSeller = 4;
    case Others    = 5;

    public function label(): string
    {
        return match ($this) {
            self::Normal    => 'Normal',
            self::Farmer    => 'Farmer',
            self::Company   => 'Company',
            self::EggSeller => 'Egg Seller',
            self::Others    => 'Others',
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

    public function color(): string
    {
        return match ($this) {
            self::Normal    => 'primary',
            self::Farmer    => 'success',
            self::Company   => 'danger',
            self::EggSeller => 'info',
            self::Others    => 'gray',
        };
    }
}
