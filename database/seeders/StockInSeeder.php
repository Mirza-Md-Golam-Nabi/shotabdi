<?php
namespace Database\Seeders;

use App\Enums\AvailableEnum;
use App\Models\Product;
use App\Models\Stock;
use App\Models\StockIn;
use Database\Seeders\Concerns\SeederHelper;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

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

            $stock        = StockIn::where('product_id', $product_id)->first();
            $is_available = AvailableEnum::INACTIVE;
            if (! $stock) {
                $is_available = AvailableEnum::ACTIVE;
            }

            StockIn::create([
                'date'         => now(),
                'customer_id'  => null,
                'product_id'   => $product_id,
                'quantity'     => $quantity,
                'rate'         => $rate,
                'amount'       => $amount,
                'discount'     => $discount,
                'is_available' => $is_available,
            ]);

            Stock::updateOrCreate(
                ['product_id' => $product_id],
                [
                    'quantity'  => DB::raw('quantity + ' . $quantity),
                    'available' => DB::raw('available + ' . $quantity),
                ]
            );
        }
    }
}
