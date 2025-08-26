<?php
namespace App\Enums;

enum CustomerEnum: int {
    case NORMAL     = 1;
    case FARMER     = 2;
    case COMPANY    = 3;
    case EGG_SELLER = 4;
    case OTHERS     = 5;

    public function label(): string
    {
        return match ($this) {
            self::NORMAL => 'Normal',
            self::FARMER => 'Farmer',
            self::COMPANY => 'Company',
            self::EGG_SELLER => 'Egg Seller',
            self::OTHERS => 'Others',
        };
    }

    public function bangla(): string
    {
        return match ($this) {
            self::NORMAL => 'সাধারণ',
            self::FARMER => 'খামারি',
            self::COMPANY => 'কোম্পানি',
            self::EGG_SELLER => 'ডিম বিক্রেতা',
            self::OTHERS => 'অন্যান্য',
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
            self::NORMAL => 'primary',
            self::FARMER => 'success',
            self::COMPANY => 'danger',
            self::EGG_SELLER => 'info',
            self::OTHERS => 'gray',
        };
    }
}
