<?php
namespace App\Filament\Resources\ProductResource\Pages;

use App\Enums\AvailableEnum;
use App\Filament\Resources\ProductResource;
use App\Models\Product;
use App\Models\Stock;
use App\Models\StockIn;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageProducts extends ManageRecords
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->createAnother(false)
                ->using(function (array $data) {
                    return Product::create($data);
                })
                ->after(function (Product $product, array $data) {
                    $quantity = $data['quantity'];
                    if ($quantity) {
                        $product_id = $product->id;

                        StockIn::create([
                            'date'         => now(),
                            'product_id'   => $product_id,
                            'quantity'     => $quantity,
                            'rate'         => 0,
                            'amount'       => 0,
                            'is_available' => AvailableEnum::ACTIVE,
                        ]);

                        Stock::create([
                            'product_id' => $product_id,
                            'quantity'   => $quantity,
                            'available'  => $quantity,
                        ]);
                    }
                }),
        ];
    }
}
