<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;
use Database\Seeders\Concerns\SeederHelper;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ProductSeeder extends Seeder
{
    use SeederHelper;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = $this->productList();

        foreach($products as $product){
            Product::create([
                'name' => $product
            ]);
        }
    }
}
