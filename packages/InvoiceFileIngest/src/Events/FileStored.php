<?php

namespace Packages\InvoiceFileIngest\Events;

class FileStored
{
    public string $filePath;
    public int $size;
    public array $metadata;

    public function __construct(string $filePath, int $size, array $metadata = [])
    {
        $this->filePath = $filePath;
        $this->size = $size;
        $this->metadata = $metadata;
    }
} 