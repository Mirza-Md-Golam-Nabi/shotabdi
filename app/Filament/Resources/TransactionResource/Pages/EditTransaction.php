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
use Illuminate\Support\Facades\Log;

class EditTransaction extends EditRecord
{
    protected static string $resource = TransactionResource::class;

    public ?string $incoming_date = null;

    public function mount($record): void
    {
        parent::mount($record);

        $this->incoming_date = $this->record->date;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->transactionRollback();

        if ($data['cash_flow_id'] == CashFlowEnum::Deposit->value) {
            $data['tran_type_id'] = TransactionTypeEnum::Deposit->value;
        }

        if ($data['cash_flow_id'] == CashFlowEnum::Expense->value) {
            $data['tran_type_id'] = TransactionTypeEnum::Expense->value;
        }

        return $data;
    }

    protected function afterSave(): void
    {
        $form_data = $this->form->getState();

        $customer_id = $form_data['customer_id'];
        $balance     = $form_data['amount'];
        $customer    = Customer::find($customer_id);

        if (CashFlowEnum::Deposit->value == $form_data['cash_flow_id']) {
            $operation = $customer?->isEggSeller() || $customer?->isCompany() ? 'add' : 'subtract';
            $this->updateCustomerBalance($customer_id, $balance, $operation);
        }

        if (CashFlowEnum::Expense->value == $form_data['cash_flow_id']) {
            $operation = $customer?->isEggSeller() || $customer?->isCompany() ? 'subtract' : 'add';
            $this->updateCustomerBalance($customer_id, $balance, $operation);
        }

        Log::info(json_encode(['afterSave', $operation, $balance]));
    }

    protected function transactionRollback(): void
    {
        $rec         = $this->record; // transactions table data
        $customer_id = $rec->customer_id;
        $balance     = $rec->amount;
        $customer    = Customer::find($customer_id);

        if (CashFlowEnum::Deposit == $rec->cash_flow_id) {
            $operation = $customer?->isEggSeller() || $customer?->isCompany() ? 'subtract' : 'add';
            $this->updateCustomerBalance($customer_id, $balance, $operation);
        }

        if (CashFlowEnum::Expense == $rec->cash_flow_id) {
            $operation = $customer?->isEggSeller() || $customer?->isCompany() ? 'add' : 'subtract';
            $this->updateCustomerBalance($customer_id, $balance, $operation);
        }

        Log::info(json_encode(['rollback', $operation, $balance]));
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
