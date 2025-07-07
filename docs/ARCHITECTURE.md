# Invoice Engine Architecture

This document describes the technical architecture of the Invoice Engine.

## 1. How is the engine set up?

The Invoice Engine is built as a **modular monolith** using the **Laravel (v12)** framework.

-   **Monolith:** The entire application is deployed and runs as a single service.
-   **Modular:** The core business logic is divided into independent, reusable **internal packages** located in the `/packages` directory. Each package has a specific business responsibility (e.g., parsing invoices, calculating prices).

These packages are loaded into the main Laravel application via `composer.json`. The Laravel application in the `/app` directory acts as a "shell" that handles HTTP requests, calls services from the packages, and serves the user interface.

## 2. How is the engine modular, micro-serviced, DRY, and test-driven?

-   **Modular:** The most obvious implementation is the `/packages` directory. Each subdirectory is a self-contained module with its own `src` and `tests` directories. This enforces a clear Separation of Concerns.

-   **"Micro-serviced":** Although it is a monolith, the architecture internally mimics a microservice design. The packages (`InvoiceAssembler`, `PricingEngine`, etc.) act like internal microservices that communicate through well-defined interfaces (class methods and events). This avoids the operational complexity of a distributed system while offering similar benefits in code organization.

-   **DRY (Don't Repeat Yourself):** The logic for a specific domain is encapsulated within a single package. For example, if the pricing calculation needs to be changed, the modification happens in only one place: the `PricingEngine` package.

-   **Test-Driven:** The project uses **PestPHP** for testing. Both the main application (`/tests`) and each individual package (`/packages/*/tests`) have their own test suites. This allows every module to be tested in isolation and ensures that new features are covered by tests.

## 3. Where are the tests and the "microservices"?

-   **Microservices (Internal Packages):** They are located in the `/packages` directory. The current packages are:
    -   `AgreementService`
    -   `InvoiceFileIngest`
    -   `InvoiceParser`
    -   `PricingEngine`
    -   `InvoiceAssembler`
    -   `PdfRenderer`
    -   `InvoiceSender`
    -   `UnitConverter` (includes FormattingService for locale-based formatting)

-   **Tests:** The tests are found in two locations:
    1.  In the root `/tests` directory for high-level Feature and Unit tests that cover the interaction between packages.
    2.  Inside the respective test directories of each package (e.g., `/packages/InvoiceParser/tests`) to test the internal logic of each module in isolation.

## 4. How do the tests work?

The tests are built on the **PestPHP Framework**. Pest is an elegant testing framework that focuses on developer experience and promotes a readable, behavior-driven syntax. A typical test case follows the **Arrange-Act-Assert** pattern:
1.  **Arrange:** Set up the necessary preconditions and objects (e.g., create a sample file, mock a service).
2.  **Act:** Execute the method or function that is being tested.
3.  **Assert:** Verify that the outcome matches the expectation (e.g., the result is correct, a specific event was dispatched).

Tests can be run from the command line using `composer test`.

## 5. Are we using a vendor package?

Yes, the entire application relies on third-party packages managed by **Composer** in the `/vendor` directory. This directory contains *all* external dependencies, including the Laravel framework itself.

Key vendor packages of particular importance to our application logic are:
-   **`phpoffice/phpspreadsheet`**: Used by the `InvoiceParser` to read `.xlsx` and `.csv` files.
-   **`spatie/laravel-event-sourcing`**: This package provides the foundation for our event-driven architecture. It simplifies the process of storing, tracking, and reacting to domain events.
-   **`spatie/laravel-pdf`**: This package is used (within the `PdfRenderer` package) to generate PDF invoices from HTML templates with locale-based formatting support.

## 6. Are we using events?

Yes, events are a core part of the architecture, enabling loose coupling between the internal packages. When a package completes its task, it dispatches an event. Other packages can then "listen" for these events and trigger their own processes.

We use the `spatie/laravel-event-sourcing` package to manage this workflow.

### Event Chain

The event-driven nature of the application creates a logical processing pipeline. Based on the service implementations and business logic, the chain of events is as follows:

1.  `FileStored`: Dispatched by `InvoiceFileIngestService` after a new invoice file has been uploaded and stored.
    -   **Listened to by:** `InvoiceParserService` (to begin parsing).

2.  `CarrierInvoiceLineExtracted`: Dispatched by `InvoiceParserService` after the data has been successfully extracted from the file.
    -   **Listened to by:** `ApplyPricing` listener in the `PricingEngine`.

3.  `PricedInvoiceLine`: Dispatched by the `ApplyPricing` listener for each processed line. The last line includes a `last_line` flag.
    -   **Listened to by:** `InvoiceAssemblerService` (to assemble the final invoice).

4.  `InvoiceAssembled`: Dispatched by `InvoiceAssemblerService` after the final invoice data structure has been created.
    -   **Listened to by:** `PdfRendererService` (to create the locale-formatted PDF) and/or `InvoiceSenderService` (to send the invoice).

This event-driven chain ensures that each "microservice" (package) operates independently and only reacts when the previous step in the process is complete.

## Locale-Based Formatting

The Invoice Engine supports internationalization through locale-based formatting:

### FormattingService

The `FormattingService` (part of the `UnitConverter` package) provides:
- **Currency Formatting**: Locale-specific currency display (e.g., "1.234,56 €" for German, "$1,234.56" for English)
- **Number Formatting**: Regional decimal and thousands separators
- **Unit Conversion**: Weight and distance formatting with appropriate units
- **Fallback Support**: Graceful degradation for unsupported locales

### PDF Integration

The `PdfRenderer` integrates with the formatting system to:
- Accept Agreement objects containing locale preferences
- Apply locale-specific formatting to all numerical data in PDFs
- Maintain backward compatibility with existing invoice generation

### Supported Locales

- **German (de)**: Comma decimal, period thousands separator
- **English (en)**: Period decimal, comma thousands separator
- **French (fr)**: Comma decimal, space thousands separator
- **Extensible**: New locales can be easily added

## 7. Manual Testing

To facilitate manual testing of the invoice processing pipeline, a simple file upload interface has been created. This allows a user to upload an invoice file directly and trigger the ingestion process without needing to use an API client or a command-line script.

### How it works:

1.  **Route:** New routes have been added to `routes/web.php`:
    -   `GET /invoice/upload`: Displays the file upload form.
    -   `POST /invoice/upload`: Handles the file submission.

2.  **Controller:** The `App\Http\Controllers\InvoiceController` handles the web requests for this feature.
    -   The `create()` method returns the view for the upload form.
    -   The `store()` method validates the uploaded file and passes its path to the `InvoiceFileIngestService`.

3.  **View:** A simple Blade view at `resources/views/invoice/create.blade.php` provides the HTML form for the file upload.

4.  **Service Registration:** All services are registered in their respective Service Providers to allow for dependency injection.

To use it, run the local server (`php artisan serve`) and navigate to `/invoice/upload` in your browser.