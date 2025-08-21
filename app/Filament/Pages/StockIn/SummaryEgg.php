<?php
namespace App\Filament\Pages\StockIn;

use App\Models\StockIn;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Pages\Page;
use Illuminate\Support\Collection;

class SummaryEgg extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.stock-in.summary-egg';

    protected static ?string $navigationLabel = 'Stock In Egg';

    protected static ?string $title = 'Transactions Date';

    protected ?string $heading = 'Transactions Date';

    protected static ?string $navigationGroup = 'Summary';

    protected static ?string $slug = 'stock-in-summary-egg';

    public Collection $groupedStockIn;

    public ?string $searchDate = null;

    public function mount(): void
    {
        $stock_ins = StockIn::query()
            ->select('date')
            ->where('product_id', 1)
            ->groupBy('date')
            ->orderByDesc('date')
            ->take(30)
            ->get();

        $this->groupedStockIn = $stock_ins->map(function ($item) {
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
            return redirect()->route('filament.admin.pages.stock-in-summary-egg', ['date' => $this->searchDate]);
        }
    }
}
