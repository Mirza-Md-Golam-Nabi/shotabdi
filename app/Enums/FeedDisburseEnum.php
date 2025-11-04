<?php
namespace App\Enums;

enum FeedDisburseEnum: int {
    case Pending   = 1;
    case Delivered = 2;
    case Cancel    = 3;

    public function label(): string
    {
        return match ($this) {
            self::Pending   => 'Pending',
            self::Delivered => 'Delivered',
            self::Cancel    => 'Cancel',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending   => 'primary',
            self::Delivered => 'success',
            self::Cancel    => 'danger',
        };
    }
}
