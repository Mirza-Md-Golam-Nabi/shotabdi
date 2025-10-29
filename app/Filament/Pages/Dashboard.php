<?php
namespace App\Filament\Pages;

use App\Enums\CustomerEnum;
use App\Models\Customer;
use App\Models\ExcludeCustomerId;
use Illuminate\Support\Facades\DB;

class Dashboard extends \Filament\Pages\Dashboard
{
    protected static string $view = 'filament.pages.dashboard';

    public function getViewData(): array
    {
        $excludedIds = ExcludeCustomerId::pluck('customer_id');

        $customer_stats = Customer::select(
            'type',
            DB::raw('SUM(balance) as total_balance'),
            DB::raw('COUNT(*) as total_count')
        )
            ->whereIn('type', [
                CustomerEnum::Bank,
                CustomerEnum::Farmer,
                CustomerEnum::EggSeller,
                CustomerEnum::Normal,
                CustomerEnum::Company,
                CustomerEnum::Others,
            ])
            ->when($excludedIds->isNotEmpty(), fn($q) => $q->whereNotIn('id', $excludedIds))
            ->groupBy('type')
            ->get();

        return [
            'customer_stats' => $customer_stats,
        ];
    }

}
