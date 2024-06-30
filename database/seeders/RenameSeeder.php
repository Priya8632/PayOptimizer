<?php

namespace Database\Seeders;

use App\Models\Customization;
use Illuminate\Database\Seeder;

class RenameSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rename = ['Always', 'Country', 'Language', 'Customer Tags'];
        Customization::updateOrCreate(['name' => Customization::RENAME], [
            'filter' => json_encode($rename),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
