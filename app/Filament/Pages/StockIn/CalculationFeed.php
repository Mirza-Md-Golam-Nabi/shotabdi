<?php
namespace App\Filament\Pages\StockIn;

use Carbon\Carbon;
use App\Models\StockIn;
use Filament\Pages\Page;
use App\Models\Transaction;

class CalculationFeed extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.stock-in.calculation-feed';

    protected static ?string $slug = 'stock-in-calculation-feed';

    protected static bool $shouldRegisterNavigation = false;

    protected ?string $heading = '';

    public array $transactions = [];

    public array $sum = [];

    public array $date = [];

    public bool $isProfit;

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

        $stock_ins = StockIn::with('customer:id,name')
            ->select('id', 'customer_id', 'quantity')
            ->where('product_id', '!=', 1)
            ->where('date', $date_select)
            ->get();

        $stock_in_id_list = $stock_ins->pluck('id')->toArray();

        // Load all transaction data
        $transaction = Transaction::with('customer:id,name')
            ->select('id', 'customer_id', 'stock_in_id', 'cash_flow_id', 'amount')
            ->where('date', $date_select)
            ->where('cash_flow_id', 2) // 2 = Expense
            ->whereIn('stock_in_id', $stock_in_id_list)
            ->get();

        // sum of data
        $this->sum = [
            'total_quantity' => $stock_ins->sum('quantity'),
            'total_amount'   => $transaction->sum('amount'),
        ];

        // convert into array
        $stock_in = $stock_ins->toArray();
        $trans    = $transaction->toArray();

        $maxCount = max(count($stock_in), count($trans));

        if ($maxCount) {
            $this->transactions = collect(range(0, $maxCount - 1))
                ->map(function ($i) use ($stock_in, $trans) {
                    return [
                        'stock_in_id'       => $stock_in[$i]['id'] ?? null,
                        'stock_in_name'     => $stock_in[$i]['customer']['name'] ?? null,
                        'stock_in_quantity' => $stock_in[$i]['quantity'] ?? null,
                        'tran_stock_in_id'  => $trans[$i]['stock_in_id'] ?? null,
                        'tran_name'         => $trans[$i]['customer']['name'] ?? null,
                        'tran_amount'       => numberFormat($trans, $i),
                    ];
                })->toArray();
        }
    }
}
