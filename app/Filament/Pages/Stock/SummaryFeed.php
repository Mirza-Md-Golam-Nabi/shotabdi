<?php
namespace App\Filament\Pages\Stock;

use App\Enums\ProductEnum;
use App\Models\StockIn;
use App\Models\StockOut;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Pages\Page;

class SummaryFeed extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.stock.summary';

    protected static ?string $navigationLabel = 'Feed';

    protected static ?string $title = 'Transactions Date';

    protected ?string $heading = 'Transactions Date';

    protected static ?string $navigationGroup = 'Summary';

    protected static ?string $slug = 'stock-summary-feed';

    public ?string $searchDate = null;

    public ?string $routeName = null;

    public int $perPage = 30;

    public function loadMore(): void
    {
        $this->perPage += 15;
    }

    public function mount(): void
    {
        $this->routeName = 'filament.admin.pages.stock-calculation-feed';

        $this->form->fill();
    }

    public function getGroupDateProperty()
    {
        $stock_ins = StockIn::select('date')
            ->where('product_id', '!=', ProductEnum::Egg)
            ->groupBy('date')
            ->orderByDesc('date')
            ->take($this->perPage)
            ->get();

        $stock_outs = StockOut::select('date')
            ->where('product_id', '!=', ProductEnum::Egg)
            ->groupBy('date')
            ->orderByDesc('date')
            ->take($this->perPage)
            ->get();

        return $stock_ins->pluck('date')
            ->merge($stock_outs->pluck('date'))
            ->unique()
            ->sortDesc()
            ->take($this->perPage)
            ->map(function ($date) {
                $carbonDate = Carbon::parse($date);

                return (object) [
                    'date'    => $date,
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
