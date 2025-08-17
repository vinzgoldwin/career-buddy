<?php

namespace App\Services;

use Spatie\PdfToText\Pdf;

class PdfToTextService
{
    /**
     * Extract text from a PDF file.
     *
     * @param string $path The path to the PDF file
     * @return string The extracted text
     */
    public function extract(string $path): string
    {
        return Pdf::getText($path);
    }
}