<?php

namespace Packages\InvoiceFileIngest\Services;

use Packages\InvoiceFileIngest\Events\FileStored;
use Illuminate\Support\Facades\Event;

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
        // Check if file exists and is not a symlink to a missing file
        if (!file_exists($filePath)) {
            throw new \Exception('Invoice file not found at path: ' . $filePath);
        }
        // Check if it's a file (not a directory)
        if (!is_file($filePath)) {
            throw new \Exception('Path is not a valid file: ' . $filePath);
        }
        // Check for symlink to missing file
        if (is_link($filePath) && !file_exists(realpath($filePath))) {
            throw new \Exception('Symlink points to missing file: ' . $filePath);
        }
        // Check readability
        if (!is_readable($filePath)) {
            throw new \Exception('File is not readable: ' . $filePath);
        }
        // Check not empty
        if (filesize($filePath) === 0) {
            throw new \Exception('File is empty: ' . $filePath);
        }
        // Check extension
        $basename = basename($filePath);
        $parts = explode('.', $basename);
        if (count($parts) < 2) {
            throw new \Exception('File has no extension: ' . $filePath);
        }
        $ext = array_pop($parts);
        $extLower = strtolower($ext);
        $supported = ['csv', 'xml'];
        if (!in_array($extLower, $supported)) {
            throw new \Exception('Unsupported file format: ' . $ext);
        }
        // Double extension check
        if (count($parts) > 1) {
            $prevExt = strtolower(array_pop($parts));
            if (in_array($prevExt, $supported)) {
                throw new \Exception('File has double extension: ' . $filePath);
            }
        }
        // Spaces in extension
        if (preg_match('/\s/', $ext)) {
            throw new \Exception('File extension contains spaces: ' . $filePath);
        }
        // Accept files with leading dot in name
        // (already handled by basename/extension logic)
        // Emit event with file path, size, and custom metadata
        $eventData = array_merge([
            'filePath' => $filePath,
            'size' => filesize($filePath),
        ], $metadata);
        Event::dispatch(new FileStored(...$eventData));
    }
} 