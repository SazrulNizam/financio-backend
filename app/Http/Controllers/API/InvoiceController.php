<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\API\ListInvoiceRequest;
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
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
