<?php
namespace App\Filament\Resources\StockOutResource\Pages;

use App\Models\Stock;
use App\Models\StockIn;
use App\Models\Customer;
use App\Models\StockOut;
use App\Enums\ProductEnum;
use App\Enums\CashFlowEnum;
use App\Enums\AvailableEnum;
use App\Models\FeedDisburse;
use App\Enums\FeedDisburseEnum;
use Illuminate\Database\Eloquent\Model;
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
            $multiply = $stock['product_id'] == ProductEnum::Egg->value ? 30 : 1;

            $stock['quantity'] *= $multiply;

            $amount      = $this->amount($stock);
            $balance     = $this->balance($stock);
            $customer_id = $stock['customer_id'];

            if (! $this->hasUnavailableStock($stock['product_id'], $stock['quantity'])) {
                continue;
            }

            $this->stock_out = $stockOut = $this->createStockOut($stock);

            $this->updateStock($stock);

            $customer  = Customer::find($customer_id);
            $operation = $customer?->transactionOperation(CashFlowEnum::Expense);
            $customer?->updateBalance($balance, $operation);

            $this->saveStockOutTransaction($stock, $amount, $stockOut);

            if ($customer?->isFarmer()) {
                $this->feedDisburseUpdate($stock);
                $this->feedDisburse($stock, $stockOut);
            }
        }

        return $this->stock_out;
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

    protected function amount(array $data)
    {
        return round($data['rate'] * $data['quantity']) - $data['discount'];
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
        } elseif ($stock->available == $data['quantity']) {
            StockIn::where('product_id', $data['product_id'])
                ->where('is_available', AvailableEnum::Active)
                ->update(['is_available' => AvailableEnum::Finished]);

            $stock_in = StockIn::where('product_id', $data['product_id'])
                ->where('is_available', AvailableEnum::Inactive)
                ->first();

            $stock->available = 0;
            if ($stock_in) {
                $stock->available = $stock_in->quantity;

                $stock_in->is_available = AvailableEnum::Active;
                $stock_in->save();
            }

            $stock->save();

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

    protected function feedDisburse($stock, $stockOut)
    {
        return FeedDisburse::create([
            'stock_out_id'  => $stockOut->id,
            'customer_id'   => $stock['customer_id'],
            'product_id'    => $stock['product_id'],
            'previous_date' => $this->date,
            'next_date'     => $stock['next_date'] ?? null,
            'status'        => FeedDisburseEnum::Pending,
        ]);
    }

    protected function feedDisburseUpdate($data)
    {
        return FeedDisburse::where('customer_id', $data['customer_id'])
            ->where('product_id', $data['product_id'])
            ->where('status', FeedDisburseEnum::Pending)
            ->update(['status' => FeedDisburseEnum::Delivered]);
    }
}
