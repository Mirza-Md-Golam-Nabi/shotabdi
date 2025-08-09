<?php
namespace App\Filament\Resources\StockInResource\Pages;

use App\Filament\Resources\StockInResource;
use App\Models\Customer;
use App\Models\Stock;
use App\Models\StockIn;
use App\Models\Transaction;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CreateStockIn extends CreateRecord
{
    protected static string $resource = StockInResource::class;

    protected ?string $date;

    protected Model $stock_in;

    protected function handleRecordCreation(array $data): Model
    {
        $this->date = $data['date'];

        DB::transaction(function () use ($data) {
            foreach ($data['stock_ins'] as $stock) {
                $amount  = ($stock['rate'] * $stock['quantity']) - $stock['discount'];
                $balance = ($stock['rate'] * $stock['quantity']) + $stock['deposit'] - $stock['cashback'] - $stock['discount'];

                $this->stock_in = StockIn::create([
                    'date'        => $this->date,
                    'customer_id' => $stock['customer_id'],
                    'product_id'  => $stock['product_id'],
                    'quantity'    => $stock['quantity'],
                    'rate'        => $stock['rate'],
                    'amount'      => $amount,
                    'discount'    => $stock['discount'],
                ]);

                Stock::updateOrCreate(
                    ['product_id' => $stock['product_id']],
                    [
                        'quantity' => DB::raw('quantity + ' . $stock['quantity']),
                        'amount'   => DB::raw('amount + ' . $amount),
                    ]
                );

                if ($stock['customer_id']) {
                    Customer::where('id', $stock['customer_id'])
                        ->update([
                            'balance' => DB::raw('balance - ' . $balance),
                        ]);
                }

                if ($stock['product_id'] == 1) {
                    Transaction::create([
                        'customer_id'  => $stock['customer_id'],
                        'date'         => $this->date,
                        'cash_flow_id' => 1, // deposit
                        'tran_type_id' => 2, // Egg
                        'amount'       => $amount,
                    ]);

                    if ($stock['deposit']) {
                        Transaction::create([
                            'customer_id'  => $stock['customer_id'],
                            'date'         => $this->date,
                            'cash_flow_id' => 1, // deposit
                            'tran_type_id' => 3, // deposit
                            'amount'       => $stock['deposit'],
                        ]);
                    }

                    if ($stock['cashback']) {
                        Transaction::create([
                            'customer_id'  => $stock['customer_id'],
                            'date'         => $this->date,
                            'cash_flow_id' => 2, // expense
                            'tran_type_id' => 4, // Egg
                            'amount'       => $stock['cashback'],
                        ]);
                    }
                }
            }

        });

        return $this->stock_in;
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('create', ['date' => $this->date]);
    }
}
