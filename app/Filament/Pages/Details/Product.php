<?php
namespace App\Filament\Pages\Details;

use App\Models\Product as ProductModel;
use App\Models\Stock;
use App\Models\StockIn;
use Filament\Pages\Page;
use Illuminate\Support\Collection;

class Product extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.details.product';

    protected static ?string $slug = 'details-product';

    protected static bool $shouldRegisterNavigation = false;

    protected ?string $heading = '';

    public ?Collection $stocks = null;

    public array $product;

    public function mount()
    {
        $product_id = request()->query('product_id');

        $prod = ProductModel::find($product_id);

        $factor = $product_id == 1 ? 30 : 1;
        $unit   = $product_id == 1 ? 'খাঁচি' : 'বস্তা';
        $stock  = Stock::where('product_id', $product_id)->first()?->quantity / $factor;

        $this->product = [
            'name'  => $prod->name,
            'unit'  => $unit,
            'stock' => $stock,
            'route' => 'filament.admin.pages.details-customer',
        ];

        $this->stocks = StockIn::with('customer')
            ->where('product_id', $product_id)
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->get();

    }
}
