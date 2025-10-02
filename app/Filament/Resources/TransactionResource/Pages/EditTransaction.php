<?php
namespace App\Filament\Resources\TransactionResource\Pages;

use App\Enums\CashFlowEnum;
use App\Enums\TransactionTypeEnum;
use App\Filament\Forms\CustomerForm;
use App\Filament\Resources\TransactionResource;
use App\Filament\Services\CustomerService;
use App\Models\Customer;
use Filament\Actions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Pages\EditRecord;

class EditTransaction extends EditRecord
{
    protected static string $resource = TransactionResource::class;

    public ?string $incoming_date = null;

    public ?Customer $prev_customer    = null;
    public ?Customer $current_customer = null;

    public function mount($record): void
    {
        parent::mount($record);

        $this->incoming_date = $this->record->date;
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $this->prev_customer = Customer::find($data['customer_id']);

        $data['cash_flow_id'] = $this->prev_customer->isBank()
            ? CashFlowEnum::from($data['cash_flow_id'])->reverse()->value
            : $data['cash_flow_id'];

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->transactionRollback();

        $customer = $this->current_customer = Customer::find($data['customer_id']);

        $cash_flow = CashFlowEnum::from($data['cash_flow_id']);

        $tran_type = $cash_flow == CashFlowEnum::Deposit
            ? TransactionTypeEnum::Deposit
            : TransactionTypeEnum::Expense;

        /**
         * If the customer is a bank,
         * the cash flow value will be reversed:
         * - Deposit becomes Expense
         * - Expense becomes Deposit
         *
         * This is because for bank customers, the meaning of deposit and expense
         * is opposite compared to regular customers.
         */

        if ($customer->isBank()) {
            $cash_flow = $cash_flow->reverse();
            $tran_type = $tran_type->reverse();
        }

        $data['cash_flow_id'] = $cash_flow->value;
        $data['tran_type_id'] = $tran_type->value;

        return $data;
    }

    protected function afterSave(): void
    {
        $form_data = $this->form->getState();

        $customer_id = $form_data['customer_id'];
        $balance     = $form_data['amount'];
        $customer    = $this->current_customer;

        $cash_flow = CashFlowEnum::from($form_data['cash_flow_id']);

        if ($customer->isBank()) {
            $cash_flow = $cash_flow->reverse();
        }

        $operation = $customer->transactionOperation($cash_flow);
        $this->updateCustomerBalance($customer_id, $balance, $operation);
    }

    protected function transactionRollback(): void
    {
        $rec         = $this->record; // transactions table data
        $customer_id = $rec->customer_id;
        $balance     = $rec->amount;
        $customer    = $this->prev_customer;

        $operation = $customer?->transactionOperation($rec->cash_flow_id, true);

        $this->updateCustomerBalance($customer_id, $balance, $operation);
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
                    ->createOptionForm(CustomerForm::fields())
                    ->createOptionUsing(function (array $data) {
                        $customer = Customer::create($data);
                        return $customer->getKey();
                    }),

                Select::make('cash_flow_id')
                    ->label('ধরণ')
                    ->options(CashFlowEnum::options())
                    ->required(),

                TextInput::make('amount')
                    ->label('টাকার পরিমান')
                    ->numeric(),
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
        return route('filament.admin.pages.daily-calculation', [
            'date' => $this->incoming_date,
        ]);
    }

    protected function updateCustomerBalance($customer_id, $balance, $operation = 'add'): void
    {
        (new CustomerService())->updateBalance($customer_id, $balance, $operation);
    }
}
