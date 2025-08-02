<?php
namespace App\Filament\Pages;

use App\Models\Distribute;
use App\Models\Transaction;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class DailyCalculation extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.daily-calculation';

    protected static bool $shouldRegisterNavigation = false;

    protected ?string $heading = '';

    public array $transactions = [];

    public array $sum = [];

    public array $date = [];

    public ?array $distribute;

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

        $this->distribute = Distribute::where('date', $date_select)
            ->first(['home', 'dokan'])
        ?->toArray();

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
        $this->sum      = [
            'deposit_sum' => number_format($deposit_sum),
            'expense_sum' => number_format($expense_sum),
            'total'       => number_format(abs($deposit_sum - $expense_sum)),
        ];

        // convert into array
        $deposits = $deposits->toArray();
        $expenses = $expenses->toArray();

        $maxCount = max(count($deposits), count($expenses));

        if ($maxCount) {
            $this->transactions = collect(range(0, $maxCount - 1))
                ->map(function ($i) use ($deposits, $expenses) {
                    return [
                        'deposit_id'     => $deposits[$i]['id'] ?? null,
                        'deposit_name'   => $deposits[$i]['customer']['name'] ?? null,
                        'deposit_amount' => numberFormat($deposits, $i),
                        'expense_id'     => $expenses[$i]['id'] ?? null,
                        'expense_name'   => $expenses[$i]['customer']['name'] ?? null,
                        'expense_amount' => numberFormat($expenses, $i),
                    ];
                })->toArray();
        }
    }

    public function getHeaderActions(): array
    {
        return [
            Action::make('openForm')
                ->label('ক্যাশ বিতরণ')
                ->form([
                    TextInput::make('dokan')
                        ->label('দোকানে ক্যাশ')
                        ->numeric(),
                    TextInput::make('home')
                        ->label('বাসায় ক্যাশ')
                        ->numeric(),
                ])
                ->action(function (array $data) {
                    $filtered = array_filter($data);
                    Distribute::updateOrCreate(
                        ['date' => $this->date['select_date']],
                        $filtered
                    );

                    Notification::make()
                        ->title('সফল হয়েছে')
                        ->success()
                        ->seconds(2)
                        ->send();

                    return redirect()->route('filament.admin.pages.daily-calculation', ['date' => $this->date['select_date']]);
                }),
        ];
    }
}
