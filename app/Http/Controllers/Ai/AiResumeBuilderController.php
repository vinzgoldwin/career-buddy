<?php

namespace App\Http\Controllers\Ai;

use App\Http\Controllers\Controller;
use App\Http\Requests\Ai\ResumeStoreRequest;
use App\Models\EmploymentType;
use App\Services\PdfToTextService;
use App\Services\Resume\BuildPdfResumeService;
use App\Services\ResumeBuilderService;
use App\Services\ResumeParsingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Log;

class AiResumeBuilderController extends Controller
{
    public function __construct(
        protected PdfToTextService $pdfToTextService,
        protected ResumeParsingService $resumeParsingService,
        protected ResumeBuilderService $resumeBuilderService,
        protected BuildPdfResumeService $buildPdfResumeService,
    ) {}

    public function index()
    {
        $user = Auth::user();

        try {
            $employmentTypes = EmploymentType::all();
        } catch (\Exception $e) {
            $employmentTypes = collect();
        }

        $aiParsedData = session('ai_parsed_data');

        return Inertia::render('ai/AiResumeBuilder', [
            'employmentTypes' => $employmentTypes,
            'aiParsedData' => $aiParsedData ?: null,
            'prefillData' => $this->resumeBuilderService->getPrefillData($user),
        ]);
    }

    public function editor()
    {
        $user = Auth::user();

        try {
            $employmentTypes = EmploymentType::all();
        } catch (\Exception $e) {
            $employmentTypes = collect();
        }

        $aiParsedData = session('ai_parsed_data');

        return Inertia::render('ai/ResumeEditor', [
            'employmentTypes' => $employmentTypes,
            'aiParsedData' => $aiParsedData ?: null,
            'prefillData' => $this->resumeBuilderService->getPrefillData($user),
        ]);
    }

    public function store(ResumeStoreRequest $request): RedirectResponse
    {
        $user = Auth::user();
        $this->resumeBuilderService->save($user, $request->validated());

        return redirect()->back()->with('success', 'Resume data saved successfully!');
    }

    public function uploadResume(Request $request)
    {
        set_time_limit(150);

        $request->validate([
            'resume' => 'required|file|mimes:pdf|max:10240',
        ]);

        try {
            $path = $request->file('resume')->getRealPath();
            $text = $this->pdfToTextService->extractWithFallback($path);

            if (! $request->header('X-Inertia')) {
                return response()->json([
                    'text' => trim((string) $text),
                ], 200);
            }

            $response = $this->resumeParsingService->extractResumeInformation((string) $text);

            $content = $response['choices'][0]['message']['content'] ?? '';
            if (preg_match('/<structured_json>(.*?)<\/structured_json>/s', $content, $matches)) {
                $jsonString = trim($matches[1]);

                try {
                    $structuredData = json_decode($jsonString, true);
                    Log::info('Parsed Structured Data', ['structuredData' => $structuredData]);

                    if (json_last_error() === JSON_ERROR_NONE) {
                        return redirect()
                            ->route('ai-resume-builder')
                            ->with('ai_parsed_data', $structuredData);
                    }
                } catch (\Exception $e) {
                    Log::error('Exception during JSON parsing', ['exception' => $e->getMessage()]);
                }
            }

            return redirect()->back()->with('error', 'Failed to parse resume data. Please try again.');
        } catch (\Exception $e) {
            Log::error('Exception during resume upload processing', ['exception' => $e->getMessage()]);

            return redirect()->back()->with('error', 'An error occurred while processing your resume. Please try again.');
        }
    }

    public function download()
    {
        return $this->buildPdfResumeService->downloadForCurrentUser();
    }
}
