<?php
namespace Database\Seeders;

use App\Models\CashFlow;
use Database\Seeders\Concerns\SeederHelper;
use Illuminate\Database\Seeder;

class CashFlowSeeder extends Seeder
{
    use SeederHelper;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cash_flows = $this->cashFlowList();

        foreach ($cash_flows as $cash) {
            CashFlow::create([
                'title' => $cash,
            ]);
        }
    }
}
