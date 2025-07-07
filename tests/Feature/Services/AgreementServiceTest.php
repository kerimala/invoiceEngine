<?php

namespace Tests\Feature\Services;

use App\Models\Agreement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Packages\AgreementService\Services\AgreementService;
use Tests\TestCase;

class AgreementServiceTest extends TestCase
{
    use RefreshDatabase;

    private AgreementService $agreementService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->agreementService = new AgreementService();
    }

    /** @test */
    public function it_can_get_agreement_for_a_customer(): void
    {
        // Arrange
        $agreementData = [
            'customer_id' => 'customer-123',
            'version' => 'v1.2',
            'strategy' => 'standard',
            'multiplier' => 1.15,
            'vat_rate' => 0.21,
            'currency' => 'EUR',
            'language' => 'en',
            'rules' => [
                'base_charge_column' => 'Weight Charge',
                'surcharge_prefix' => 'XC',
                'surcharge_suffix' => 'Charge',
            ],
        ];
        Agreement::factory()->create($agreementData);

        // Act
        $result = $this->agreementService->getAgreementForCustomer('customer-123');

        // Assert
        $this->assertNotNull($result);
        $this->assertEquals($agreementData['version'], $result['version']);
        $this->assertEquals($agreementData['strategy'], $result['strategy']);
        $this->assertEquals($agreementData['rules'], $result['rules']);
    }

    /** @test */
    public function it_returns_null_when_no_agreement_is_found(): void
    {
        // Act
        $result = $this->agreementService->getAgreementForCustomer('non-existent-customer');

        // Assert
        $this->assertNull($result);
    }
}
