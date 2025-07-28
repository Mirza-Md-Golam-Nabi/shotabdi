<?php

namespace Database\Seeders;

use App\Models\Customer;
use Database\Seeders\Concerns\SeederHelper;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class CustomerSeeder extends Seeder
{
    use SeederHelper;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customers = $this->customerList();

        foreach ($customers as $customer) {
            Customer::firstOrCreate(
                ['mobile' => $customer['mobile']],
                ['name' => $customer['name']]
            );
        }
    }
}
