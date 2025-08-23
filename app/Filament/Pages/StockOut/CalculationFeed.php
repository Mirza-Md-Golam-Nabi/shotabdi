<?php
namespace App\Filament\Pages\StockOut;

use App\Enums\CashFlowEnum;
use App\Models\StockOut;
use App\Models\Transaction;
use Carbon\Carbon;
use Filament\Pages\Page;

class CalculationFeed extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.stock-out.calculation-feed';

    protected static ?string $slug = 'stock-out-calculation-feed';

    protected static bool $shouldRegisterNavigation = false;

    protected ?string $heading = '';

    public array $transactions = [];

    public array $sum = [];

    public array $date = [];

    public function mount()
    {
        $date_select = request()->query('date', now()->toDateString());

        $date_parse = Carbon::parse($date_select);
        $this->date = [
            'select_date' => $date_select,
            'prev_date'   => $date_parse->copy()->subDay()->toDateString(),
            'next_date'   => $date_parse->copy()->addDay()->toDateString(),
            'bn_date'     => enToBn($date_parse->locale('bn')->translatedFormat('j F, Y')),
            'bn_day'      => $date_parse->locale('bn')->translatedFormat('l'),
        ];

        $stock_outs = StockOut::with('customer:id,name')
            ->select('id', 'customer_id', 'quantity')
            ->where('product_id', '!=', 1)
            ->where('date', $date_select)
            ->get();

        $stock_out_id_list = $stock_outs->pluck('id')->toArray();

        // Load all transaction data
        $transaction = Transaction::with('customer:id,name')
            ->select('id', 'customer_id', 'stock_out_id', 'cash_flow_id', 'amount')
            ->where('date', $date_select)
            ->where('cash_flow_id', CashFlowEnum::DEPOSIT)
            ->whereIn('stock_out_id', $stock_out_id_list)
            ->get();

        // sum of data
        $this->sum = [
            'total_quantity' => $stock_outs->sum('quantity'),
            'total_amount'   => $transaction->sum('amount'),
        ];

        // convert into array
        $stock_out = $stock_outs->toArray();
        $trans     = $transaction->toArray();

        $maxCount = max(count($stock_out), count($trans));

        if ($maxCount) {
            $this->transactions = collect(range(0, $maxCount - 1))
                ->map(function ($i) use ($stock_out, $trans) {
                    return [
                        'stock_out_id'       => $stock_out[$i]['id'] ?? null,
                        'stock_out_name'     => $stock_out[$i]['customer']['name'] ?? null,
                        'stock_out_quantity' => $stock_out[$i]['quantity'] ?? null,
                        'tran_stock_out_id'  => $trans[$i]['stock_out_id'] ?? null,
                        'tran_name'          => $trans[$i]['customer']['name'] ?? null,
                        'tran_amount'        => numberFormat($trans, $i),
                    ];
                })->toArray();
        }
    }
}
