<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
    //

    protected $fillable = [
        'invoice_id', 'product_name', 'unit_price', 'quantity', 'total_amount'
    ];

    protected $casts = [
        'unit_price'   => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}
