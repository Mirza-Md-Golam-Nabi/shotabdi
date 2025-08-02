<?php

namespace App\Filament\Pages;

use Carbon\Carbon;
use Filament\Pages\Page;
use App\Models\Transaction;
use Filament\Forms\Components\DatePicker;
use Illuminate\Support\Collection;

class Summary extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.summary';

    protected static ?string $navigationLabel = 'Summary';

    protected static ?string $title = 'Transactions Date';

    protected ?string $heading = 'Transactions Date';

    public Collection $groupedTransactions;

    public ?string $searchDate = null;

    public function mount(): void
    {
        $transactions = Transaction::query()
            ->select('date')
            ->groupBy('date')
            ->orderByDesc('date')
            ->take(30)
            ->get();

        $this->groupedTransactions = $transactions->map(function ($item) {
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
            return redirect()->route('filament.admin.pages.daily-calculation', ['date' => $this->searchDate]);
        }
    }
}
