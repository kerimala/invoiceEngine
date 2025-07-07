# Agreement Service and Architecture

This document outlines the architecture of the Agreement Service, its integration with the testing suite, and other relevant changes.

## Agreement Service

The `AgreementService` is responsible for managing customer agreements, which are now versioned. It retrieves agreement details from the database, which are then used by the `PricingEngine` to calculate invoice line items.

### Versioning

To support versioning, the `agreements` table has been modified:

- The unique constraint on `customer_id` has been removed.
- A `valid_from` timestamp has been added to track when a version becomes active.
- A new unique constraint has been added to the combination of `customer_id` and `version`.

The `AgreementService` has been updated to handle this new structure:

- `getAgreementForCustomer()`: Fetches the latest agreement version for a customer based on the `valid_from` timestamp.
- `createNewVersion(string $customerId, array $data)`: Creates a new agreement version for a customer, incrementing the version number and setting the `valid_from` timestamp.

### Service Provider

The `AgreementService` uses a dedicated service provider, `AgreementServiceProvider`, to register itself with the Laravel application and to load its database migrations. The provider's `boot` method contains the following logic:

```php
public function boot(): void
{
    $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
}
```

This ensures that the `agreements` table is created when running the application's migrations.

## Testing

Significant refactoring was done to ensure the test suite is robust and reliable. The key changes are:

1.  **Database Migrations**: The tests now use the `RefreshDatabase` trait, which ensures that the database is migrated before each test, providing a clean and predictable state.

2.  **Service Container**: Services are no longer instantiated manually in tests. Instead, they are resolved from Laravel's service container using `$this->app->make()`. This ensures that all dependencies are correctly injected and that the services are in a valid state.

3.  **Data Seeding**: Tests that rely on database records now use the `AgreementService` to create versioned agreements. For example, the `ApplyPricingTest` creates an agreement before dispatching the event that triggers the pricing logic:

    ```php
    $agreementService = $this->app->make(AgreementService::class);
    $agreementService->createNewVersion('some_customer_id', [
        'strategy' => 'standard',
        'multiplier' => 1.0,
        'vat_rate' => 21.0,
        'currency' => 'EUR',
        'language' => 'en',
        'rules' => [
            'base_charge_column' => 'Weight Charge',
            'surcharge_prefix' => 'SUR',
            'surcharge_suffix' => 'CHARGE',
        ],
    ]);
    ```

## SFTP Integration

There is currently no direct integration with an SFTP server in the codebase. The file ingestion process is handled locally from the `storage/temp_invoices` directory.

## Summary of Changes

-   Created `AgreementServiceProvider` and `InvoiceServiceProvider` to automatically load package migrations.
-   Registered the new service providers in `bootstrap/providers.php`.
-   Refactored all tests in the `PricingEngine` package to use `RefreshDatabase` and the service container.
-   Seeded an agreement in `ApplyPricingTest` to resolve a `TypeError`.
-   Removed temporary migration-loading logic from `tests/TestCase.php`.