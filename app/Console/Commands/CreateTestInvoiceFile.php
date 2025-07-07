<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class CreateTestInvoiceFile extends Command
{
    protected $signature = 'app:create-test-invoice-file {filename=test_invoice.xlsx}';
    protected $description = 'Create a test XLSX invoice file';

    public function handle()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set headers
        $headers = [
            'Billing Account', 'Shipment Number', 'Shipment Date', 'Shipment Reference 1', 'Shipment Reference 2', 'Shipment Reference 3', 'Product Name', 'Pieces', 'Senders Name', 'Senders Address 1', 'Senders Address 2', 'Senders Address 3', 'Senders Postcode', 'Dest Country Code', 'Receivers Name', 'Receivers Address 1', 'Receivers Address 2', 'Receivers Address 3', 'Receivers Postcode', 'Weight (kg)', 'Tax Code', 'Weight Charge', 'XC1 Name', 'XC1 Charge', 'XC2 Name', 'XC2 Charge', 'XC3 Name', 'XC3 Charge', 'XC4 Name', 'XC4 Charge', 'XC5 Name', 'XC5 Charge', 'XC6 Name', 'XC6 Charge', 'Nett', 'VAT', 'Total'
        ];
        $sheet->fromArray($headers, null, 'A1');

        // Add data rows
        $data = [
            ['6149199', '69439722', '20240801', 'TXCOS24005', 'JVGL06149', '6149199', 'DHL Europlus', 1, 'MERPO (EXT)', 'HEYDWG 10', null, 'SWALMEN', '6071PT', 'BE', 'ALESSANDR', 'Meelblok 2', 'BEKKERZEEL', null, '1730', 12, 'A', 8.36, 'Brandstof', 1.27, 'GoGreen products', 0.03, 'Tol België', 0.37, null, null, null, null, null, null, 10.04, 2.11, 12.14],
            ['6149199', '69439739', '20240801', 'TXCOS24005', 'JVGL06149', '6149199', 'DHL Europlus', 1, 'MERPO (EXT)', 'HEYDWG 10', null, 'SWALMEN', '6071PT', 'BE', 'RIN ISHIKAWA', 'Rue Kelle 12', 'WOLUWE-SAINT-PIERRE', null, '1150', 1, 'A', 8.36, 'Brandstof', 1.27, 'GoGreen products', 0.03, 'Extra handling', null, 'Tol België', 0.37, 'Niet-leverbaar', 17.03, null, null, 27.06, 5.68, 32.75],
            ['6149199', '88754471', '20240802', 'TXCOS24005', 'JVGL06149', '6149199', 'DHL Europlus', 1, 'MERPO (EXT)', 'HEYDWG 10', null, 'SWALMEN', '6071PT', 'BE', 'NADEZDA K', 'Lokaertlaan', 'TERVUREN', null, '3080', 12, 'A', 8.36, 'Brandstof', 1.27, 'GoGreen products', 0.03, 'Tol België', 0.37, null, null, null, null, null, null, 10.04, 2.11, 12.14],
            ['6149199', '88860562', '20240805', 'TXCOS24005', 'JVGL06149', '6149199', 'DHL Europlus', 1, 'MERPO (EXT)', 'HEYDWG 10', null, 'SWALMEN', '6071PT', 'BE', 'DMITRY POI', 'Patrijzenstr', 'EVERBERG', null, '3078', 6, 'A', 8.36, 'Brandstof', 1.27, 'GoGreen products', 0.03, 'Tol België', 0.37, null, null, null, null, null, null, 10.04, 2.11, 12.14],
        ];
        $sheet->fromArray($data, null, 'A2');

        $writer = new Xlsx($spreadsheet);
        $filename = $this->argument('filename');
        $writer->save($filename);

        $this->info("Test invoice file created: {$filename}");
    }
} 