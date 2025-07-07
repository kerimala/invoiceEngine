# Invoice Engine

<p align="center">
  <a href="https://github.com/your-repo/invoiceEngine/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
  <a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About The Project

The Invoice Engine is a web application built with Laravel (v12) designed to automate the process of invoicing. It ingests invoice files (e.g., `.xlsx`, `.csv`), parses them, applies pricing rules, assembles a final invoice, generates a PDF, and sends it out.

The application is architected as a **modular monolith**, where the core business logic is broken down into independent, reusable internal packages. This design provides the organizational benefits of microservices without the operational complexity of a distributed system.

## Getting Started

Follow these steps to get a local copy up and running.

### Prerequisites

- PHP ^8.2
- Composer
- A local database (e.g., SQLite, MySQL)

### Installation

1.  **Clone the repository:**
    ```sh
    git clone https://github.com/your-repo/invoiceEngine.git
    cd invoiceEngine
    ```

2.  **Install PHP dependencies:**
    ```sh
    composer install
    ```

3.  **Set up your environment:**
    Copy the example environment file and generate your application key.
    ```sh
    cp .env.example .env
    php artisan key:generate
    ```

4.  **Configure your database:**
    Update the `DB_*` variables in your `.env` file with your database credentials.

5.  **Run database migrations:**
    ```sh
    php artisan migrate
    ```

6.  **Run the development server:**
    The project includes a concurrent script to run the server, queue worker, and log viewer.
    ```sh
    composer dev
    ```
    Alternatively, you can run the server on its own:
    ```sh
    php artisan serve
    ```

## Architecture Overview

The Invoice Engine is designed as a **modular monolith**. The core business logic is divided into independent, reusable **internal packages** located in the `/packages` directory. Each package has a specific responsibility (e.g., parsing invoices, calculating prices).

-   `/app`: The main Laravel application shell that handles HTTP requests and the user interface.
-   `/packages`: Contains the internal "microservices" or modules:
    -   `AgreementService`
    -   `InvoiceFileIngest`
    -   `InvoiceParser`
    -   `PricingEngine`
    -   `InvoiceAssembler`
    -   `PdfRenderer`
    -   `InvoiceSender`

This modular approach enforces a clear Separation of Concerns and makes the codebase easier to maintain and test.

## Usage

To test the invoicing pipeline manually, you can use the built-in file upload interface.

1.  Make sure your local server is running (`php artisan serve`).
2.  Navigate to `/invoice/upload` in your browser.
3.  Upload an invoice file to trigger the processing workflow.

## Event-Driven Workflow

The application uses an event-driven architecture to decouple its internal packages. When a package completes a task, it dispatches an event, and other packages listen for these events to trigger their own processes.

The main event chain is:
1.  `FileStored` → Triggers invoice parsing.
2.  `CarrierInvoiceLineExtracted` → Triggers price application.
3.  `PricedInvoiceLine` → Triggers final invoice assembly.
4.  `InvoiceAssembled` → Triggers PDF rendering and sending.

This workflow is managed by the `spatie/laravel-event-sourcing` package.

## Testing

The project uses **PestPHP** for testing. Tests are located in two places:
-   `/tests`: For high-level feature and integration tests.
-   `/packages/*/tests`: For unit tests that cover the logic of each package in isolation.

To run the entire test suite, use the following Composer script:
```sh
composer test
```

## Key Dependencies

The application relies on several key third-party packages:
-   **`phpoffice/phpspreadsheet`**: For reading `.xlsx` and `.csv` files.
-   **`spatie/laravel-event-sourcing`**: For the event-driven architecture.
-   **`spatie/laravel-pdf`**: For generating PDF invoices.

## Contributing

Thank you for considering contributing to the Invoice Engine! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Invoice Engine is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
