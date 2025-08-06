<?php
namespace App\Filament\Resources\ProductResource\Pages;

use Filament\Actions;
use Filament\Tables\Table;
use App\Filament\Resources\ProductResource;
use Filament\Resources\Pages\ManageRecords;

class ManageProducts extends ManageRecords
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->createAnother(false),
        ];
    }
}
