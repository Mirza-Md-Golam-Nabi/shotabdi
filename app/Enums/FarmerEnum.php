<?php
namespace App\Enums;

enum FarmerEnum: int {
    case NO  = 0;
    case YES = 1;

    public function description(): string
    {
        return match ($this) {
            self::NO => 'No',
            self::YES => 'Yes',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(function ($case) {
                return [
                  $case->value => $case->description(),
                ];
            })
            ->toArray();
    }
}
