<?php

namespace App\Filament\Auth;

use Filament\Forms\Form;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Auth\Login as BaseAuth;
use Illuminate\Validation\ValidationException;

class Login extends BaseAuth
{
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                $this->getLoginFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getRememberFormComponent(),
            ])
            ->statePath('data');
    }

    protected function getLoginFormComponent(): Component
    {
        return TextInput::make('mobile')
            ->label('Mobile Number')
            ->required()
            ->rule('regex:/^01[0-9]{9}$/')
            ->helperText('শুধু ইংরেজি ডিজিট ব্যবহার করুন, যেমন: 017XXXXXXXX')
            ->validationMessages([
                'regex' => 'ফোন নাম্বার অবশ্যই ইংরেজিতে ১১ ডিজিটের এবং ০১ দিয়ে শুরু হতে হবে।',
            ])
            ->autocomplete()
            ->autofocus();
    }

    protected function getCredentialsFromFormData(array $data): array
    {
        return [
            'mobile' => $data['mobile'],
            'password' => $data['password'],
        ];
    }

    protected function throwFailureValidationException(): never
    {
        throw ValidationException::withMessages([
            'data.mobile' => __('filament-panels::pages/auth/login.messages.failed'),
        ]);
    }
}
