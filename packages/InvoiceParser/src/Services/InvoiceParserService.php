<?php

namespace Packages\InvoiceParser\Services;

use Packages\InvoiceParser\Events\CarrierInvoiceLineExtracted;
use Illuminate\Support\Facades\Event;

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
        if (!in_array($ext, ['csv', 'xml'])) {
            throw new \Exception('Unsupported file format: ' . $ext);
        }
        if ($ext === 'csv') {
            $handle = fopen($filePath, 'r');
            if (!$handle) {
                throw new \Exception('Failed to open CSV file: ' . $filePath);
            }
            // Read the first line, strip BOM if present, and parse as CSV for the header
            $firstLine = fgets($handle);
            if ($firstLine === false) {
                fclose($handle);
                throw new \Exception('Failed to read CSV file: ' . $filePath);
            }
            $firstLine = preg_replace('/^\xEF\xBB\xBF/', '', $firstLine);
            $header = str_getcsv($firstLine);
            $header = array_map('trim', $header);
            // Case-insensitive headers
            $headerLower = array_map('strtolower', $header);
            if (!in_array('header1', $headerLower) || !in_array('header2', $headerLower)) {
                fclose($handle);
                throw new \Exception('Missing required header(s) in CSV: ' . $filePath);
            }
            $parsedLines = [];
            while (($row = fgetcsv($handle)) !== false) {
                if (count($row) < 2) {
                    continue; // skip malformed or empty lines
                }
                $rowAssoc = [];
                foreach ($header as $j => $h) {
                    if (isset($row[$j])) {
                        $rowAssoc[$h] = $row[$j];
                    }
                }
                $parsedLines[] = $rowAssoc;
            }
            fclose($handle);
            if (count($parsedLines) === 0) {
                throw new \Exception('No data lines found in CSV: ' . $filePath);
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