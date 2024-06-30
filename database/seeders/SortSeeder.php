<?php

namespace Database\Seeders;

use App\Models\Customization;
use Illuminate\Database\Seeder;

class SortSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sort = ['Country', 'Shipping Title'];
        Customization::updateOrCreate(['name' => Customization::SORT], [
            'filter' => json_encode($sort),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

    }
}
