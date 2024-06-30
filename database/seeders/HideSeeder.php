<?php

namespace Database\Seeders;

use App\Models\Customization;
use Illuminate\Database\Seeder;

class HideSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $hide = ['Total Amount', 'Subtotal Amount', 'Total Weight', 'Total Quantity', 'Sku', 'Collections', 'Country', 'Zipcode', 'City', 'Total Spend', 'State/Province Code', 'Customer Tags', 'Delivery/Shipping Title', 'Total Discount', 'Discount Rate', 'Shipping Cost', 'Currency Code'];
        Customization::updateOrCreate(['name' => Customization::HIDE], [
            'filter' => json_encode($hide),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
