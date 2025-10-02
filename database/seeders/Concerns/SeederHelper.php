<?php
namespace Database\Seeders\Concerns;

use App\Enums\CustomerEnum;

trait SeederHelper
{
    protected function customerList(): array
    {
        return [
            [
                'name'   => 'Company',
                'mobile' => '01712457896',
                'type'   => CustomerEnum::Company,
            ],
            [
                'name'   => 'Farmer',
                'mobile' => '01854457896',
                'type'   => CustomerEnum::Farmer,
            ],
            [
                'name'   => 'Normal',
                'mobile' => '01365457896',
                'type'   => CustomerEnum::Normal,
            ],
            [
                'name'   => 'Egg Seller',
                'mobile' => '01958457896',
                'type'   => CustomerEnum::EggSeller,
            ],
            [
                'name'   => 'Bank',
                'mobile' => '01748457896',
                'type'   => CustomerEnum::Bank,
            ],
            [
                'name'   => 'Other',
                'mobile' => '01654457896',
                'type'   => CustomerEnum::Others,
            ],
        ];
    }

    protected function productList(): array
    {
        return [
            'ডিম',
            'সোনালী ফিড',
            'সোনালী ম্যাশ ফিড',
        ];
    }
}
