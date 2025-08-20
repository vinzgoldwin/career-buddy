<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Spatie\PdfToText\Pdf;

class PdfToTextService
{
    public function extract(string $path): string
    {
        return Pdf::getText($path);
    }

    public function extractWithFallback(string $path): string
    {
        try {
            $text = $this->extract($path);
        } catch (\Throwable $e) {
            Log::warning('PdfToTextService failed, using naive extractor', ['exception' => $e->getMessage()]);
            $text = $this->naivePdfTextExtract($path);
        }

        if (trim((string) $text) === '') {
            $text = $this->naivePdfTextExtract($path);
        }

        return $text;
    }

    private function naivePdfTextExtract(string $path): string
    {
        $content = @file_get_contents($path) ?: '';
        if ($content === '') {
            return '';
        }

        if (preg_match_all('/\(([^\)]+)\)/', $content, $m)) {
            $text = trim(preg_replace('/\s+/', ' ', implode(' ', $m[1])));

            return $text;
        }

        $text = preg_replace('/[^\x20-\x7E]+/', ' ', $content) ?? '';

        return trim(preg_replace('/\s+/', ' ', $text));
    }
}
