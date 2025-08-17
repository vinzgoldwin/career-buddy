<?php

namespace App\Http\Controllers;

use App\Services\PdfToTextService;
use Illuminate\Http\Request;

class PdfController extends Controller
{
    protected PdfToTextService $pdfToTextService;

    public function __construct(PdfToTextService $pdfToTextService)
    {
        $this->pdfToTextService = $pdfToTextService;
    }

    public function extractText(Request $request)
    {
        $request->validate([
            'pdf' => 'required|file|mimes:pdf|max:10240', // Max 10MB
        ]);

        $path = $request->file('pdf')->getRealPath();
        $text = $this->pdfToTextService->extract($path);

        return response()->json([
            'text' => $text,
        ]);
    }
}