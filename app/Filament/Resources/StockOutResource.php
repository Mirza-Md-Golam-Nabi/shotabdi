<?php
namespace App\Filament\Resources;

use App\Enums\CustomerEnum;
use App\Filament\Forms\CustomerForm;
use App\Filament\Resources\StockOutResource\Pages;
use App\Filament\Traits\HasAmountCalculation;
use App\Models\Customer;
use App\Models\Product;
use App\Models\StockOut;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class StockOutResource extends Resource
{
    use HasAmountCalculation;

    protected static ?string $model = StockOut::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema(self::formData())
            ->columns('full');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns(self::tableData())
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
            'index'  => Pages\CreateStockOut::route('/'),
            'list'   => Pages\ListStockOuts::route('/list'),
            'create' => Pages\CreateStockOut::route('/create'),
            'edit'   => Pages\EditStockOut::route('/{record}/edit'),
        ];
    }

    public static function formData(): array
    {
        return [
            DatePicker::make('date')
                ->label('তারিখ')
                ->default(fn() => request()->query('date'))
                ->required(),
            Repeater::make('stock_outs')
                ->schema([
                    Select::make('customer_id')
                        ->label('কাস্টমার নাম')
                        ->placeholder('Select Customer')
                    // ->relationship('customer', 'name')
                        ->options(Customer::selectOption(type: CustomerEnum::stockOutType()))
                        ->required()
                        ->searchable()
                    // ->preload()
                        ->columnSpan('full')
                        ->createOptionForm(CustomerForm::fields())
                        ->createOptionUsing(function (array $data) {
                            $customer = Customer::create($data);
                            return $customer->getKey();
                        }),

                    Select::make('product_id')
                        ->label('পণ্যের নাম')
                        ->placeholder('Select')
                    // ->relationship('customer', 'name')
                        ->options(Product::selectOption())
                        ->required()
                        ->searchable()
                    // ->preload()
                        ->columnSpan('full'),

                    TextInput::make('quantity')
                        ->label('পরিমান')
                        ->required()
                        ->numeric()
                        ->columnSpan(1)
                        ->live(onBlur: true) // শুধু ফোকাস সরালে আপডেট হবে
                        ->afterStateUpdated(function ($set, $get) {
                            self::stockOutUpdateAmount($set, $get);
                        }),

                    TextInput::make('rate')
                        ->label('রেট')
                        ->required()
                        ->numeric()
                        ->columnSpan(1)
                        ->live(onBlur: true) // শুধু ফোকাস সরালে আপডেট হবে
                        ->afterStateUpdated(function ($set, $get) {
                            self::stockOutUpdateAmount($set, $get);
                        }),

                    TextInput::make('discount')
                        ->label('ডিস্কাউন্ট')
                        ->numeric()
                        ->columnSpan(1)
                        ->live(onBlur: true) // শুধু ফোকাস সরালে আপডেট হবে
                        ->afterStateUpdated(function ($set, $get) {
                            self::stockOutUpdateAmount($set, $get);
                        }),

                    TextInput::make('deposit')
                        ->label('জমা')
                        ->numeric()
                        ->columnSpan(1)
                        ->live(onBlur: true) // শুধু ফোকাস সরালে আপডেট হবে
                        ->afterStateUpdated(function ($set, $get) {
                            self::stockOutUpdateAmount($set, $get);
                        }),

                    TextInput::make('amount')
                        ->label('টাকার পরিমান')
                        ->numeric()
                        ->disabled()
                        ->columnSpan(1),

                    DatePicker::make('next_date')
                        ->label('পরবর্তী তারিখ')
                        ->columnSpan(1),
                ])
                ->columns([
                    'default' => 2,
                ]),
        ];
    }

    public static function tableData(): array
    {
        return [
            TextColumn::make('date')
                ->date()
                ->sortable(),
            TextColumn::make('customer_id')
                ->numeric()
                ->sortable(),
            TextColumn::make('product_id')
                ->numeric()
                ->sortable(),
            TextColumn::make('quantity')
                ->numeric()
                ->sortable(),
            TextColumn::make('rate')
                ->numeric()
                ->sortable(),
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
            TextColumn::make('deleted_at')
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
        ];
    }
}
