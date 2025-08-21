<?php
namespace App\Filament\Resources\StockInResource\Pages;

use App\Enums\TransactionTypeEnum;
use App\Filament\Resources\StockInResource;
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
use Illuminate\Support\Facades\DB;

class EditStockIn extends EditRecord
{
    use HasAmountCalculation, HandlesTransactions;

    protected static string $resource = StockInResource::class;

    public ?string $date = null;

    public function mount($record): void
    {
        parent::mount($record);

        $rec        = $this->record;
        $this->date = $rec->date;

        [$deposit, $cashback] = $this->tranBalance();

        $amount = ($rec->rate * $rec->quantity) - $rec->discount + $deposit - $cashback;

        $this->form->fill([
             ...$this->form->getState(),
            'deposit'  => $deposit,
            'cashback' => $cashback,
            'amount'   => $amount,
        ]);

    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->stockInRollback();

        $data['amount'] = ($data['rate'] * $data['quantity']) - $data['discount'];

        return $data;
    }

    protected function afterSave(): void
    {
        $form_data = $this->form->getState();

        $amount  = ($form_data['rate'] * $form_data['quantity']) - $form_data['discount'];
        $balance = $amount + $form_data['deposit'] - $form_data['cashback'];

        if ($form_data['customer_id']) {
            Customer::where('id', $form_data['customer_id'])
                ->update([
                    'balance' => DB::raw('balance + ' . $balance),
                ]);
        }

        $st = Stock::where('product_id', $form_data['product_id'])->first();

        $updateData = [
            'quantity' => DB::raw('quantity + ' . $form_data['quantity']),
        ];

        if (! $st || $st->quantity == 0) {
            $updateData['available'] = $form_data['quantity'];
        }

        Stock::updateOrCreate(
            ['product_id' => $form_data['product_id']],
            $updateData
        );

        $this->saveTransaction($form_data, $amount, $this->record);
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
                    ->columnSpan(1)
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
                    ->columnSpan(1)
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
                        self::stockInUpdateAmount($set, $get);
                    }),

                TextInput::make('rate')
                    ->label('রেট')
                    ->required()
                    ->numeric()
                    ->columnSpan(1)
                    ->live(onBlur: true) // শুধু ফোকাস সরালে আপডেট হবে
                    ->afterStateUpdated(function ($state, $set, $get) {
                        self::stockInUpdateAmount($set, $get);
                    }),
                TextInput::make('discount')
                    ->label('ডিস্কাউন্ট')
                    ->numeric()
                    ->columnSpan(1)
                    ->live(onBlur: true) // শুধু ফোকাস সরালে আপডেট হবে
                    ->afterStateUpdated(function ($state, $set, $get) {
                        self::stockInUpdateAmount($set, $get);
                    }),
                TextInput::make('deposit')
                    ->label('জমা')
                    ->numeric()
                    ->columnSpan(1)
                    ->live(onBlur: true) // শুধু ফোকাস সরালে আপডেট হবে
                    ->afterStateUpdated(function ($state, $set, $get) {
                        self::stockInUpdateAmount($set, $get);
                    }),
                TextInput::make('cashback')
                    ->label('ফেরত')
                    ->numeric()
                    ->columnSpan(1)
                    ->live(onBlur: true) // শুধু ফোকাস সরালে আপডেট হবে
                    ->afterStateUpdated(function ($state, $set, $get) {
                        self::stockInUpdateAmount($set, $get);
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
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return route('filament.admin.pages.stock-in-calculation-egg', [
            'date' => $this->date,
        ]);
    }

    /**
     * Get transaction balance details
     *
     * @return array{
     *     deposit: float|int,
     *     cashback: float|int
     * }
     */
    protected function tranBalance(): array
    {
        $transaction = Transaction::select(
            'cash_flow_id',
            'tran_type_id',
            'amount'
        )
            ->where('stock_in_id', $this->record->id ?? 0)
            ->whereIn('tran_type_id', [TransactionTypeEnum::DEPOSIT, TransactionTypeEnum::CASHBACK])
            ->get();

        $deposit  = 0;
        $cashback = 0;

        foreach ($transaction as $tran) {
            if ($tran->tran_type_id == TransactionTypeEnum::DEPOSIT) {
                $deposit = $tran->amount;
            }

            if ($tran->tran_type_id == TransactionTypeEnum::CASHBACK) {
                $cashback = $tran->amount;
            }
        }

        return [$deposit, $cashback];
    }

    protected function stockInRollback()
    {
        $rec      = $this->record;
        $rate     = $rec->rate;
        $quantity = $rec->quantity;
        $discount = $rec->discount;

        [$deposit, $cashback] = $this->tranBalance();

        $amount  = ($rate * $quantity) - $discount;
        $balance = $amount + $deposit - $cashback;

        if ($this->is_farmer($rec->customer_id)) {
            $this->updateBalance($rec->customer_id, $balance, 'add');
        } else {
            $this->updateBalance($rec->customer_id, $balance, 'subtract');
        }

        if ($rec->is_available == 1) {
            Stock::where('product_id', $rec->product_id)
                ->update([
                    'quantity'  => DB::raw('quantity - ' . $quantity),
                    'available' => DB::raw('available - ' . $quantity),
                ]);
        } else {
            Stock::where('product_id', $rec->product_id)
                ->update([
                    'quantity' => DB::raw('quantity - ' . $quantity),
                ]);
        }

        StockIn::where('id', $rec->id)->update([
            'quantity'     => 0,
            'rate'         => 0,
            'discount'     => 0,
            'amount'       => 0,
            'is_available' => 0,
        ]);

        Transaction::where('stock_in_id', $rec->id)
            ->delete();
    }

    protected function is_farmer($customer_id): bool
    {
        return Customer::find($customer_id)?->value('is_farmer');
    }

    protected function updateBalance($customer_id, $balance, $operation = 'add')
    {
        $customer = Customer::find($customer_id);

        if (! $customer) {
            return;
        }

        $operation === 'subtract'
        ? $customer->decrement('balance', $balance)
        : $customer->increment('balance', $balance);
    }
}
