<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Company;
use App\Models\ApiLog;

class InvoiceCreationTest extends TestCase
{
    use RefreshDatabase;

    public function it_creates_invoice_and_logs_attempt()
    {
        $company = Company::factory()->create(['company_name' => 'TestCorp']);
        $payload = [
            'company_id' => $company->id,
            'invoice_id' => 'Test-INV-1',
            'lines' => [
                ['description' => 'Test Desc', 'qty' => 2, 'price' => 100, 'vat' => 21],
                ['description' => 'Second', 'qty' => 1, 'price' => 50, 'vat' => 9],
            ]
        ];

        $response = $this->post('/invoice', $payload);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('invoices', [
            'company_name' => 'TestCorp',
            'invoice_id' => 'Test-INV-1',
        ]);


        $this->assertDatabaseHas('api_logs', ['level' => 'info', 'message' => 'Attempt to create invoice']);
        $this->assertDatabaseHas('api_logs', ['level' => 'info', 'message' => 'Invoice created']);
    }

    /** @test */
    public function it_fails_and_logs_if_company_missing()
    {
        $payload = [
            'company_id' => 9999,
            'invoice_id' => 'X',
            'lines' => [
                ['description' => 'Test', 'qty' => 1, 'price' => 1, 'vat' => 21]
            ]
        ];

        $response = $this->post('/invoice', $payload);

        $response->assertSessionHasErrors();
        $this->assertDatabaseHas('api_logs', ['level' => 'error']);
    }
}