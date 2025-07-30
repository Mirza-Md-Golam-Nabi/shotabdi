<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Customer;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\Transaction;
use Filament\Resources\Resource;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Repeater;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\TransactionResource\Pages;
use App\Filament\Resources\TransactionResource\RelationManagers;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

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
                            ->options(Customer::pluck('name', 'id'))
                            ->required()
                            ->searchable()
                            // ->preload()
                            ->columnSpan('full')
                            ->createOptionForm([
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
                                        'regex' => 'ফোন নাম্বার অবশ্যই ইংরেজিতে ১১ ডিজিটের এবং ০১ দিয়ে শুরু হতে হবে।',
                                    ]),
                                TextInput::make('address')
                                    ->label('ঠিকানা')
                                    ->maxLength(255),
                            ])
                            ->createOptionUsing(function (array $data) {
                                $customer = Customer::create($data);
                                return $customer->getKey();
                            }),
                        TextInput::make('deposit_amount')
                            ->label('জমা')
                            ->numeric(),
                        TextInput::make('expense_amount')
                            ->label('খরচ')
                            ->numeric(),
                    ])
                    ->columns(2),
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
            'index' => Pages\CreateTransaction::route('/'),
            'list' => Pages\ListTransactions::route('/list'),
            'create' => Pages\CreateTransaction::route('/create'),
            'edit' => Pages\EditTransaction::route('/{record}/edit'),
        ];
    }
}
