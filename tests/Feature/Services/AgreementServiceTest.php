<?php

namespace Tests\Feature\Services;

use App\Models\Agreement;
use Database\Seeders\AgreementSeeder;
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
        $this->assertEquals('custom', $result['agreement_type']);
    }



    /** @test */
    public function it_falls_back_to_standard_agreement(): void
    {
        // Arrange
        $this->seed(AgreementSeeder::class);

        // Act
        $result = $this->agreementService->getAgreementForCustomer('new-customer');

        // Assert
        $this->assertNotNull($result);
        $this->assertEquals('standard', $result['agreement_type']);
        $this->assertEquals('standard', $result['strategy']);
        $this->assertEquals(1.0, $result['multiplier']);
    }

    /** @test */
    public function it_returns_latest_valid_agreement_version(): void
    {
        // Arrange
        $customerId = 'customer-123';
        $oldAgreement = $this->agreementService->createNewVersion($customerId, [
            'strategy' => 'standard',
            'multiplier' => 1.0,
            'vat_rate' => 0.21,
            'currency' => 'EUR',
            'language' => 'en',
            'rules' => ['base_charge_column' => 'Weight'],
        ]);

        // Create a newer version that's valid from tomorrow
        $futureAgreement = Agreement::create([
            'customer_id' => $customerId,
            'version' => 2,
            'strategy' => 'premium',
            'multiplier' => 1.5,
            'vat_rate' => 0.21,
            'currency' => 'EUR',
            'language' => 'en',
            'rules' => ['base_charge_column' => 'Weight'],
            'valid_from' => now()->addDay(),
        ]);

        // Act
        $result = $this->agreementService->getAgreementForCustomer($customerId);

        // Assert
        $this->assertNotNull($result);
        $this->assertEquals($oldAgreement->id, $result['id']);
        $this->assertEquals('standard', $result['strategy']);
    }
}
