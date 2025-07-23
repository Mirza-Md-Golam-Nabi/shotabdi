<?php

namespace Database\Seeders;

use App\Enums\TransactionTypeEnum;
use Carbon\Carbon;
use App\Models\User;
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
        $users = $this->userList();

        foreach ($users as $user) {
            $user_id = User::where('mobile', $user['mobile'])->value('id');

            $limit = rand(3, 5);

            for ($i = 1; $i <= $limit; $i++) {
                Transaction::create([
                    'user_id' => $user_id,
                    'date' => Carbon::now()->subDays(rand(0, 10))->format('Y-m-d'),
                    'type' => TransactionTypeEnum::cases()[array_rand(TransactionTypeEnum::cases())],
                    'amount' => rand(5, 100) * 100,
                ]);
            }
        }
    }
}
