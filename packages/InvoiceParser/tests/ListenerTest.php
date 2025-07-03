<?php

namespace Packages\InvoiceParser\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Packages\InvoiceFileIngest\Events\FileStored;
use Packages\InvoiceParser\Services\InvoiceParserService;
use Mockery;
use Tests\TestCase;
use Packages\InvoiceParser\Listeners\ParseInvoiceFile;

class ListenerTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        config(['queue.default' => 'sync']);
    }

    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_file_stored_event_triggers_parsing()
    {
        $filePath = 'path/to/my/invoice.csv';

        // Mock the InvoiceParserService
        $mockParserService = Mockery::mock(InvoiceParserService::class);
        $mockParserService->shouldReceive('parse')->once()->with($filePath);

        // Replace the service in the container with our mock
        $this->app->instance(InvoiceParserService::class, $mockParserService);

        // Dispatch the event (listener is already registered by service provider)
        Event::dispatch(new FileStored($filePath, 12345, []));

        $this->assertTrue(true);
    }
}