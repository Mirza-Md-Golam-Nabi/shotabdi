<?php

namespace Database\Seeders;

use App\Enums\TransactionTypeEnum;
use App\Models\Customer;
use Carbon\Carbon;
use App\Models\Transaction;
use Illuminate\Database\Seeder;
use Database\Seeders\Concerns\SeederHelper;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class TransactionSeeder extends Seeder
{
    use SeederHelper;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customers = $this->customerList();

        foreach ($customers as $customer) {
            $customer_id = Customer::where('mobile', $customer['mobile'])->value('id');

            $limit = rand(10, 15);

            for ($i = 1; $i <= $limit; $i++) {
                Transaction::create([
                    'customer_id' => $customer_id,
                    'date' => Carbon::now()->subDays(rand(0, 10))->format('Y-m-d'),
                    'type' => TransactionTypeEnum::cases()[array_rand(TransactionTypeEnum::cases())],
                    'amount' => rand(5, 100) * 100,
                ]);
            }
        }
    }
}
