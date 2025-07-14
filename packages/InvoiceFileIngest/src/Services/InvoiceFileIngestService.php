<?php

namespace Packages\InvoiceFileIngest\Services;

use Packages\InvoiceFileIngest\Events\FileStored;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;

class InvoiceFileIngestService
{
    /**
     * Ingest a file, validate, and emit FileStored event.
     *
     * @param string $filePath
     * @param array $metadata
     * @throws \Exception
     */
    public function ingest(string $filePath, array $metadata = [])
    {
        Log::info('Starting file ingestion process for: ' . $filePath);

        // Check if file exists and is not a symlink to a missing file
        if (!file_exists($filePath)) {
            Log::error('File not found: ' . $filePath);
            throw new \Exception('Invoice file not found at path: ' . $filePath);
        }
        // Check if it's a file (not a directory)
        if (!is_file($filePath)) {
            Log::error('Path is not a file: ' . $filePath);
            throw new \Exception('Path is not a valid file: ' . $filePath);
        }
        // Check for symlink to missing file
        if (is_link($filePath) && !file_exists(realpath($filePath))) {
            Log::error('Symlink points to missing file: ' . $filePath);
            throw new \Exception('Symlink points to missing file: ' . $filePath);
        }
        // Check readability
        if (!is_readable($filePath)) {
            Log::error('File not readable: ' . $filePath);
            throw new \Exception('File is not readable: ' . $filePath);
        }
        // Check not empty
        if (filesize($filePath) === 0) {
            Log::error('File is empty: ' . $filePath);
            throw new \Exception('File is empty: ' . $filePath);
        }

        Log::info('File passed initial validation: ' . $filePath);

        // Check extension
        $basename = basename($filePath);
        $parts = explode('.', $basename);
        if (count($parts) < 2) {
            Log::error('File has no extension: ' . $filePath);
            throw new \Exception('File has no extension: ' . $filePath);
        }
        $ext = array_pop($parts);
        $extLower = strtolower($ext);
        $supported = ['csv', 'xml', 'txt', 'xlsx'];
        if (!in_array($extLower, $supported)) {
            Log::error('Unsupported file format: ' . $ext, ['filePath' => $filePath]);
            throw new \Exception('Unsupported file format: ' . $ext);
        }
        // Double extension check
        if (count($parts) > 1) {
            $prevExt = strtolower(array_pop($parts));
            if (in_array($prevExt, $supported)) {
                Log::error('File has double extension: ' . $filePath);
                throw new \Exception('File has double extension: ' . $filePath);
            }
        }
        // Spaces in extension
        if (preg_match('/\s/', $ext)) {
            Log::error('File extension contains spaces: ' . $filePath);
            throw new \Exception('File extension contains spaces: ' . $filePath);
        }

        Log::info('File extension validation passed: ' . $filePath);

        // Accept files with leading dot in name
        // (already handled by basename/extension logic)
        // Emit event with file path, size, and custom metadata
        $fileSize = filesize($filePath);
        $eventData = [
            'filePath' => $filePath,
            'size' => $fileSize,
            'metadata' => $metadata,
        ];

        Log::info('Dispatching FileStored event for: ' . $filePath, $eventData);

        // Dispatch an event
        Event::dispatch(new FileStored($filePath, $fileSize, $metadata));

        Log::info('File ingestion process finished for: ' . $filePath);
    }
}