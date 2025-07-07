<?php

namespace Packages\PdfRenderer\Facades;

class Pdf
{
    /**
     * Fakeable stub of the PDF generator.
     *
     * @param string $view
     * @param array  $data
     * @return \Packages\PdfRenderer\Facades\PdfDocument
     */
    public static function loadView(string $view, array $data = [])
    {
        return new PdfDocument();
    }
}
