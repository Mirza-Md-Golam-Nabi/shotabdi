<?php
namespace App\Filament\Resources\ExcludeCustomerIdResource\Pages;

use App\Filament\Resources\ExcludeCustomerIdResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageExcludeCustomerIds extends ManageRecords
{
    protected static string $resource = ExcludeCustomerIdResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Exclude Customer')
                ->createAnother(false)
                ->modalWidth('md')
                ->modalHeading('কাস্টমার বাদ দিন'),
        ];
    }
}
