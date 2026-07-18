<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'invoice_number' => $this->invoice_number,
            'customer_name'  => $this->customer_name,
            'invoice_date'   => $this->invoice_date,
            'reference'      => $this->reference,
            'amount'         => (float) $this->amount,
            'created_at'     => $this->created_at?->format('Y-m-d H:i:s'),

            'items'          => InvoiceItemResource::collection($this->whenLoaded('items')),
        ];
    }
}
