<?php
namespace App\Filament\Resources;

use App\Filament\Forms\CustomerForm;
use App\Filament\Resources\TransactionResource\Pages;
use App\Models\Customer;
use App\Models\Transaction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                DatePicker::make('date')
                    ->label('তারিখ')
                    ->default(fn() => request()->query('date'))
                    ->required(),
                Repeater::make('transactions')
                    ->schema([
                        Select::make('customer_id')
                            ->label('কাস্টমার নাম')
                        // ->relationship('customer', 'name')
                            ->options(Customer::selectOption())
                            ->required()
                            ->searchable()
                        // ->preload()
                            ->columnSpan('full')
                            ->createOptionForm(CustomerForm::fields())
                            ->createOptionUsing(function (array $data) {
                                $customer = Customer::create($data);
                                return $customer->getKey();
                            }),
                        TextInput::make('deposit_amount')
                            ->label('জমা')
                            ->numeric()
                            ->columnSpan(1),
                        TextInput::make('expense_amount')
                            ->label('খরচ')
                            ->numeric()
                            ->columnSpan(1),
                    ])
                    ->columns([
                        'default' => 2,
                    ]),
            ])
            ->columns('full');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('customer.name')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('date')
                    ->date()
                    ->sortable(),
                TextColumn::make('type')
                    ->searchable(),
                TextColumn::make('amount')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\CreateTransaction::route('/'),
            'list'   => Pages\ListTransactions::route('/list'),
            'create' => Pages\CreateTransaction::route('/create'),
            'edit'   => Pages\EditTransaction::route('/{record}/edit'),
        ];
    }
}
