<?php
namespace Database\Seeders;

use App\Models\TranType;
use Database\Seeders\Concerns\SeederHelper;
use Illuminate\Database\Seeder;

class TranTypeSeeder extends Seeder
{
    use SeederHelper;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tranTypes = $this->tranTypeList();

        foreach ($tranTypes as $type) {
            TranType::create([
                'title' => $type,
            ]);
        }
    }
}
