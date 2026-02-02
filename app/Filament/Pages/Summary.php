<?php
namespace App\Filament\Pages;

use App\Models\Transaction;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Pages\Page;

class Summary extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.stock.summary';

    protected static ?string $navigationLabel = 'Deposit';

    protected static ?string $title = 'Transactions Date';

    protected ?string $heading = 'Transactions Date';

    protected static ?string $navigationGroup = 'Summary';

    public ?string $searchDate = null;

    public ?string $routeName = null;

    public int $perPage = 30;

    public function loadMore(): void
    {
        $this->perPage += 15;
    }

    public function mount(): void
    {
        $this->routeName = 'filament.admin.pages.daily-calculation';

        $this->form->fill();
    }

    public function getGroupDateProperty()
    {
        return Transaction::query()
            ->select('date')
            ->whereNull('stock_in_id')
            ->whereNull('stock_out_id')
            ->groupBy('date')
            ->orderByDesc('date')
            ->limit($this->perPage)
            ->get()
            ->map(function ($item) {
                $carbonDate = Carbon::parse($item->date);

                return (object) [
                    'date'    => $item->date,
                    'en_date' => $carbonDate->format('d M, Y'),
                    'bn_day'  => $carbonDate->locale('bn')->translatedFormat('l'),
                ];
            });
    }

    protected function getFormSchema(): array
    {
        return [
            DatePicker::make('searchDate')
                ->label('Select a date')
                ->required(),
        ];
    }

    public function submit()
    {
        if ($this->searchDate) {
            return redirect()->route('filament.admin.pages.daily-calculation', ['date' => $this->searchDate]);
        }
    }
}
