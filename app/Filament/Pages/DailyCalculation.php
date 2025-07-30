<?php

namespace App\Filament\Pages;

use Carbon\Carbon;
use Filament\Pages\Page;
use App\Models\Transaction;

class DailyCalculation extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.daily-calculation';

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
            'prev_date' => $date_parse->copy()->subDay()->toDateString(),
            'next_date' => $date_parse->copy()->addDay()->toDateString(),
            'bn_date' => enToBn($date_parse->locale('bn')->translatedFormat('j F, Y')),
            'bn_day' => $date_parse->locale('bn')->translatedFormat('l')
        ];

        // Load all transaction data
        $trans = Transaction::with('customer:id,name')
            ->select('id', 'customer_id', 'type', 'amount')
            ->where('date', $date_select)
            ->get();

        // Deposits and expenses have been separated.
        $deposits = $trans->where('type', 'deposit')->values();
        $expenses = $trans->where('type', 'expense')->values();

        // Store total calculation
        $deposit_sum = $deposits->sum('amount');
        $expense_sum = $expenses->sum('amount');

        $this->isProfit = $deposit_sum > $expense_sum;
        $this->sum = [
            'deposit_sum' => number_format($deposit_sum),
            'expense_sum' => number_format($expense_sum),
            'total' => number_format(abs($deposit_sum - $expense_sum)),
        ];

        // convert into array
        $deposits = $deposits->toArray();
        $expenses = $expenses->toArray();

        $maxCount = max(count($deposits), count($expenses));

        if ($maxCount) {
            $this->transactions = collect(range(0, $maxCount - 1))
                ->map(function ($i) use ($deposits, $expenses) {
                    return [
                        'deposit_id' => $deposits[$i]['id'] ?? null,
                        'deposit_name'   => $deposits[$i]['customer']['name'] ?? null,
                        'deposit_amount' => numberFormat($deposits, $i),
                        'expense_id' => $expenses[$i]['id'] ?? null,
                        'expense_name'   => $expenses[$i]['customer']['name'] ?? null,
                        'expense_amount' => numberFormat($expenses, $i),
                    ];
                })->toArray();
        }
    }
}
