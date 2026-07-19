<?php

namespace Tests\Feature;

use App\Models\Invoice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceApiTest extends TestCase
{
    use RefreshDatabase;

    // ─────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────

    private function invoicePayload(array $overrides = []): array
    {
        return array_merge([
            'invoice_number' => 'INV-001',
            'customer_name'  => 'Ahmad',
            'invoice_date'   => '2026-07-18',
            'reference'      => 'REF-001',
            'items'          => [
                ['product_name' => 'Pen',      'unit_price' => 2.50,  'quantity' => 4],
                ['product_name' => 'Notebook', 'unit_price' => 10.00, 'quantity' => 2],
            ],
        ], $overrides);
    }

    private function createInvoice(array $overrides = []): Invoice
    {
        $invoice = Invoice::create(array_merge([
            'invoice_number' => 'INV-001',
            'customer_name'  => 'Ahmad',
            'invoice_date'   => '2026-07-18',
            'reference'      => 'REF-001',
            'amount'         => '30.00',
        ], $overrides));

        $invoice->items()->createMany([
            ['product_name' => 'Pen',      'unit_price' => '2.50',  'quantity' => 4, 'total_amount' => '10.00'],
            ['product_name' => 'Notebook', 'unit_price' => '10.00', 'quantity' => 2, 'total_amount' => '20.00'],
        ]);

        return $invoice;
    }

    // ─────────────────────────────────────────────
    // INDEX
    // ─────────────────────────────────────────────

    public function test_can_list_invoices(): void
    {
        $this->createInvoice();

        $response = $this->getJson('/api/invoices');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [['id', 'invoice_number', 'customer_name', 'amount']],
                'status',
                'message',
                'meta',
                'links',
            ])
            ->assertJsonPath('status', 'success');
    }

    public function test_list_invoices_returns_empty_when_no_data(): void
    {
        $response = $this->getJson('/api/invoices');

        $response->assertStatus(200)
            ->assertJsonPath('data', []);
    }

    public function test_list_invoices_supports_pagination(): void
    {
        $this->createInvoice(['invoice_number' => 'INV-001']);
        $this->createInvoice(['invoice_number' => 'INV-002', 'customer_name' => 'Ali']);

        $response = $this->getJson('/api/invoices?per_page=1');

        $response->assertStatus(200)
            ->assertJsonPath('meta.per_page', 1)
            ->assertJsonCount(1, 'data');
    }

    public function test_list_invoices_fails_with_invalid_sort(): void
    {
        $response = $this->getJson('/api/invoices?sort_by=invalid_column');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['sort_by']);
    }

    // ─────────────────────────────────────────────
    // STORE
    // ─────────────────────────────────────────────

    public function test_can_create_invoice(): void
    {
        $response = $this->postJson('/api/invoices', $this->invoicePayload());

        $response->assertStatus(201)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.invoice_number', 'INV-001')
            ->assertJsonPath('data.amount', '30.00')
            ->assertJsonCount(2, 'data.items');

        $this->assertDatabaseHas('invoices', ['invoice_number' => 'INV-001']);
        $this->assertDatabaseCount('invoice_items', 2);
    }

    public function test_create_invoice_calculates_amount_automatically(): void
    {
        $response = $this->postJson('/api/invoices', $this->invoicePayload());

        // 2.50 * 4 = 10.00, 10.00 * 2 = 20.00, total = 30.00
        $response->assertStatus(201)
            ->assertJsonPath('data.amount', '30.00');
    }

    public function test_create_invoice_fails_without_required_fields(): void
    {
        $response = $this->postJson('/api/invoices', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'invoice_number',
                'customer_name',
                'invoice_date',
                'items',
            ]);
    }

    public function test_create_invoice_fails_without_items(): void
    {
        $response = $this->postJson('/api/invoices', $this->invoicePayload(['items' => []]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['items']);
    }

    public function test_create_invoice_fails_with_invalid_item_fields(): void
    {
        $response = $this->postJson('/api/invoices', $this->invoicePayload([
            'items' => [
                ['product_name' => '', 'unit_price' => -1, 'quantity' => 0],
            ],
        ]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'items.0.product_name',
                'items.0.unit_price',
                'items.0.quantity',
            ]);
    }

    public function test_create_invoice_fails_duplicate_number_same_customer_same_year(): void
    {
        $this->createInvoice();

        $response = $this->postJson('/api/invoices', $this->invoicePayload());

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['invoice_number']);
    }

    public function test_create_invoice_allows_same_number_different_customer(): void
    {
        $this->createInvoice(['customer_name' => 'Ali']);

        $response = $this->postJson('/api/invoices', $this->invoicePayload([
            'customer_name' => 'Ahmad',
        ]));

        $response->assertStatus(201);
    }

    // ─────────────────────────────────────────────
    // SHOW
    // ─────────────────────────────────────────────

    public function test_can_show_invoice_with_items(): void
    {
        $invoice = $this->createInvoice();

        $response = $this->getJson("/api/invoices/{$invoice->id}");

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.id', $invoice->id)
            ->assertJsonCount(2, 'data.items');
    }

    public function test_show_invoice_returns_404_when_not_found(): void
    {
        $response = $this->getJson('/api/invoices/999');

        $response->assertStatus(404);
    }

    // ─────────────────────────────────────────────
    // UPDATE
    // ─────────────────────────────────────────────

    public function test_can_update_invoice(): void
    {
        $invoice = $this->createInvoice();

        $response = $this->putJson("/api/invoices/{$invoice->id}", $this->invoicePayload([
            'customer_name' => 'Ahmad Updated',
            'items'         => [
                ['product_name' => 'Stapler', 'unit_price' => 5.00, 'quantity' => 3],
            ],
        ]));

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.customer_name', 'Ahmad Updated')
            ->assertJsonPath('data.amount', '15.00')
            ->assertJsonCount(1, 'data.items');

        $this->assertDatabaseHas('invoices', ['customer_name' => 'Ahmad Updated']);
        $this->assertDatabaseCount('invoice_items', 1);
    }

    public function test_update_invoice_replaces_old_items(): void
    {
        $invoice = $this->createInvoice();

        $this->assertDatabaseCount('invoice_items', 2);

        $this->putJson("/api/invoices/{$invoice->id}", $this->invoicePayload([
            'items' => [
                ['product_name' => 'Stapler', 'unit_price' => 5.00, 'quantity' => 1],
            ],
        ]));

        $this->assertDatabaseCount('invoice_items', 1);
    }

    public function test_update_invoice_fails_without_required_fields(): void
    {
        $invoice = $this->createInvoice();

        $response = $this->putJson("/api/invoices/{$invoice->id}", []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'invoice_number',
                'customer_name',
                'invoice_date',
                'items',
            ]);
    }

    public function test_update_invoice_allows_same_invoice_number_on_itself(): void
    {
        $invoice = $this->createInvoice();

        $response = $this->putJson("/api/invoices/{$invoice->id}", $this->invoicePayload());

        $response->assertStatus(200);
    }

    public function test_update_invoice_returns_404_when_not_found(): void
    {
        $response = $this->putJson('/api/invoices/999', $this->invoicePayload());

        $response->assertStatus(404);
    }

    // ─────────────────────────────────────────────
    // DESTROY
    // ─────────────────────────────────────────────

    public function test_can_delete_invoice(): void
    {
        $invoice = $this->createInvoice();

        $response = $this->deleteJson("/api/invoices/{$invoice->id}");

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success');

        $this->assertSoftDeleted('invoices', ['id' => $invoice->id]);
    }

    public function test_deleted_invoice_not_visible_in_list(): void
    {
        $invoice = $this->createInvoice();
        $invoice->delete();

        $response = $this->getJson('/api/invoices');

        $response->assertStatus(200)
            ->assertJsonCount(0, 'data');
    }

    public function test_deleted_invoice_returns_404_on_show(): void
    {
        $invoice = $this->createInvoice();
        $invoice->delete();

        $response = $this->getJson("/api/invoices/{$invoice->id}");

        $response->assertStatus(404);
    }

    public function test_delete_invoice_returns_404_when_not_found(): void
    {
        $response = $this->deleteJson('/api/invoices/999');

        $response->assertStatus(404);
    }
}
