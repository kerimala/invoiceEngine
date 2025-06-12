<?php

namespace Packages\InvoiceFileIngest\Events;

use Spatie\EventSourcing\StoredEvents\ShouldBeStored;

class FileStored extends ShouldBeStored
{
    public string $filePath;
    public int $size;
    // Allow for custom metadata
    public function __construct(string $filePath, int $size, ...$metadata)
    {
        $this->filePath = $filePath;
        $this->size = $size;
        // Dynamically assign any extra metadata as public properties
        foreach ($metadata as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $k => $v) {
                    $this->$k = $v;
                }
            } else {
                $this->$key = $value;
            }
        }
    }
} 