<?php
namespace App\Filament\Resources\StockOutResource\Pages;

use App\Enums\AvailableEnum;
use App\Enums\TransactionTypeEnum;
use App\Filament\Forms\CustomerForm;
use App\Filament\Resources\StockOutResource;
use App\Filament\Services\CustomerService;
use App\Filament\Traits\HandlesTransactions;
use App\Filament\Traits\HasAmountCalculation;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Stock;
use App\Models\StockIn;
use App\Models\Transaction;
use Filament\Actions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Pages\EditRecord;

class EditStockOut extends EditRecord
{
    use HasAmountCalculation, HandlesTransactions;

    protected static string $resource = StockOutResource::class;

    public ?string $date = null;

    public function mount($record): void
    {
        parent::mount($record);

        $rec = $this->record;

        $this->date = $rec->date;
        $factor     = $rec->product_id == 1 ? 30 : 1;
        $quantity   = $rec->quantity / $factor;

        $deposit = $this->tranBalance();

        $amount = round($rec->rate * $rec->quantity) - $rec->discount - $deposit;

        $this->form->fill([
             ...$this->form->getState(),
            'deposit'  => $deposit,
            'amount'   => $amount,
            'quantity' => $quantity,
        ]);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->stockOutRollback();

        $multiply = $data['product_id'] == 1 ? 30 : 1;

        $data['quantity'] *= $multiply;

        $data['amount'] = round($data['rate'] * $data['quantity']) - $data['discount'];

        return $data;
    }

    protected function afterSave(): void
    {
        $form_data = $this->form->getState();

        $multiply = $form_data['product_id'] == 1 ? 30 : 1;

        $form_data['quantity'] *= $multiply;

        $amount      = round($form_data['rate'] * $form_data['quantity']) - $form_data['discount'];
        $balance     = $amount - $form_data['deposit'];
        $customer_id = $form_data['customer_id'];

        $operation = Customer::find($customer_id)?->isEggSeller() ? 'subtract' : 'add';
        $this->updateCustomerBalance($customer_id, $balance, $operation);

        $this->updateStock($form_data);

        $this->saveStockOutTransaction($form_data, $amount, $this->record);
    }

    protected function updateStock(array $data)
    {
        $stock = Stock::where('product_id', $data['product_id'])->first();

        if ($stock->available > $data['quantity']) {
            $stock->decrement('available', $data['quantity']);
        } else {
            $remain = $data['quantity'] - $stock->available;

            StockIn::where('product_id', $data['product_id'])
                ->where('is_available', AvailableEnum::Active)
                ->update(['is_available' => AvailableEnum::Finished]);

            do {
                $stock_in = StockIn::where('product_id', $data['product_id'])
                    ->where('is_available', AvailableEnum::Inactive)
                    ->first();

                if ($remain > $stock_in->quantity) {
                    $stock_in->is_available = AvailableEnum::Finished;
                    $remain -= $stock_in->quantity;
                } elseif ($remain == $stock_in->quantity) {
                    $stock_in->is_available = AvailableEnum::Finished;
                    $stock_in->save();

                    $stock_in = StockIn::where('product_id', $data['product_id'])
                        ->where('is_available', AvailableEnum::Inactive)
                        ->first();

                    $stock_in->is_available = AvailableEnum::Active;
                    $stock->available       = $stock_in->quantity;
                    $stock->save();

                    $remain = 0;
                } else {
                    $stock_in->is_available = AvailableEnum::Active;
                    $stock->available       = $stock_in->quantity - $remain;
                    $stock->save();
                    $remain = 0;
                }

                $stock_in->save();

            } while ($remain != 0);

        }

        $stock->decrement('quantity', $data['quantity']);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                DatePicker::make('date')
                    ->label('তারিখ')
                    ->default(fn() => request()->query('date'))
                    ->required()
                    ->columnSpan('full'),

                Select::make('customer_id')
                    ->label('কাস্টমার নাম')
                    ->placeholder('Select')
                // ->relationship('customer', 'name')
                    ->options(Customer::pluck('name', 'id'))
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
                    ->options(Product::pluck('name', 'id'))
                    ->required()
                    ->searchable()
                // ->preload()
                    ->columnSpan('full')
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
            ])
            ->columns([
                'default' => 2,
                'lg'      => 2,
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->visible(false),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return url()->previous();
    }

    /**
     * Get transaction balance details
     *
     * @return float
     */
    protected function tranBalance(): float
    {
        return Transaction::where('stock_out_id', $this->record->id ?? 0)
            ->where('tran_type_id', TransactionTypeEnum::Deposit)
            ->sum('amount');
    }

    protected function stockOutRollback()
    {
        $rec         = $this->record; // stock_outs table data
        $rate        = $rec->rate;
        $quantity    = $rec->quantity;
        $discount    = $rec->discount;
        $product_id  = $rec->product_id;
        $customer_id = $rec->customer_id;

        $deposit = $this->tranBalance();

        $amount  = round($rate * $quantity) - $discount;
        $balance = $amount - $deposit;

        $operation = Customer::find($customer_id)?->isEggSeller() ? 'add' : 'subtract';
        $this->updateCustomerBalance($customer_id, $balance, $operation);

        $stock = Stock::where('product_id', $product_id)->first();

        $stock_in = StockIn::where('product_id', $product_id)
            ->where('is_available', AvailableEnum::Active)
            ->first();

        if (($stock_in->quantity - $stock->available) >= $quantity) {
            $stock->available = $stock->available + $quantity;
            $stock->quantity  = $stock->quantity + $quantity;
            $stock->save();
        } else {
            $stock_in->is_available = AvailableEnum::Inactive;
            $stock_in->save();

            $remain = $quantity - ($stock_in->quantity - $stock->available);

            while ($remain != 0) {
                $stock_in = StockIn::where('product_id', $product_id)
                    ->where('is_available', AvailableEnum::Finished)
                    ->orderBy('id', 'desc')
                    ->first();

                if ($remain > $stock_in->quantity) {
                    $remain = $remain - $stock_in->quantity;

                    $stock_in->is_available = AvailableEnum::Inactive;
                    $stock_in->save();
                } else {
                    $stock->available = $stock_in->quantity - $remain;
                    $stock->save();

                    $stock_in->is_available = AvailableEnum::Active;
                    $stock_in->save();

                    $remain = 0;
                }
            }

        }

        Transaction::where('stock_out_id', $rec->id)
            ->delete();
    }

    protected function updateCustomerBalance($customer_id, $balance, $operation = 'add')
    {
        (new CustomerService())->updateBalance($customer_id, $balance, $operation);
    }
}
