<?php

namespace Database\Seeders;

use App\Models\User;
use Database\Seeders\Concerns\SeederHelper;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserSeeder extends Seeder
{
    use SeederHelper;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = $this->userList();

        foreach ($users as $user) {
            User::firstOrCreate(
                [
                    'mobile' => $user['mobile'],
                ],
                [
                    'name' => $user['name'],
                    'password' => Hash::make('12345')
                ]
            );
        }
    }
}
