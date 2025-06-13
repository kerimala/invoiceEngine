<?php

namespace Packages\InvoiceParser\Services;

use Packages\InvoiceParser\Events\CarrierInvoiceLineExtracted;
use Illuminate\Support\Facades\Event;
use PhpOffice\PhpSpreadsheet\IOFactory;

class InvoiceParserService
{
    /**
     * Parse an invoice file and emit CarrierInvoiceLineExtracted event.
     *
     * @param string $filePath
     * @throws \Exception
     */
    public function parse(string $filePath)
    {
        if (!file_exists($filePath)) {
            throw new \Exception('Invoice file not found at path: ' . $filePath);
        }
        if (!is_file($filePath)) {
            throw new \Exception('Path is not a valid file: ' . $filePath);
        }
        if (!is_readable($filePath)) {
            throw new \Exception('File is not readable: ' . $filePath);
        }
        if (filesize($filePath) === 0) {
            throw new \Exception('File is empty: ' . $filePath);
        }
        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        if (!in_array($ext, ['csv', 'xml', 'txt', 'xlsx'])) {
            throw new \Exception('Unsupported file format: ' . $ext);
        }

        if ($ext === 'csv' || $ext === 'xlsx' || $ext === 'txt') {
            $spreadsheet = IOFactory::load($filePath);
            $worksheet = $spreadsheet->getActiveSheet();
            $header = [];
            $parsedLines = [];
            foreach ($worksheet->getRowIterator() as $row) {
                $cellIterator = $row->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(FALSE); 
                $lineData = [];
                foreach ($cellIterator as $cell) {
                    $lineData[] = $cell->getValue();
                }

                if (empty($header)) {
                    $header = $lineData;
                    continue;
                }
                
                $parsedLines[] = array_combine($header, $lineData);
            }

            if (count($parsedLines) === 0) {
                throw new \Exception('No data lines found in file: ' . $filePath);
            }
            Event::dispatch(new CarrierInvoiceLineExtracted($filePath, count($parsedLines), $parsedLines));

        } elseif ($ext === 'xml') {
            $xmlContent = file_get_contents($filePath);
            // Handle BOM
            $xmlContent = preg_replace('/^\xEF\xBB\xBF/', '', $xmlContent);
            libxml_use_internal_errors(true);
            $xml = simplexml_load_string($xmlContent);
            if ($xml === false) {
                throw new \Exception('Malformed XML: ' . $filePath);
            }
            $parsedLines = [];
            foreach ($xml->line as $line) {
                $row = [];
                foreach ($line as $k => $v) {
                    $row[(string)$k] = (string)$v;
                }
                $parsedLines[] = $row;
            }
            if (count($parsedLines) === 0) {
                throw new \Exception('No <line> elements found in XML: ' . $filePath);
            }
            Event::dispatch(new CarrierInvoiceLineExtracted($filePath, count($parsedLines), $parsedLines));
        }
    }
} 