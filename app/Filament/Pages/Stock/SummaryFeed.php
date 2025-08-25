<?php
namespace App\Filament\Pages\Stock;

use App\Models\StockIn;
use App\Models\StockOut;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Pages\Page;
use Illuminate\Support\Collection;

class SummaryFeed extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.stock.summary';

    protected static ?string $navigationLabel = 'Feed';

    protected static ?string $title = 'Transactions Date';

    protected ?string $heading = 'Transactions Date';

    protected static ?string $navigationGroup = 'Summary';

    protected static ?string $slug = 'stock-summary-feed';

    public Collection $groupedDate;

    public ?string $searchDate = null;

    public ?string $routeName = null;

    public function mount(): void
    {
        $this->routeName = 'filament.admin.pages.stock-calculation-feed';

        $stock_ins = StockIn::select('date')
            ->where('product_id', '!=', 1)
            ->groupBy('date')
            ->orderByDesc('date')
            ->take(30)
            ->get();

        $stock_outs = StockOut::select('date')
            ->where('product_id', '!=', 1)
            ->groupBy('date')
            ->orderByDesc('date')
            ->take(30)
            ->get();

        $dates = $stock_ins->pluck('date')
            ->merge($stock_outs->pluck('date'))
            ->unique()
            ->sortDesc()
            ->take(30);

        $this->groupedDate = $dates->map(function ($date) {
            $carbonDate = Carbon::parse($date);

            return (object) [
                'date'    => $date,
                'en_date' => $carbonDate->format('d M, Y'),
                'bn_day'  => $carbonDate->locale('bn')->translatedFormat('l'),
            ];
        });

        $this->form->fill();
    }

    protected function getFormSchema(): array
    {
        return [
            DatePicker::make('searchDate')
                ->label('Select a date')
                ->required()
                ->format('m/d/Y'),
        ];
    }

    public function submit()
    {
        if ($this->searchDate) {
            return redirect()->route('filament.admin.pages.stock-summary-feed', ['date' => $this->searchDate]);
        }
    }
}
