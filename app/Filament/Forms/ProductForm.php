<?php
namespace App\Filament\Forms;

use Filament\Forms\Components\TextInput;

class ProductForm
{
    public static function fields(): array
    {
        return [
            TextInput::make('name')
                ->label('প্রোডাক্ট নাম')
                ->required()
                ->maxLength(255),

            TextInput::make('quantity')
                ->label('বর্তমান স্টক (বস্তা/খাঁচি)')
                ->afterStateHydrated(function ($component, $record) {
                    $component->state($record->stock?->quantity ?? 0);
                }),
        ];
    }
}
