<?php

namespace Packages\InvoiceParser\Services;

use Packages\InvoiceParser\Events\CarrierInvoiceLineExtracted;
use Illuminate\Support\Facades\Event;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Log;

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
        Log::info('Starting invoice parsing for file: ' . $filePath);

        if (!file_exists($filePath)) {
            Log::error('File not found during parsing: ' . $filePath);
            throw new \Exception('Invoice file not found at path: ' . $filePath);
        }
        if (!is_file($filePath)) {
            Log::error('Path is not a file during parsing: ' . $filePath);
            throw new \Exception('Path is not a valid file: ' . $filePath);
        }
        if (!is_readable($filePath)) {
            Log::error('File not readable during parsing: ' . $filePath);
            throw new \Exception('File is not readable: ' . $filePath);
        }
        if (filesize($filePath) === 0) {
            Log::error('File is empty during parsing: ' . $filePath);
            throw new \Exception('File is empty: ' . $filePath);
        }
        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        if (!in_array($ext, ['csv', 'xml', 'txt', 'xlsx'])) {
            Log::error('Unsupported file format during parsing: ' . $ext, ['filePath' => $filePath]);
            throw new \Exception('Unsupported file format: ' . $ext);
        }

        Log::info('File validation passed in InvoiceParserService. Starting parsing.', ['filePath' => $filePath, 'extension' => $ext]);

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
                    Log::info('InvoiceParserService: Header found', ['header' => $header, 'filePath' => $filePath]);
                    continue;
                }

                Log::info('InvoiceParserService: Processing line', ['line_data' => $lineData, 'filePath' => $filePath]);
                
                // Skip empty rows
                if (count(array_filter($lineData)) == 0) {
                    Log::warning('InvoiceParserService: Skipping empty row.', ['filePath' => $filePath]);
                    continue;
                }

                $parsedLines[] = array_combine($header, $lineData);
                Log::info('InvoiceParserService: Line parsed', ['parsed_line' => end($parsedLines), 'filePath' => $filePath]);
            }

            if (count($parsedLines) === 0) {
                Log::error('No data lines found in file: ' . $filePath);
                throw new \Exception('No data lines found in file: ' . $filePath);
            }
            Log::info('InvoiceParserService: Dispatching CarrierInvoiceLineExtracted event', ['file_path' => $filePath, 'line_count' => count($parsedLines)]);
            Event::dispatch(new CarrierInvoiceLineExtracted($filePath, count($parsedLines), $parsedLines));
            Log::info('InvoiceParserService: Finished parsing and dispatched event.', ['filePath' => $filePath]);

        } elseif ($ext === 'xml') {
            Log::info('InvoiceParserService: Starting XML parsing.', ['filePath' => $filePath]);
            $xmlContent = file_get_contents($filePath);
            // Handle BOM
            $xmlContent = preg_replace('/^\xEF\xBB\xBF/', '', $xmlContent);
            libxml_use_internal_errors(true);
            $xml = simplexml_load_string($xmlContent);
            if ($xml === false) {
                $errors = libxml_get_errors();
                Log::error('Malformed XML in file: ' . $filePath, ['errors' => $errors]);
                libxml_clear_errors();
                throw new \Exception('Malformed XML: ' . $filePath);
            }
            $parsedLines = [];
            foreach ($xml->line as $line) {
                $row = [];
                foreach ($line as $k => $v) {
                    $row[(string)$k] = (string)$v;
                }
                $parsedLines[] = $row;
                Log::info('InvoiceParserService: Parsed XML line.', ['line' => $row, 'filePath' => $filePath]);
            }
            if (count($parsedLines) === 0) {
                Log::error('No <line> elements found in XML: ' . $filePath);
                throw new \Exception('No <line> elements found in XML: ' . $filePath);
            }
            Log::info('InvoiceParserService: Dispatching CarrierInvoiceLineExtracted event for XML.', ['file_path' => $filePath, 'line_count' => count($parsedLines)]);
            Event::dispatch(new CarrierInvoiceLineExtracted($filePath, count($parsedLines), $parsedLines));
            Log::info('InvoiceParserService: Finished parsing XML and dispatched event.', ['filePath' => $filePath]);
        }
    }
} 