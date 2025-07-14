<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use App\Models\Agreement;
use Packages\AgreementService\App\Services\AgreementService;

class ExcelToPdfTest extends TestCase
{
    use RefreshDatabase;

    public function test_excel_upload_to_pdf_rendering()
    {
        // Arrange: Create a fake disk for storage
        Storage::fake('local');
        Storage::fake('invoices');

        // Arrange: Create a test agreement
        $agreement = Agreement::factory()->create([
            'customer_id' => 'test-customer', // This will be the value of 'Billing Account'
            'rules' => [
                'base_charge_column' => 'price',
                'surcharge_prefix' => 'surcharge_',
                'surcharge_suffix' => '_fee',
            ]
        ]);

        // Arrange: Create a dummy Excel file
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'Billing Account');
        $sheet->setCellValue('B1', 'price');
        $sheet->setCellValue('A2', 'test-customer');
        $sheet->setCellValue('B2', 100);

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $tempFilePath = tempnam(sys_get_temp_dir(), 'invoice');
        $writer->save($tempFilePath);

        $uploadedFile = new UploadedFile($tempFilePath, 'invoice.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);

        // Act: Upload the file
        $response = $this->post(route('invoice.store'), [
            'invoice_file' => $uploadedFile,
        ]);

        // Assert: File was stored
        $response->assertStatus(200);
$this->assertTrue(Storage::disk('local')->exists('invoices/' . $uploadedFile->hashName()));

        // This part of the test will require more information about how the PDF is generated and stored.
        // For now, we've tested the upload part.
        // We will need to investigate the PdfRenderer package to complete this test.
    }
}