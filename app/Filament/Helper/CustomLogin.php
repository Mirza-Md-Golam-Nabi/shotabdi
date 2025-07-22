<?php

namespace App\Filament\Helper;

use Filament\Forms\Form;
use Filament\Pages\Auth\Login;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\TextInput;

class CustomLogin extends Login
{
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('mobile')
                    ->label('Mobile Number')
                    ->required()
                    ->autocomplete()
                    ->autofocus(),
                TextInput::make('password')
                    ->password()
                    ->autocomplete('current-password')
                    ->required(),
                Checkbox::make('remember')
            ]);
    }

    protected function getCredentialsFromFormData(array $data): array
    {
        return [
            'mobile' => $data['mobile'],
            'password' => $data['password'],
        ];
    }
}
