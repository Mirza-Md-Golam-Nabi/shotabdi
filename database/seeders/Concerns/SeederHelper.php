<?php
namespace Database\Seeders\Concerns;

trait SeederHelper
{
    protected function customerList(): array
    {
        return [
            [
                'name'   => 'আসলাম কাকা',
                'mobile' => '01712457896',
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

    protected function tranTypeList(): array
    {
        return [
            'Product',
            'Egg',
            'Deposit',
            'Cashback',
        ];
    }

    protected function cashFlowList(): array
    {
        return [
            'Deposit',
            'Expense',
        ];
    }
}
