<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
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

    private function productList():array
    {
        return [
            'সোনালী ফিড',
            'সোনালী ম্যাশ ফিড'
        ];
    }
}
