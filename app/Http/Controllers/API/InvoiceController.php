<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\API\ListInvoiceRequest;
use App\Http\Requests\API\StoreInvoiceRequest;
use App\Http\Requests\API\UpdateInvoiceRequest;
use App\Http\Resources\InvoiceResource;
use App\Models\Invoice;

class InvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(ListInvoiceRequest $request)
    {
        $sortBy    = $request->validated('sort_by', 'created_at');
        $sortOrder = $request->validated('sort_order', 'desc');
        $perPage   = $request->validated('per_page', 10);

        // 2. Query database dengan Eager Loading
        $invoices = Invoice::orderBy($sortBy, $sortOrder)
            ->paginate($perPage);

        // 3. Maximize API Resource: 
        // Laravel automatik buat "data", "meta", dan "links"
        // Kita guna ->additional() untuk selitkan "status" dan "message"
        return InvoiceResource::collection($invoices)->additional([
            'status'  => 'success',
            'message' => 'Invoices retrieved successfully'
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreInvoiceRequest $request)
    {
        $invoice = DB::transaction(function () use ($request) {

            // Calculate total_amount per item, never trust client value
            $items = collect($request->validated('items'))->map(fn($item) => [
                'product_name' => $item['product_name'],
                'unit_price'   => $item['unit_price'],
                'quantity'     => $item['quantity'],
                'total_amount' => $item['unit_price'] * $item['quantity'],
            ]);

            // Create invoice, amount = sum of all items
            $invoice = Invoice::create([
                'invoice_number' => $request->validated('invoice_number'),
                'customer_name'  => $request->validated('customer_name'),
                'invoice_date'   => $request->validated('invoice_date'),
                'reference'      => $request->validated('reference'),
                'amount'         => $items->sum('total_amount'),
            ]);

            // Insert all items linked to this invoice via relationship
            $invoice->items()->createMany($items->toArray());

            return $invoice;
        });

        return (new InvoiceResource($invoice->load('items')))
            ->additional([
                'status'  => 'success',
                'message' => 'Invoice created successfully',
            ])
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Invoice $invoice)
    {
        return (new InvoiceResource($invoice->load('items')))
            ->additional([
                'status'  => 'success',
                'message' => 'Invoice retrieved successfully',
            ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateInvoiceRequest $request, Invoice $invoice)
    {
        $invoice = DB::transaction(function () use ($request, $invoice) {

            // Calculate total_amount per item
            $items = collect($request->validated('items'))->map(fn($item) => [
                'product_name' => $item['product_name'],
                'unit_price'   => $item['unit_price'],
                'quantity'     => $item['quantity'],
                'total_amount' => $item['unit_price'] * $item['quantity'],
            ]);

            // Update invoice fields + recalculate amount
            $invoice->update([
                'invoice_number' => $request->validated('invoice_number'),
                'customer_name'  => $request->validated('customer_name'),
                'invoice_date'   => $request->validated('invoice_date'),
                'reference'      => $request->validated('reference'),
                'amount'         => $items->sum('total_amount'),
            ]);

            // Delete old items, insert new items
            $invoice->items()->delete();
            $invoice->items()->createMany($items->toArray());

            return $invoice;
        });

        return (new InvoiceResource($invoice->load('items')))
            ->additional([
                'status'  => 'success',
                'message' => 'Invoice updated successfully',
            ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Invoice $invoice)
    {
        $invoice->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Invoice deleted successfully',
        ]);
    }
}
