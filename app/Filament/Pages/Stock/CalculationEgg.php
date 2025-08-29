<?php
namespace App\Filament\Pages\Stock;

use App\Models\Stock;
use App\Models\StockIn;
use App\Models\StockOut;
use Carbon\Carbon;
use Filament\Pages\Page;

class CalculationEgg extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.stock.calculation-egg';

    protected static ?string $slug = 'stock-calculation-egg';

    protected static bool $shouldRegisterNavigation = false;

    protected ?string $heading = '';

    public array $stocks;

    public array $sum;

    public array $date;

    public array $route;

    public ?int $current_stock = null;

    public function mount()
    {
        $this->route = [
            'current'         => 'filament.admin.pages.stock-calculation-egg',
            'stock_in_edit'   => 'filament.admin.resources.stock-ins.edit',
            'stock_out_edit'  => 'filament.admin.resources.stock-outs.edit',
            'customer_detail' => 'filament.admin.pages.details-customer',
        ];

        $this->current_stock = optional(
            Stock::where('product_id', 1)
                ->first()
        )->quantity / 30;

        $date_select = request()->query('date', now()->toDateString());

        $date_parse = Carbon::parse($date_select);
        $this->date = [
            'select_date' => $date_select,
            'prev_date'   => $date_parse->copy()->subDay()->toDateString(),
            'next_date'   => $date_parse->copy()->addDay()->toDateString(),
            'bn_date'     => enToBn($date_parse->locale('bn')->translatedFormat('j F, Y')),
            'bn_day'      => $date_parse->locale('bn')->translatedFormat('l'),
        ];

        $stock_ins = StockIn::with('customer:id,name')
            ->select('id', 'customer_id', 'quantity')
            ->where('product_id', 1)
            ->where('date', $date_select)
            ->get();

        $stock_outs = StockOut::with('customer:id,name')
            ->select('id', 'customer_id', 'quantity')
            ->where('product_id', 1)
            ->where('date', $date_select)
            ->get();

        // sum of data
        $this->sum = [
            'stock_in_total'  => $stock_ins->sum('quantity') / 30,
            'stock_out_total' => $stock_outs->sum('quantity') / 30,
        ];

        // convert into array
        $stock_in  = $stock_ins->toArray();
        $stock_out = $stock_outs->toArray();

        $maxCount = max(count($stock_in), count($stock_out));

        if ($maxCount) {
            $this->stocks = collect(range(0, $maxCount - 1))
                ->map(function ($i) use ($stock_in, $stock_out) {
                    return [
                        'stock_in_id'        => $stock_in[$i]['id'] ?? null,
                        'stock_in_customer'  => $stock_in[$i]['customer']['id'] ?? null,
                        'stock_in_name'      => $stock_in[$i]['customer']['name'] ?? null,
                        'stock_in_quantity'  => isset($stock_in[$i]['quantity'])
                        ? $stock_in[$i]['quantity'] / 30
                        : null,
                        'stock_out_id'       => $stock_out[$i]['id'] ?? null,
                        'stock_out_customer' => $stock_out[$i]['customer']['id'] ?? null,
                        'stock_out_name'     => $stock_out[$i]['customer']['name'] ?? null,
                        'stock_out_quantity' => isset($stock_out[$i]['quantity'])
                        ? $stock_out[$i]['quantity'] / 30
                        : null,
                    ];
                })->toArray();
        }
    }
}
