<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number');
            $table->string('customer_name');
            $table->date('invoice_date');
            $table->string('reference')->nullable();
            $table->decimal('amount', 15, 2)->default(0.00);
            $table->timestamps();
            $table->softDeletes();
            $table->unique(
                ['customer_name', 'invoice_number', DB::raw('(YEAR(invoice_date))'), 'deleted_at'],
                'invoice_customer_year_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
