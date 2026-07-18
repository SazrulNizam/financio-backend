<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Invoice;

class InvoiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //Invois 1
        $inv1 = Invoice::create([
            'invoice_number' => 'INV-2026-001',
            'customer_name'  => 'Muhammad Sazrul',
            'invoice_date'   => '2026-07-17',
            'reference'      => 'REF-001',
            'amount'         => 135.00,
        ]);

        $inv1->items()->createMany([
            [
                'product_name' => 'Keyboard', 
                'unit_price'   => 45.00, 
                'quantity'     => 2, 
                'total_amount' => 90.00
            ],
            [
                'product_name' => 'Mouse', 
                'unit_price'   => 15.00, 
                'quantity'     => 3, 
                'total_amount' => 45.00
            ],
        ]);

       
        // Invois 2
   
        $inv2 = Invoice::create([
            'invoice_number' => 'INV-2026-002', 
            'customer_name'  => 'Ali Bin Abu',   
            'invoice_date'   => '2026-07-17',
            'reference'      => 'REF-002',
            'amount'         => 500.00,
        ]);

        $inv2->items()->create([
            'product_name' => 'Monitor 24 Inch', 
            'unit_price'   => 500.00, 
            'quantity'     => 1, 
            'total_amount' => 500.00,
        ]);
    }
}
