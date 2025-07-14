<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Agreement;

class InvoiceGenerationTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_generate_an_invoice_for_an_agreement(): void
    {
        $agreement = Agreement::factory()->create();

        $response = $this->postJson(route('invoice.generate'), ['agreement_id' => $agreement->id]);

        $response->assertStatus(200)
            ->assertJson([
                'agreement_id' => $agreement->id,
                'customer_id' => $agreement->customer_id,
                'amount' => 100 * $agreement->multiplier,
            ]);
    }
}