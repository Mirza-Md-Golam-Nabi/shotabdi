<?php
namespace App\Filament\Resources;

use App\Enums\BankTransactionEnum;
use App\Enums\CashFlowEnum;
use App\Filament\Resources\BankTransactionResource\Pages;
use App\Models\BankTransaction;
use App\Models\Customer;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BankTransactionResource extends Resource
{
    protected static ?string $model = BankTransaction::class;

    protected static ?string $navigationLabel = 'Bank Transaction';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?int $navigationSort = 6;

    protected static array $banks = [];

    protected static array $customer = [];

    public static function form(Form $form): Form
    {
        if (empty(self::$banks)) {
            self::$banks = Customer::bankOption();
        }

        if (empty(self::$customer)) {
            self::$customer = Customer::customerWithoutBank();
        }

        return $form
            ->schema([
                DatePicker::make('date')
                    ->label('তারিখ')
                    ->default(fn() => request()->query('date'))
                    ->required()
                    ->columnSpan(1),

                Select::make('type')
                    ->label('ধরণ')
                    ->options(BankTransactionEnum::options())
                    ->required()
                    ->reactive()
                    ->columnSpan(1),

                Select::make('from_bank')
                    ->label('ব্যাংক (from)')
                    ->options(self::$banks)
                    ->required()
                    ->columnSpan(1)
                    ->hidden(fn(callable $get) => $get('type') == BankTransactionEnum::CustomerToBank->value),

                Select::make('to_customer')
                    ->label('কাস্টমার (to)')
                    ->options(self::$customer)
                    ->required()
                    ->columnSpan(1)
                    ->hidden(fn(callable $get) => in_array($get('type'), [
                        BankTransactionEnum::CustomerToBank->value,
                        BankTransactionEnum::Self->value,
                    ])),

                Select::make('from_customer')
                    ->label('কাস্টমার (from)')
                    ->options(self::$customer)
                    ->required()
                    ->columnSpan(1)
                    ->visible(fn(callable $get) => $get('type') == BankTransactionEnum::CustomerToBank->value),

                Select::make('to_bank')
                    ->label('ব্যাংক (to)')
                    ->options(self::$banks)
                    ->required()
                    ->columnSpan(1)
                    ->visible(fn(callable $get) => $get('type') == BankTransactionEnum::CustomerToBank->value),

                Select::make('cash_flow_id')
                    ->label('লেনদেনের ধরণ (জমা বা উত্তোলন)')
                    ->options(CashFlowEnum::options('bank'))
                    ->required()
                    ->columnSpan(1)
                    ->visible(fn(callable $get) => $get('type') == BankTransactionEnum::Self->value),

                TextInput::make('amount')
                    ->label('টাকার পরিমান')
                    ->required()
                    ->numeric()
                    ->columnSpan(1),
            ])
            ->columns([
                'default' => 1,
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
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
            'index'  => Pages\CreateBankTransaction::route('/'),
            'list'   => Pages\ListBankTransactions::route('/list'),
            'create' => Pages\CreateBankTransaction::route('/create'),
            'edit'   => Pages\EditBankTransaction::route('/{record}/edit'),
        ];
    }
}
