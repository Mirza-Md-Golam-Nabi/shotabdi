<?php
namespace App\Filament\Resources\StockInResource\Pages;

use App\Enums\AvailableEnum;
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
            $amount  = $this->amount($stock);
            $balance = $this->balance($stock);

            $this->stock_in = $stockIn = $this->createStockIn($stock);

            $this->updateOrCreateStock($stock);

            if ($this->is_farmer($stock['customer_id'])) {
                $this->updateCustomerBalance($stock['customer_id'], $balance, 'subtract');
            } else {
                $this->updateCustomerBalance($stock['customer_id'], $balance, 'add');
            }

            $this->saveStockInTransaction($stock, $amount, $stockIn);
        }

        return $this->stock_in;
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('create', ['date' => $this->date]);
    }

    protected function hasUnavailableStock($product_id): bool
    {
        return StockIn::where('product_id', $product_id)
            ->whereIn('is_available', [AvailableEnum::INACTIVE, AvailableEnum::ACTIVE])
            ->exists();
    }

    public function is_farmer($customer_id): bool
    {
        return Customer::where('id', $customer_id)->value('is_farmer')->value;
    }

    protected function updateCustomerBalance($customer_id, $balance, $operation = 'add'): void
    {
        (new CustomerService())->updateBalance($customer_id, $balance, $operation);
    }

    protected function amount(array $data)
    {
        return ($data['rate'] * $data['quantity']) - $data['discount'];
    }

    protected function balance(array $data)
    {
        return ($data['rate'] * $data['quantity']) - $data['discount'] + $data['deposit'] - $data['cashback'];
    }

    protected function createStockIn(array $stock): StockIn
    {
        $hasUnavailableStock = $this->hasUnavailableStock($stock['product_id']);

        $is_available = AvailableEnum::INACTIVE;
        if (! $hasUnavailableStock) {
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
