<?php
namespace App\Filament\Resources;

use App\Filament\Resources\StockInResource\Pages;
use App\Models\Customer;
use App\Models\Product;
use App\Models\StockIn;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class StockInResource extends Resource
{
    protected static ?string $model = StockIn::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

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
            'index'  => Pages\CreateStockIn::route('/'),
            'list'   => Pages\ListStockIns::route('/list'),
            'create' => Pages\CreateStockIn::route('/create'),
            'edit'   => Pages\EditStockIn::route('/{record}/edit'),
        ];
    }

    public static function formData(): array
    {
        return [
            DatePicker::make('date')
                ->label('তারিখ')
                ->default(fn() => request()->query('date'))
                ->required(),
            Repeater::make('stock_ins')
                ->schema([
                    Select::make('customer_id')
                        ->label('কাস্টমার নাম')
                        ->placeholder('Select')
                    // ->relationship('customer', 'name')
                        ->options(Customer::pluck('name', 'id'))
                        ->required()
                        ->searchable()
                    // ->preload()
                        ->columnSpan('1')
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
                                    'regex'  => 'ফোন নাম্বার অবশ্যই ইংরেজিতে ১১ ডিজিটের এবং ০১ দিয়ে শুরু হতে হবে।',
                                ]),
                            TextInput::make('address')
                                ->label('ঠিকানা')
                                ->maxLength(255),
                        ])
                        ->createOptionUsing(function (array $data) {
                            $customer = Customer::create($data);
                            return $customer->getKey();
                        }),
                    Select::make('product_id')
                        ->label('পণ্যের নাম')
                        ->placeholder('Select')
                    // ->relationship('customer', 'name')
                        ->options(Product::pluck('name', 'id'))
                        ->required()
                        ->searchable()
                    // ->preload()
                        ->columnSpan('1')
                        ->createOptionForm([
                            TextInput::make('name')
                                ->required()
                                ->maxLength(255),
                        ])
                        ->createOptionUsing(function (array $data) {
                            $product = Product::create($data);
                            return $product->getKey();
                        }),
                    TextInput::make('quantity')
                        ->label('পরিমান')
                        ->required()
                        ->numeric()
                        ->columnSpan(1)
                        ->live(onBlur: true) // শুধু ফোকাস সরালে আপডেট হবে
                        ->afterStateUpdated(function ($state, $set, $get) {
                            self::updateAmount($set, $get);
                        }),
                    TextInput::make('rate')
                        ->label('রেট')
                        ->required()
                        ->numeric()
                        ->columnSpan(1)
                        ->live(onBlur: true) // শুধু ফোকাস সরালে আপডেট হবে
                        ->afterStateUpdated(function ($state, $set, $get) {
                            self::updateAmount($set, $get);
                        }),
                    TextInput::make('discount')
                        ->label('ডিস্কাউন্ট')
                        ->numeric()
                        ->columnSpan(1)
                        ->live(onBlur: true) // শুধু ফোকাস সরালে আপডেট হবে
                        ->afterStateUpdated(function ($state, $set, $get) {
                            self::updateAmount($set, $get);
                        }),
                    TextInput::make('deposit')
                        ->label('জমা')
                        ->numeric()
                        ->columnSpan(1)
                        ->live(onBlur: true) // শুধু ফোকাস সরালে আপডেট হবে
                        ->afterStateUpdated(function ($state, $set, $get) {
                            self::updateAmount($set, $get);
                        }),
                    TextInput::make('cashback')
                        ->label('ফেরত')
                        ->numeric()
                        ->columnSpan(1)
                        ->live(onBlur: true) // শুধু ফোকাস সরালে আপডেট হবে
                        ->afterStateUpdated(function ($state, $set, $get) {
                            self::updateAmount($set, $get);
                        }),
                    TextInput::make('amount')
                        ->label('টাকার পরিমান')
                        ->numeric()
                        ->readOnly()
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

    private static function updateAmount($set, $get)
    {
        $quantity = $get('quantity') ?? 0;
        $rate     = $get('rate') ?? 0;
        $discount = $get('discount') ?? 0;
        $deposit  = $get('deposit') ?? 0;
        $cashback = $get('cashback') ?? 0;

        $amount = ($quantity * $rate) + $deposit - $discount - $cashback;
        $set('amount', $amount);
    }
}
