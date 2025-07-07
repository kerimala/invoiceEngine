<?php

namespace Tests\Feature\Services;

use App\Models\Agreement;
use Database\Seeders\AgreementSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Packages\AgreementService\Services\AgreementService;
use Packages\AgreementService\Exceptions\AgreementMissingException;
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

    /** @test */
    public function it_throws_agreement_missing_exception_when_no_agreement_found(): void
    {
        // Arrange - no agreements in database
        
        // Act & Assert
        $this->expectException(AgreementMissingException::class);
        $this->expectExceptionCode(422);
        $this->expectExceptionMessage("Agreement Missing: No valid agreement found for customer 'non-existent-customer'");
        
        $this->agreementService->getAgreementForCustomer('non-existent-customer');
    }

    /** @test */
    public function it_throws_agreement_missing_exception_when_no_standard_agreement_exists(): void
    {
        // Arrange - create a custom agreement but no standard one
        Agreement::factory()->create([
            'customer_id' => 'custom-customer',
            'valid_from' => now()->subDay(),
        ]);
        
        // Act & Assert
        $this->expectException(AgreementMissingException::class);
        $this->expectExceptionCode(422);
        $this->expectExceptionMessage("Agreement Missing: No valid agreement found for customer 'different-customer'");
        
        $this->agreementService->getAgreementForCustomer('different-customer');
    }

    /** @test */
    public function agreement_missing_exception_contains_customer_id(): void
    {
        // Arrange
        $customerId = 'test-customer-123';
        
        try {
            // Act
            $this->agreementService->getAgreementForCustomer($customerId);
            $this->fail('Expected AgreementMissingException was not thrown');
        } catch (AgreementMissingException $e) {
            // Assert
            $this->assertEquals($customerId, $e->getCustomerId());
            $this->assertNull($e->getInvoiceId());
            $this->assertEquals(422, $e->getCode());
        }
    }
}
