<?php
namespace App\Services;

use Exception;

class InvoiceParserService
{
    /**
     * Parse .txt, .csv, or .xml; for .xlsx throw a “not implemented” exception.
     */
    public function parse(string $filePath): array
    {
        throw new Exception('InvoiceParserService::parse() not implemented.');
    }

    /**
     * Parse a plain-text invoice file.
     * 
     * @param string $filePath Path to the invoice file.
     * @return array Parsed invoice data.
     * @throws Exception If the file is not found or has an unsupported format.
     */
    protected function parsePlainText(string $filePath): array
    {
        throw new Exception('InvoiceParserService::parsePlainText() not implemented.');
    }

    /**
     * Parse a CSV invoice file.
     * 
     * @param string $filePath Path to the invoice file.
     * @return array Parsed invoice data.
     * @throws Exception If the file is not found or has an unsupported format.
     */
    protected function parseCsv(string $filePath): array
    {
        throw new Exception('InvoiceParserService::parseCsv() not implemented.');
    }

    /**
     * Parse an XML invoice file.
     * 
     * @param string $filePath Path to the invoice file.
     * @return array Parsed invoice data.
     * @throws Exception If the file is not found or has an unsupported format.
     */
    protected function parseXml(string $filePath): array
    {
        throw new Exception('InvoiceParserService::parseXml() not implemented.');
    }
}