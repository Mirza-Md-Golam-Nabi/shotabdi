<?php

namespace App\Filament\Resources\TransactionResource\Pages;

use Filament\Actions;
use App\Models\Customer;
use Filament\Forms\Form;
use App\Enums\TransactionTypeEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\EditRecord;
use Filament\Forms\Components\DatePicker;
use App\Filament\Resources\TransactionResource;

class EditTransaction extends EditRecord
{
    protected static string $resource = TransactionResource::class;

    public ?string $incoming_date = null;

    public function mount($record): void
    {
        parent::mount($record);

        $this->incoming_date = $this->record->date;
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                DatePicker::make('date')
                    ->label('তারিখ')
                    ->default(fn() => request()->query('date'))
                    ->required(),

                Select::make('customer_id')
                    ->label('কাস্টমার নাম')
                    // ->relationship('customer', 'name')
                    ->options(Customer::pluck('name', 'id'))
                    ->required()
                    ->searchable()
                    // ->preload()
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

                Select::make('type')
                    ->label('ধরন')
                    ->options(TransactionTypeEnum::options())
                    ->required(),

                TextInput::make('amount')
                    ->label('টাকার পরিমান')
                    ->numeric(),
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
        return route('filament.admin.pages.daily-calculation', [
            'date' => $this->incoming_date,
        ]);
    }
}
