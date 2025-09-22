<?php
namespace App\Filament\Forms;

use App\Enums\CustomerEnum;
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

            TextInput::make('balance')
                ->label('বর্তমান হিসাব')
                ->numeric()
                ->required()
                ->integer()
                ->maxValue(8388607)
                ->helperText('ভগ্নাংশ নাম্বার থেকে বিরত থাকুন, যেমন: 50.2')
                ->validationMessages([
                    'maxLength' => 'মান অবশ্যই 8,388,607 এর কম বা সমান হতে হবে।',
                ]),

            TextInput::make('mobile')
                ->label('ফোন নাম্বার')
                ->numeric()
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

            Radio::make('type')
                ->label('কাস্টমার ধরণ')
                ->options(CustomerEnum::options())
                ->inline()
                ->inlineLabel(false)
                ->default(CustomerEnum::Normal),
        ];
    }
}
