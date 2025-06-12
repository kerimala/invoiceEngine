<?php

namespace Packages\InvoiceAssembler\Tests;

use Illuminate\Support\Facades\Cache;
use Packages\InvoiceAssembler\Services\InvoiceAssemblerService;
use Tests\TestCase;
use Illuminate\Support\Facades\Event;
use Packages\InvoiceAssembler\Events\InvoiceAssembled;

class InvoiceAssemblerServiceTest extends TestCase
{
    public function test_can_instantiate_service()
    {
        $service = new InvoiceAssemblerService();
        $this->assertInstanceOf(InvoiceAssemblerService::class, $service);
    }

    public function test_stores_priced_line_in_cache()
    {
        $service = new InvoiceAssemblerService();
        $pricedLine = ['item' => 'test', 'price' => 100];
        $filePath = 'path/to/invoice.csv';

        $service->assemble($pricedLine, $filePath);

        $cachedData = Cache::get($filePath);
        $this->assertCount(1, $cachedData);
        $this->assertEquals($pricedLine, $cachedData[0]);
    }

    public function test_dispatches_event_and_clears_cache_on_last_line()
    {
        Event::fake();
        $service = new InvoiceAssemblerService();
        $filePath = 'path/to/invoice.csv';
        $line1 = ['item' => 'test1', 'price' => 100];
        $line2 = ['item' => 'test2', 'price' => 200, 'last_line' => true];

        $service->assemble($line1, $filePath);
        $service->assemble($line2, $filePath);

        Event::assertDispatched(InvoiceAssembled::class, function ($event) use ($filePath) {
            return $event->invoiceData['filePath'] === $filePath && count($event->invoiceData['lines']) === 2;
        });

        $this->assertNull(Cache::get($filePath));
    }
} 