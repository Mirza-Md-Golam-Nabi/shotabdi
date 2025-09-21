<?php
namespace App\Filament\Resources\StockInResource\Pages;

use App\Enums\AvailableEnum;
use App\Enums\ProductEnum;
use App\Filament\Resources\StockInResource;
use App\Filament\Services\CustomerService;
use App\Filament\Traits\HandlesTransactions;
use App\Models\Customer;
use App\Models\Stock;
use App\Models\StockIn;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateStockIn extends CreateRecord
{
    use HandlesTransactions;

    protected static string $resource = StockInResource::class;

    protected static bool $canCreateAnother = false;

    protected ?string $date;

    protected Model $stock_in;

    protected function handleRecordCreation(array $data): Model
    {
        $this->date = $data['date'];

        foreach ($data['stock_ins'] as $stock) {
            $multiply          = $stock['product_id'] == ProductEnum::EGG->value ? 30 : 1;
            $stock['quantity'] = $stock['quantity'] * $multiply;

            $amount      = $this->amount($stock);
            $balance     = $this->balance($stock);
            $customer_id = $stock['customer_id'];

            $this->stock_in = $stockIn = $this->createStockIn($stock);

            $this->updateOrCreateStock($stock);

            /**
             * These 3 types of customers have stock-in permission.
             * 1. Company fund Add
             * 2. Farmer Egg fund Subtract
             * 3. Egg seller fund Add
             */

            $operation = Customer::find($customer_id)?->isFarmer() ? 'subtract' : 'add';
            $this->updateCustomerBalance($customer_id, $balance, $operation);

            $this->saveStockInTransaction($stock, $amount, $stockIn);
        }

        return $this->stock_in;
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('create', ['date' => $this->date]);
    }

    protected function updateCustomerBalance($customer_id, $balance, $operation = 'add'): void
    {
        (new CustomerService())->updateBalance($customer_id, $balance, $operation);
    }

    protected function amount(array $data)
    {
        return round($data['rate'] * $data['quantity']) - $data['discount'];
    }

    protected function balance(array $data)
    {
        return $this->amount($data) + $data['deposit'] - $data['cashback'];
    }

    protected function createStockIn(array $stock): StockIn
    {
        $hasAvailableStock = StockIn::hasAvailableStock($stock['product_id']);

        $is_available = AvailableEnum::INACTIVE;
        if (! $hasAvailableStock) {
            $is_available = AvailableEnum::ACTIVE;
        }

        return StockIn::create([
            'date'         => $this->date,
            'customer_id'  => $stock['customer_id'],
            'product_id'   => $stock['product_id'],
            'quantity'     => $stock['quantity'],
            'rate'         => $stock['rate'],
            'amount'       => $this->amount($stock),
            'discount'     => $stock['discount'],
            'is_available' => $is_available,
        ]);
    }

    protected function updateOrCreateStock(array $data)
    {
        $stock = Stock::firstOrNew(['product_id' => $data['product_id']]);

        $stock->quantity += $data['quantity'];

        if ($stock->quantity == $data['quantity']) {
            $stock->available = $data['quantity'];
        }

        $stock->save();
    }

}
