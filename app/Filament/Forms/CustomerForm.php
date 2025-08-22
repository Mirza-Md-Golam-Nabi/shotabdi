<?php
namespace App\Filament\Forms;

use App\Enums\FarmerEnum;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\TextInput;

class CustomerForm
{
    public static function fields(): array
    {
        return [
            TextInput::make('name')
                ->label('নাম')
                ->required()
                ->maxLength(255),

            TextInput::make('mobile')
                ->label('ফোন নাম্বার')
                ->nullable()
                ->length(11)
                ->rule('regex:/^01[0-9]{9}$/')
                ->helperText('শুধু ইংরেজি ডিজিট ব্যবহার করুন, যেমন: 017XXXXXXXX')
                ->validationMessages([
                    'length' => 'ফোন নাম্বার অবশ্যই ১১ সংখ্যার হতে হবে।',
                    'regex'  => 'ফোন নাম্বার অবশ্যই ইংরেজিতে ১১ ডিজিটের এবং ০১ দিয়ে শুরু হতে হবে।',
                ]),

            TextInput::make('address')
                ->label('ঠিকানা')
                ->maxLength(255),

            Radio::make('is_farmer')
                ->label('খামারি')
                ->options(FarmerEnum::options())
                ->inline()
                ->inlineLabel(false)
                ->default('0'),

        ];
    }
}
