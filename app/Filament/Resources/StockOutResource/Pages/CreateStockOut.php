<?php
namespace App\Filament\Resources\StockOutResource\Pages;

use App\Models\Stock;
use App\Models\StockIn;
use App\Models\Customer;
use App\Models\StockOut;
use App\Enums\CustomerEnum;
use App\Enums\AvailableEnum;
use Illuminate\Database\Eloquent\Model;
use App\Filament\Services\CustomerService;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\StockOutResource;
use App\Filament\Traits\HandlesTransactions;

class CreateStockOut extends CreateRecord
{
    use HandlesTransactions;

    protected static string $resource = StockOutResource::class;

    protected static bool $canCreateAnother = false;

    protected ?string $date;

    protected Model $stock_out;

    protected function handleRecordCreation(array $data): Model
    {
        $this->date = $data['date'];

        foreach ($data['stock_outs'] as $stock) {
            $amount  = $this->amount($stock);
            $balance = $this->balance($stock);
            $customer_id = $stock['customer_id'];

            if (! $this->hasUnavailableStock($stock['product_id'], $stock['quantity'])) {
                continue;
            }

            $this->stock_out = $stockOut = $this->createStockOut($stock);

            $this->updateStock($stock);

            $operation = Customer::find($customer_id)?->isEggSeller() ? 'subtract' : 'add';
            $this->updateCustomerBalance($customer_id, $balance, $operation);

            $this->saveStockOutTransaction($stock, $amount, $stockOut);
        }

        return $this->stock_out;
    }

    public function is_egg_seller($customer_id):bool
    {
        return Customer::where('id', $customer_id)->value('type') == CustomerEnum::EGG_SELLER;
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('create', ['date' => $this->date]);
    }

    protected function hasUnavailableStock($product_id, $quantity)
    {
        return Stock::where('product_id', $product_id)
            ->where('quantity', '>=', $quantity)
            ->exists();
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
        return $this->amount($data) - $data['deposit'];
    }

    protected function createStockOut(array $stock): StockOut
    {
        return StockOut::create([
            'date'        => $this->date,
            'customer_id' => $stock['customer_id'],
            'product_id'  => $stock['product_id'],
            'quantity'    => $stock['quantity'],
            'rate'        => $stock['rate'],
            'amount'      => $this->amount($stock),
            'discount'    => $stock['discount'],
        ]);
    }

    protected function updateStock(array $data)
    {
        $stock = Stock::where('product_id', $data['product_id'])->first();

        if ($stock->available > $data['quantity']) {
            $stock->decrement('available', $data['quantity']);
        } else {
            $remain = $data['quantity'] - $stock->available;

            StockIn::where('product_id', $data['product_id'])
                ->where('is_available', AvailableEnum::ACTIVE)
                ->update(['is_available' => AvailableEnum::FINISHED]);

            do {
                $stock_in = StockIn::where('product_id', $data['product_id'])
                    ->where('is_available', AvailableEnum::INACTIVE)
                    ->first();

                if ($remain > $stock_in->quantity) {
                    $stock_in->is_available = AvailableEnum::FINISHED;
                    $remain -= $stock_in->quantity;
                } elseif ($remain == $stock_in->quantity) {
                    $stock_in->is_available = AvailableEnum::FINISHED;
                    $stock_in->save();

                    $stock_in = StockIn::where('product_id', $data['product_id'])
                        ->where('is_available', AvailableEnum::INACTIVE)
                        ->first();

                    $stock_in->is_available = AvailableEnum::ACTIVE;
                    $stock->available       = $stock_in->quantity;
                    $stock->save();

                    $remain = 0;
                } else {
                    $stock_in->is_available = AvailableEnum::ACTIVE;
                    $stock->available       = $stock_in->quantity - $remain;
                    $stock->save();
                    $remain = 0;
                }

                $stock_in->save();

            } while ($remain != 0);

        }

        $stock->decrement('quantity', $data['quantity']);
    }
}
