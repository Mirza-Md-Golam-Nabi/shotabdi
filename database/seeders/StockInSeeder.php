<?php
namespace Database\Seeders;

use App\Models\Stock;
use App\Models\Product;
use App\Models\StockIn;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Database\Seeders\Concerns\SeederHelper;

class StockInSeeder extends Seeder
{
    use SeederHelper;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = $this->productList();

        foreach ($products as $product) {
            $product_id = Product::where('name', $product)->value('id');

            $quantity = rand(50, 100);
            $rate     = rand(20, 25) * 100;
            $discount = rand(0, 5) * 10;
            $amount   = ($quantity * $rate) - $discount;

            StockIn::create([
                'date'        => now(),
                'customer_id' => null,
                'product_id'  => $product_id,
                'quantity'    => $quantity,
                'rate'        => $rate,
                'amount'      => $amount,
                'discount'    => $discount,
            ]);

            Stock::updateOrCreate(
                ['product_id' => $product_id],
                [
                    'quantity' => DB::raw('quantity + ' . $quantity),
                    'amount'   => DB::raw('amount + ' . $amount),
                ]
            );
        }
    }
}
