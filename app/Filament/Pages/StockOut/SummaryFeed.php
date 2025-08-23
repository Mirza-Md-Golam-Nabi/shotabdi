<?php
namespace App\Filament\Pages\StockOut;

use App\Models\StockOut;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Pages\Page;
use Illuminate\Support\Collection;

class SummaryFeed extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.stock-out.summary-feed';

    protected static ?string $navigationLabel = 'Stock Out Feed';

    protected static ?string $title = 'Transactions Date';

    protected ?string $heading = 'Transactions Date';

    protected static ?string $navigationGroup = 'Summary';

    protected static ?string $slug = 'stock-out-summary-feed';

    public Collection $groupedStockOut;

    public ?string $searchDate = null;

    public function mount(): void
    {
        $stock_outs = StockOut::select('date')
            ->where('product_id', '!=', 1)
            ->groupBy('date')
            ->orderByDesc('date')
            ->take(30)
            ->get();

        $this->groupedStockOut = $stock_outs->map(function ($item) {
            $carbonDate = Carbon::parse($item->date);

            return (object) [
                'date'    => $item->date,
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
                ->required(),
        ];
    }

    public function submit()
    {
        if ($this->searchDate) {
            return redirect()->route('filament.admin.pages.stock-out-summary-feed', ['date' => $this->searchDate]);
        }
    }
}
