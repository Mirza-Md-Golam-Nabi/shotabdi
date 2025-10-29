<?php
namespace App\Filament\Resources;

use App\Enums\CustomerEnum;
use App\Filament\Resources\ExcludeCustomerIdResource\Pages;
use App\Models\Customer;
use App\Models\ExcludeCustomerId;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ExcludeCustomerIdResource extends Resource
{
    protected static ?string $model = ExcludeCustomerId::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Exclude Customer ID';

    protected static ?string $navigationGroup = 'Settings';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('type')
                    ->label('ধরণ')
                    ->placeholder('Select')
                    ->options(CustomerEnum::options())
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(fn(callable $set) => $set('customer_id', null))
                    ->columnSpan('full'),

                Select::make('customer_id')
                    ->label('কাস্টমার নাম')
                    ->placeholder('Select')
                    ->options(function (callable $get) {
                        $type = $get('type');
                        return Customer::selectOption(type: $type);
                    })
                    ->required()
                    ->searchable()
                    ->columnSpan('full'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('customer.name')
                    ->label('কাস্টমার নাম')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('customer.type')
                    ->label('ধরণ')
                    ->formatStateUsing(fn(CustomerEnum $state) => $state->bangla())
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageExcludeCustomerIds::route('/'),
        ];
    }
}
