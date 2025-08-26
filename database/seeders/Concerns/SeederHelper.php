<?php
namespace Database\Seeders\Concerns;

use App\Enums\CustomerEnum;

trait SeederHelper
{
    protected function customerList(): array
    {
        return [
            [
                'name'   => 'কাউন এগ্রো ইন্ডা লিঃ',
                'mobile' => '01712457896',
                'type'   => CustomerEnum::COMPANY,
            ],
            [
                'name'   => 'হাসিব ভাই',
                'mobile' => '01712457885',
            ],
            [
                'name'   => 'শফিক চাচা',
                'mobile' => '01712457844',
            ],
            [
                'name'   => 'রতন ভাই',
                'mobile' => '01712457874',
            ],
            [
                'name'   => 'সুলতান চাচা',
                'mobile' => '01712457884',
            ],
            [
                'name'   => 'রফিক কাকা',
                'mobile' => '01712457811',
            ],
            [
                'name'   => 'রহিম ভাই',
                'mobile' => '01712457814',
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
